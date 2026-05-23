<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Exception;
use FastImageSize\FastImageSize;
use GuzzleHttp\Psr7\MimeType;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use JUPWA\Data\Data;
use JUPWA\Thumbs\Render;

class Manifest
{
    /**
     *
     * @param array $options
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public static function create(array $options = []): void
    {
        $manifestPath = JPATH_SITE.'/manifest.webmanifest';

        $data = Data::$manifest;
        $param = $options['param'];

        $data['name'] = $param['manifest_name'] ?? $options['site'] ?? '';
        $data['short_name'] = $param['manifest_sname'] ?? $options['site'] ?? '';
        $data['description'] = $param['manifest_desc'] ?? $options['description'] ?? '';
        $data['lang'] = $param['manifest_lang'] ?? 'en';
        $data['dir'] = $param['manifest_dir'] ?? 'ltr';
        $data['scope'] = $param['manifest_scope'] ?? Uri::root();
        $data['display'] = $param['manifest_display'] ?? 'standalone';
        $data['start_url'] = $param['manifest_start_url'] ?? Uri::root().'?utm_source=pwa';
        $data['id'] = $param['manifest_id'] ?? $data['start_url'];
        $data['display_override'] = $param['manifest_display_override'] ?? null;
        $data['orientation'] = $param['manifest_orientation'] ?? null;
        $data['background_color'] = $param['background_color'] ?? '#fafafa';
        $data['theme_color'] = $param['theme_color'] ?? '#fafafa';
        $data['launch_handler'] = self::launch_handler($param ?? []);
        $data['prefer_related_applications'] = $param['prefer_related_applications'] === 'true' ? true : false;
        $data['related_applications'] = self::related_applications($param ?? []);
        $data['scope_extensions'] = self::scope_extensions($param ?? []);
        $data['screenshots'] = self::screenshots($param ?? []);
        $data['icons'] = self::icons();
        $data['shortcuts'] = self::shortcuts($param ?? []);
        $data['handle_links'] = $param['manifest_handle_links'] ?? [];
        $data['categories'] = $param['manifest_categories'] ?? [];

        $data['edge_side_panel'] = [
            'preferred_width' => (int)($param['manifest_edge_side_panel_width'] ?? 0),
        ];

        self::removeEmptyArrays($data, [
            'screenshots',
            'icons',
            'shortcuts',
            'categories',
            'scope_extensions',
        ]);

        if (empty($data['edge_side_panel']['preferred_width'])) {
            unset($data['edge_side_panel']);
        }

        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );

        File::write(
            $manifestPath,
            $json
        );
    }

    /**
     * @param array $data
     * @param array $keys
     *
     *
     * @since 1.0
     */
    private static function removeEmptyArrays(
        array &$data,
        array $keys
    ): void {
        foreach ($keys as $key) {
            if (isset($data[$key]) && is_countable($data[$key]) && count($data[$key]) === 0) {
                unset($data[$key]);
            }
        }
    }

    /**
     *
     * @return void
     *
     * @since 1.0
     */
    public static function addVersion(): void
    {
        $json = [
            'version' => bin2hex(random_bytes(8)),
        ];

        $json = json_encode(
            $json,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        File::write(
            JPATH_SITE.'/favicons/version.json',
            $json
        );
    }

    /**
     *
     * @return string
     *
     * @since 1.0
     */
    public static function getVersion(): string
    {
        $json = JPATH_SITE.'/favicons/version.json';
        if (file_exists($json)) {
            $json = file_get_contents($json);

            return json_decode($json)->{'version'};
        }

        return '';
    }

    /**
     *
     * @return array
     *
     * @since 1.0
     */
    private static function icons(): array
    {
        $sizes = Data::$manifest_icons;
        $icons = [];
        $sitePath = JPATH_SITE.'/';
        $rootUri = Uri::root();

        foreach ($sizes as $size) {
            $files = [
                'any' => 'favicons/micon_'.$size.'.png',
                'maskable' => 'favicons/maskable_'.$size.'.png',
            ];

            foreach ($files as $purpose => $filePath) {
                if (file_exists($sitePath.$filePath)) {
                    $v = filemtime($sitePath.$filePath);

                    $icons[] = [
                        'src' => $rootUri.$filePath.'?v='.$v,
                        'sizes' => $size.'x'.$size,
                        'type' => 'image/png',
                        'purpose' => $purpose,
                    ];
                }
            }
        }

        return $icons;
    }

    /**
     *
     * @param array $options
     * @return array
     *
     * @since 1.0
     */
    private static function shortcuts(array $options = []): array
    {
        $shortcutsConfig = $options['shortcuts'] ?? [];

        if (empty($shortcutsConfig)) {
            return [];
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $menuIds = array_column(
            $shortcutsConfig,
            'item'
        );

        $menuItems = self::getMenuItems(
            $db,
            $menuIds
        );

        $result = [];

        foreach ($shortcutsConfig as $shortcut) {
            $menuId = (int)($shortcut['item'] ?? 0);

            if ($menuId === 0 || !isset($menuItems[$menuId])) {
                continue;
            }

            $row = $menuItems[$menuId];
            $languagePrefix = self::getLanguagePrefix($shortcut['language'] ?? '');
            $iconUrl = self::getShortcutIconUrl($menuId);

            $result[] = [
                'name' => $row->title,
                'url' => $languagePrefix.$row->path,
                'icons' => [
                    [
                        'src' => $iconUrl,
                        'type' => 'image/png',
                        'sizes' => '96x96',
                    ],
                ],
            ];
        }

        return $result;
    }

    /**
     * @param DatabaseInterface $db
     * @param array $ids
     *
     * @return array
     *
     * @since 1.0
     */
    private static function getMenuItems(
        DatabaseInterface $db,
        array $ids
    ): array {
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select(['id', 'title', 'path'])
            ->from('#__menu')
            ->whereIn($db->quoteName('id'), $ids);
        $db->setQuery($query);

        $items = $db->loadObjectList('id');

        return $items ?: [];
    }

    /**
     * @param string $language
     *
     * @return string
     *
     * @since 1.0
     */
    private static function getLanguagePrefix(string $language): string
    {
        if ($language === '' || $language === '*') {
            return '';
        }

        $langCode = explode('-', $language)[0] ?? '';

        return $langCode ? '/'.$langCode.'/' : '';
    }

    /**
     * @param int $menuId
     *
     * @return string
     *
     * @since 1.0
     */
    private static function getShortcutIconUrl(int $menuId): string
    {
        $iconPath = 'favicons/shortcut_'.$menuId.'.png';
        $fullPath = JPATH_SITE.'/'.$iconPath;

        return file_exists($fullPath)
            ? Uri::root().$iconPath
            : '';
    }

    /**
     *
     * @param array $options
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    private static function screenshots(array $options = []): array
    {
        $screenshotsConfig = $options['screenshots'] ?? [];

        if (empty($screenshotsConfig)) {
            return [];
        }

        static $fastImageSize = null;
        if ($fastImageSize === null) {
            $fastImageSize = new FastImageSize();
        }

        $result = [];
        foreach ($screenshotsConfig as $item) {
            $screen = self::processScreenshot(
                $item,
                $fastImageSize
            );

            if ($screen !== null) {
                $result[] = $screen;
            }
        }

        return $result;
    }

    /**
     * @param array $screenshot
     * @param FastImageSize $fis
     *
     * @return array|null
     *
     * @throws Exception
     * @since 1.0
     */
    private static function processScreenshot(
        array $screenshot,
        FastImageSize $fis
    ): ?array {
        $relativePath = Render::image($screenshot['screen'] ?? null);

        if (empty($relativePath) || !file_exists(JPATH_SITE.'/'.$relativePath)) {
            return null;
        }

        $fullPath = JPATH_SITE.'/'.$relativePath;
        $imageSize = $fis->getImageSize($fullPath);
        $sizes = ($imageSize !== false)
            ? ($imageSize['width'].'x'.$imageSize['height'])
            : '';

        return [
            'src' => Uri::root().$relativePath,
            'sizes' => $sizes,
            'type' => MimeType::fromFilename(JPATH_ROOT.'/'.$relativePath) ?: 'image/png',
        ];
    }

    /**
     *
     * @param array $options
     * @return array
     *
     * @since 1.0
     */
    private static function related_applications(array $options = []): array
    {
        $related = [];
        if (!empty($options['my_webapp_pwa']) && file_exists(JPATH_ROOT.'/manifest.webmanifest')) {
            $related[] = [
                'platform' => 'webapp',
                'url' => Uri::root().'manifest.webmanifest',
            ];
        }

        $relatedApps = $options['related_apps'] ?? [];
        foreach ($relatedApps as $app) {
            $platform = $app->related_apps_platforms ?? '';
            $url = $app->related_apps_url ?? '';
            $id = $app->related_apps_id ?? '';

            if (empty($platform) || empty($url)) {
                continue;
            }

            $related[] = array_filter([
                'platform' => $platform,
                'url' => $url,
                'id' => $id,
            ], static fn($value) => $value !== '');
        }

        return $related;
    }

    /**
     *
     * @param array $options
     * @return array
     *
     * @since 1.0
     */
    private static function scope_extensions(array $options = []): array
    {
        $scopeExtensions = $options['manifest_scope_extensions'] ?? [];

        if (empty($scopeExtensions)) {
            return [];
        }

        $item = [];
        foreach ($scopeExtensions as $scope) {
            $item[] = [
                'origin' => $scope['domains'],
            ];
        }

        return $item;
    }

    /**
     *
     * @param array $options
     *
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    private static function launch_handler(array $options = []): array
    {
        $modes = $options['manifest_launch_handler'] ?? [];

        $cleanModes = array_values(
            array_filter(
                array_map('trim', (array)$modes)
            )
        );

        return $cleanModes !== []
            ? ['client_mode' => $cleanModes]
            : [];
    }
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Thumbs
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Thumbs;

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use JUPWA\Classes\PHP_ICO;
use JUPWA\Data\Data;
use JUPWA\Utils\Image;

class Render
{
    /**
     *
     * @param array $option
     * @param       $app
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public static function create(
        array $option = [],
        $app = null
    ): void {
        $path = JPATH_SITE.'/favicons';
        if (file_exists($path) && is_dir($path)) {
            Folder::delete($path);
        }

        Folder::create($path);

        $icon_sm = ($option['source_icon_sm'] ?? 'media/jupwa/image/logo.png');
        if (empty($icon_sm)) {
            $icon_sm = 'media/jupwa/image/logo.png';
        }

        $favicon = self::ico(['source_icon_sm' => $icon_sm]);
        $source_icon_path = JPATH_SITE.'/'.($option['source_icon'] ?? '');

        $icons_s = self::icons([
            'size' => Data::$icons_sm,
            'icon' => $icon_sm,
        ]);

        $appleicons = self::appleicons([
            'size' => Data::$favicons['apple-touch-icon'] ?? [],
            'icon' => $icon_sm,
            'color' => $option['maskiconcolor'] ?? null,
        ]);

        $json = [
            'favicon_root' => $favicon->root ?? '',
            'favicon_favicons' => $favicon->favicons ?? '',
            'icons' => $icons_s,
            'appleicons' => $appleicons,
            'manifest_icons' => self::manifest_icons($icon_sm, $option),
            'splash_icons' => self::splash_icons($icon_sm),
            'shortcuts' => self::shortcuts($option),
        ];

        $json_ext = [];
        $source_icon = $option['source_icon'] ?? '';

        if ($source_icon && !file_exists($source_icon_path)) {
            $json_ext = [
                'article_logo' => self::article_logo($option),
            ];
        }

        $json = array_merge(
            $json,
            $json_ext
        );

        $json_string = json_encode(
            $json,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        File::write(
            JPATH_SITE.'/favicons/thumbs.json',
            $json_string
        );

        if ($app
            && is_object($app)
            && method_exists(
                $app,
                'enqueueMessage'
            )
            && !file_exists(
                JPATH_SITE.'/favicons/thumbs.json'
            )
        ) {
            $app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
        }
    }

    /**
     *
     * @param string|null $image
     *
     * @return string
     *
     * @since 1.0
     */
    public static function image(?string $image): string
    {
        if ($image === null || $image === '') {
            return '';
        }

        if (!str_contains($image, '#joomlaImage')) {
            return $image;
        }

        $parts = explode('#joomlaImage', $image);

        return $parts[0];
    }

    /**
     *
     * @param array $option
     *
     * @return string
     *
     * @throws Exception
     * @since 1.0
     */
    public static function article_logo(array $option = []): string
    {
        $source_icon = $option['source_icon'] ?? '';
        if (!$source_icon) {
            return '';
        }

        $source = self::image($source_icon);
        $width = 600;
        $height = 60;
        $out = 'favicons/logo_'.$width.'x'.$height.'.png';

        return Image::render_image($source, $out, [
            'width' => $width,
            'height' => $height,
            'position' => 'left',
            'color' => '#ffffff',
            'ratio' => 0.6,
            'r' => 15,
        ]);
    }

    /**
     *
     * @param string|null $icon_sm
     * @param array $option
     *
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    public static function manifest_icons(
        ?string $icon_sm,
        array $option = []
    ): array {
        if (!$icon_sm) {
            return [];
        }

        $icons = Data::$manifest_icons ?? [];
        $source = self::image($icon_sm);
        $maskColor = $option['maskiconcolor'] ?? '#ffffff';
        $useBgColor = ($option['manifest_icon_background_color'] ?? 0) == 1;

        $images = [];
        foreach ($icons as $icon) {
            $out = 'favicons/micon_'.$icon.'.png';
            $images[] = Image::render_image(
                $source,
                $out,
                [
                    'width' => $icon,
                    'height' => $icon,
                    'ratio' => 1.1,
                    'color' => $useBgColor ? $maskColor : null,
                ]
            );

            $out = 'favicons/maskable_'.$icon.'.png';
            $images[] = Image::render_image(
                $source,
                $out,
                [
                    'width' => $icon,
                    'height' => $icon,
                    'ratio' => 1.45,
                    'color' => $useBgColor ? $maskColor : '#ffffff',
                ]
            );
        }

        return $images;
    }

    /**
     *
     * @param string|null $icon_sm
     *
     * @return string
     *
     * @throws Exception
     * @since 1.0
     */
    public static function splash_icons(?string $icon_sm): string
    {
        if (!$icon_sm) {
            return '';
        }

        $source = self::image($icon_sm);
        $out = 'favicons/sicon_512.png';

        return Image::render($source, $out, [
            'width' => 512,
            'height' => 512,
        ]);
    }

    /**
     *
     * @param array $option
     *
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    public static function appleicons(array $option = []): array
    {
        $icons = $option['size'] ?? [];
        $icon_path = $option['icon'] ?? null;

        if (!$icon_path) {
            return [];
        }

        $source = self::image($icon_path);
        $name = (!empty($option['name']) ? $option['name'] : 'icon');

        $images = [];
        foreach ($icons as $icon) {
            $out = 'favicons/apple'.$name.'_'.$icon.'.png';
            $images[] = Image::render_image(
                $source,
                $out,
                [
                    'width' => $icon,
                    'height' => $icon,
                    'ratio' => 1.3,
                    'color' => $option['color'] ?? null,
                ]
            );
        }

        return $images;
    }

    /**
     *
     * @param array $option
     *
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    public static function icons(array $option = []): array
    {
        $icons = $option['size'] ?? [];
        $icon_path = $option['icon'] ?? null;
        if (!$icon_path) {
            return [];
        }

        $source = self::image($icon_path);
        $name = (!empty($option['name']) ? $option['name'] : 'icon');

        $images = [];
        foreach ($icons as $icon) {
            $out = 'favicons/'.$name.'_'.$icon.'.png';
            $images[] = Image::render(
                $source,
                $out,
                [
                    'width' => $icon,
                    'height' => $icon,
                ]
            );
        }

        return $images;
    }

    /**
     *
     * @param array $option
     *
     * @return array
     *
     * @throws Exception
     * @since 1.0
     */
    public static function shortcuts(array $option = []): array
    {
        $images = [];
        $shortcuts = $option['shortcuts'] ?? [];

        if (is_array($shortcuts)) {
            foreach ($shortcuts as $val) {
                if (empty($val['icons'])) {
                    continue;
                }

                $source = self::image($val['icons']);
                $item = $val['item'] ?? 'default';
                $out = 'favicons/shortcut_'.$item.'.png';

                $images[] = Image::render(
                    $source,
                    $out,
                    [
                        'width' => 96,
                        'height' => 96,
                    ]
                );
            }
        }

        return $images;
    }

    /**
     *
     * @param array $option
     *
     * @return object
     *
     * @throws Exception
     * @since 1.0
     */
    public static function ico(array $option = []): object
    {
        $source_icon_sm = $option['source_icon_sm'] ?? '';

        if ($source_icon_sm !== '') {
            $source = JPATH_SITE.'/'.self::image($source_icon_sm);
            $destination = JPATH_SITE.'/favicon.ico';
            $favicons_path = JPATH_SITE.'/favicons/favicon.ico';

            $ico_lib = new PHP_ICO(
                $source,
                [
                    [16, 16],
                    [32, 32],
                    [48, 48],
                ]
            );

            $result = [
                'root' => '',
                'favicons' => '',
            ];

            if ($ico_lib->save_ico($destination)) {
                File::copy($destination, $favicons_path);

                $result['root'] = 'favicon.ico';
            }

            if (file_exists($favicons_path)) {
                $result['favicons'] = 'favicons/favicon.ico';
            }

            return (object)$result;
        }

        return (object)[
            'root' => '',
            'favicons' => '',
        ];
    }
}
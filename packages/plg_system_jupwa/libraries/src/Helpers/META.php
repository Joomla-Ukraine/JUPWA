<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use JUPWA\Data\Data;
use JUPWA\Thumbs\Render;

class META
{
    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function render(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $pwa_version = '?v='.Manifest::getVersion();

        self::manifest($pwa_version);

        self::speculationrules(['params' => $params]);

        self::appstore(['params' => $params]);
        self::googleplay(['params' => $params]);

        self::preconnect(['params' => $params]);
        self::preloads(['params' => $params]);

        self::meta_apple([
            'params' => $params,
            'pwa_version' => $pwa_version,
        ]);

        self::meta_ms(['params' => $params]);

        self::facebook(['params' => $params]);

        self::icons([
            'params' => $params,
            'pwa_version' => $pwa_version,
        ]);

        self::pwa([
            'params' => $params,
            'pwa_version' => $pwa_version,
        ]);

        self::splash(['params' => $params]);
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function splash(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params['source_icon_sm']) {
            $icon = Uri::root(true).'/favicons/sicon_512.png';

            $pwaicons = [
                'icon' => $icon,
                'color' => $params['ioscolor'] ?: '#ffffff',
            ];

            $pwaicons = json_encode($pwaicons);

            $doc->addCustomTag('<script id="pwaicons" type="application/json">'.$pwaicons.'</script>');
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function speculationrules(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $doc = $app->getDocument();

        if ($user->guest == 1 && $params->get('use_speculationrules') == 1) {
            $prerender = [
                'prerender' => [
                    [
                        'source' => 'document',
                        'where' => [
                            'and' => [
                                [
                                    'href_matches' => '/*',
                                ],

                            ],
                        ],
                        'eagerness' => 'moderate',
                    ],
                ],
            ];

            if (trim($params->get('speculationrules_class')) !== '') {
                $speculation_class = str_replace('.', '', trim($params->get('speculationrules_class')));

                $prerender['prerender'][0]['where']['and'][] = [
                    'not' => [
                        'selector_matches' => '.'.$speculation_class,
                    ],
                ];
            }

            $doc->addCustomTag(
                '<script type="speculationrules">'.json_encode($prerender, JSON_UNESCAPED_SLASHES).'</script>'
            );
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function appstore(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params->get('appstore') !== null) {
            $doc->setMetaData('apple-itunes-app', 'app-id='.trim($params->get('appstore')));
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function googleplay(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params->get('googleplay') !== null) {
            $doc->setMetaData(
                'google-play-app',
                'app-id='.trim($params->get('googleplay'))
            );
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function facebook(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params->get('fbpage') !== null) {
            $doc->setMetaData(
                'article:publisher',
                $params->get('fbpage'),
                'property'
            );
        }

        if ($params->get('fbapp') !== null) {
            $doc->setMetaData(
                'fb:app_id',
                $params->get('fbapp'),
                'property'
            );
        }

        $fbadmins = (array)$params->get('fbadmin');
        $i = 0;
        foreach ($fbadmins as $fbadmin) {
            if ($fbadmin->id) {
                $doc->setMetaData(
                    'fb:admins_'.($i + 1),
                    $fbadmin->id,
                    'property'
                );
            }
            $i++;
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function preloads(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        $preloads = (array)$params->get('preloads');
        foreach ($preloads as $preload) {
            if ($preload->url) {
                $preload_as = ['as' => $preload->as];
                $preload_type = [];

                if ($preload->type) {
                    $preload_type = ['type' => $preload->type];
                }

                $preload_co = [];
                if ($preload->crossorigin) {
                    $preload_co = ['crossorigin' => $preload->crossorigin];
                }

                $preload_media = [];
                if ($preload->media) {
                    $preload_media = ['media' => $preload->media];
                }

                $_preload = array_merge(
                    $preload_as,
                    $preload_type,
                    $preload_co,
                    $preload_media
                );

                $doc->addHeadLink(
                    $preload->url,
                    'preload',
                    'rel',
                    [$_preload]
                );
            }
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function preconnect(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        $preconnect = Data::$preconnect;
        foreach ($preconnect as $key => $val) {
            if ($params->get('precnct-'.$key) == 1) {
                foreach ($val as $link) {
                    $doc->addHeadLink(
                        $link,
                        'preconnect'
                    );
                }
            }
        }

        $preconnects = (array)$params->get('preconnect');
        foreach ($preconnects as $preconnect) {
            if ($preconnect->url) {
                $doc->addHeadLink(
                    $preconnect->url,
                    'preconnect'
                );
            }
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function meta_apple(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $app_name = $params->get('manifest_name');
        $icons = Data::$favicons;

        foreach ($icons['apple-touch-icon'] as $icon) {
            $file = 'favicons/appleicon_'.$icon.'.png';

            if (file_exists(JPATH_SITE.'/'.$file)) {
                $href = Uri::root().$file.$option['pwa_version'];

                $doc->addCustomTag('<link rel="apple-touch-icon" sizes="'.$icon.'x'.$icon.'" href="'.$href.'">');
            }
        }

        foreach ($icons['apple-touch-icon'] as $icon) {
            $file = 'favicons/appleicon_'.$icon.'.png';

            if (file_exists(JPATH_SITE.'/'.$file) && $icon == 180) {
                $href = Uri::root().$file.$option['pwa_version'];

                $doc->addCustomTag('<link rel="apple-touch-icon" href="'.$href.'">');
            }
        }

        $doc->setMetaData('mobile-web-app-capable', 'yes');
        $doc->setMetaData('apple-mobile-web-app-capable', 'yes');
        $doc->setMetaData('application-name', $app_name);
        $doc->setMetaData('apple-mobile-web-app-title', $app_name);
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function meta_ms(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params->get('use_color_scheme') == 1) {
            $doc->addCustomTag('<meta name="color-scheme" content="light dark">');
        }

        if ($params->get('theme_color')) {
            $doc->addCustomTag(
                '<meta name="theme-color" content="'.$params->get(
                    'theme_color'
                ).'" media="(prefers-color-scheme: light)">'
            );
        }

        if ($params->get('theme_color_dark')) {
            $doc->addCustomTag(
                '<meta name="theme-color" content="'.$params->get(
                    'theme_color_dark'
                ).'" media="(prefers-color-scheme: dark)">'
            );
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function icons(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();
        $icons = Data::$favicons;

        foreach ($icons['icon'] as $icon) {
            $file = 'favicons/icon_'.$icon.'.png';

            if (file_exists(JPATH_SITE.'/'.$file)) {
                $href = Uri::root().$file.$option['pwa_version'];

                $doc->addHeadLink($href, 'icon', 'rel', [
                    'sizes' => $icon.'x'.$icon,
                    'type' => 'image/png',
                ]);
            }
        }

        $file = 'favicons/favicon.ico';

        if (file_exists(JPATH_SITE.'/'.$file)) {
            $href = Uri::root().$file.$option['pwa_version'];

            $doc->addHeadLink($href, 'shortcut icon');
        }

        if ($params->get('source_icon_svg')) {
            $href = Uri::root().Render::image($params->get('source_icon_svg')).$option['pwa_version'];

            $doc->addHeadLink($href, 'icon', 'rel', [
                'type' => 'image/svg+xml',
            ]);
        }
    }

    /**
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function manifest(string $version): void
    {
        $app = Factory::getApplication();
        $doc = $app->getDocument();

        $file = 'manifest.webmanifest';

        if (file_exists(JPATH_SITE.'/'.$file)) {
            $href = Uri::root().$file.$version;

            $doc->addHeadLink($href, 'manifest', 'rel', [
                'crossorigin' => 'use-credentials',
            ]);
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function pwa(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params) {
            return;
        }

        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if ($params->get('usepwa', 0) == 1) {
            $pwajs = "if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('".Uri::base(
                )."sw.js".$option['pwa_version']."'); }); }";

            $doc->addScriptDeclaration($pwajs);
        }
    }
}
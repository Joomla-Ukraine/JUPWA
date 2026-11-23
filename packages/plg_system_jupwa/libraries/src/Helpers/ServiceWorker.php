<?php
/**
 * JUPWA plugin
 *
 * @version       2.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use JUPWA\Data\Data;
use JUPWA\Utils\Util;

class ServiceWorker
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
    public static function create(array $option = []): void
    {
        $app = Factory::getApplication();

        $params = $option['param'] ?? [];
        $apiKey = trim($params['apiKey'] ?? '');
        $projectId = trim($params['projectId'] ?? '');
        $messagingSenderId = trim($params['messagingSenderId'] ?? '');
        $appId = trim($params['appId'] ?? '');
        $usePwa = ($params['usepwa'] ?? 0) == 1;
        $usePush = ($params['usepush'] ?? 0) == 1;

        $import_scripts = "";
        if ($usePwa) {
            $import_scripts .= "importScripts('".Data::$workbox."');\n";
        }

        if ($usePush) {
            $import_scripts .= "importScripts('".Data::$firebase_app."');\n";
            $import_scripts .= "importScripts('".Data::$firebase_messaging."');\n";
        }

        if ($usePwa || $usePush) {
            $import_scripts .= "\n\n";
        }

        if ($usePwa) {
            $pwa_data = Util::tmpl('sw', [
                'pwa_version' => Manifest::getVersion(),
            ]);

            $pwa_firebase = '';
            if (
                $usePush &&
                $apiKey &&
                $projectId &&
                $messagingSenderId &&
                $appId
            ) {
                $pwa_firebase .= "\n\n";

                $pwa_firebase .= Util::tmpl('firebase-messaging-sw', [
                    'config' => [
                        'apiKey' => $apiKey,
                        'projectId' => $projectId,
                        'messagingSenderId' => $messagingSenderId,
                        'appId' => $appId,
                    ],
                ]);

                $pwa_firebase .= "\n\n";
            }

            file_put_contents(
                JPATH_SITE.'/sw.js',
                $import_scripts.$pwa_data.$pwa_firebase
            );

            $pwa_offline = Util::tmpl('offline', [
                'app' => $app,
            ]);

            file_put_contents(
                JPATH_SITE.'/offline.php',
                $pwa_offline
            );
        } else {
            if (file_exists(JPATH_SITE.'/sw.js')) {
                File::delete(JPATH_SITE.'/sw.js');

                $app->enqueueMessage('File sw.js deleted successfully.', 'error');
            }

            if (file_exists(JPATH_SITE.'/offline.php')) {
                File::delete(JPATH_SITE.'/offline.php');

                $app->enqueueMessage('File offline.php deleted successfully.', 'error');
            }
        }
    }
}

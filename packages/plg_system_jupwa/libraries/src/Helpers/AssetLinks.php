<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use JUPWA\Data\Data;

class AssetLinks
{
    /**
     *
     * @param array $option
     *
     * @return bool
     *
     * @since 1.0
     */
    public static function create(array $option = []): bool
    {
        $params = $option['param'] ?? [];

        if ((int)($params['use_assetlinks'] ?? 0) !== 1) {
            return false;
        }

        if (!file_exists(JPATH_ROOT.'/manifest.webmanifest')) {
            return false;
        }

        $folderPath = JPATH_SITE.'/.well-known';
        $filePath = $folderPath.'/assetlinks.json';

        if (!Folder::exists($folderPath) && !Folder::create($folderPath)) {
            return false;
        }

        $data = Data::$assetlinks ?? [];

        $data['relation'] = ['delegate_permission/common.handle_all_urls'];
        $data['target'] = [
            'namespace' => 'android_app',
            'package_name' => (string)($params['assetlinks_package_name'] ?? ''),
            'sha256_cert_fingerprints' => self::prepareFingerprints((string)($params['assetlinks_sha256'] ?? '')),
        ];

        $json = json_encode(
            [$data],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            return false;
        }

        return File::write(
            $filePath,
            $json
        );
    }

    /**
     * @param string $fingerprint
     * @return array
     *
     * @since 1.0
     */
    private static function prepareFingerprints(string $fingerprint): array
    {
        if (trim($fingerprint) === '') {
            return [];
        }

        return array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', $fingerprint)
                )
            )
        );
    }
}
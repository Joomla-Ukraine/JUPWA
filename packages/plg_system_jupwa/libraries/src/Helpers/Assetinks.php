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

class Assetinks
{
	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function create(array $option = []): void
	{
		$folder          = JPATH_SITE . '/.well-known';
		$assetlinks      = '/assetlinks.json';
		$file_assetlinks = $folder . $assetlinks;

		if($option[ 'param' ][ 'use_assetlinks' ] == 1 && file_exists(JPATH_ROOT . '/manifest.webmanifest'))
		{
			if(!(file_exists($folder) && is_dir($folder)))
			{
				Folder::create($folder);
			}

			$data               = Data::$assetlinks;
			$data[ 'relation' ] = [ 'delegate_permission/common.handle_all_urls' ];
			$data[ 'target' ]   = [
				'namespace'                => 'android_app',
				'package_name'             => $option[ 'param' ][ 'assetlinks_package_name' ],
				'sha256_cert_fingerprints' => [ $option[ 'param' ][ 'assetlinks_sha256' ] ],
			];

			$data = json_encode([ $data ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

			File::write($file_assetlinks, $data);
		}
	}
}
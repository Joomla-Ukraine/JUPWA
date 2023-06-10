<?php
/**
 * @package     JUPWA\Helpers
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Helpers;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;
use JUPWA\Data\Data;

class Assetinks
{
	/**
	 *
	 * @param   array  $option
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

		if($option[ 'param' ][ 'use_assetlinks' ] == 1 && File::exists(JPATH_ROOT . '/manifest.webmanifest'))
		{
			if(!Folder::exists($folder))
			{
				Folder::create($folder);
			}

			$data           = Data::$assetlinks;
			$data[ 'site' ] = Uri::root() . 'manifest.webmanifest';

			$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

			File::write($file_assetlinks, $data);
		}
	}
}
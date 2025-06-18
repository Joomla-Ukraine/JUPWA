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
use JUPWA\Data\Data;
use JUPWA\Utils\Util;

class ServiceWorkerFirebase
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
		if($option[ 'param' ][ 'usepwa' ] == 1)
		{
			$pwa_data = Util::tmpl('sw', [
				'workbox'     => Data::$workbox,
				'pwa_version' => Manifest::getVersion()
			]);

			file_put_contents(JPATH_SITE . '/firebase-messaging-sw.js', $pwa_data);
		}
		else
		{
			if(file_exists(JPATH_SITE . '/firebase-messaging-sw.js'))
			{
				File::delete(JPATH_SITE . '/firebase-messaging-sw.js');
			}
		}
	}
}
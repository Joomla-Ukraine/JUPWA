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

		if($option[ 'param' ][ 'usepwa' ] == 1)
		{
			$pwa_data = Util::tmpl('sw', [
				'workbox'     => Data::$workbox,
				'pwa_version' => Manifest::getVersion()
			]);

			file_put_contents(JPATH_SITE . '/sw.js', $pwa_data);

			$pwa_offline = Util::tmpl('offline', [
				'app' => $app
			]);

			file_put_contents(JPATH_SITE . '/offline.php', $pwa_offline);
		}
		else
		{
			if(file_exists(JPATH_SITE . '/sw.js'))
			{
				File::delete(JPATH_SITE . '/sw.js');
			}

			if(file_exists(JPATH_SITE . '/offline.php'))
			{
				File::delete(JPATH_SITE . '/offline.php');
			}
		}
	}
}
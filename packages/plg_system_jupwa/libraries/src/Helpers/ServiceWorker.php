<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
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

			$html        = '<div style="margin: 30px;align-content: center">' . date('Y') . ' &copy; With ♥️ <a href="https://joomla-ua.org">Joomla! Україна</a></div>';
			$pwa_offline = str_replace('</body>', $html, $pwa_offline);

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
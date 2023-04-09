<?php
/**
 * @package     JUPWA\Helpers
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
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
			if(File::exists(JPATH_SITE . '/sw.js'))
			{
				File::delete(JPATH_SITE . '/sw.js');
			}

			if(File::exists(JPATH_SITE . '/offline.php'))
			{
				File::delete(JPATH_SITE . '/offline.php');
			}
		}
	}
}
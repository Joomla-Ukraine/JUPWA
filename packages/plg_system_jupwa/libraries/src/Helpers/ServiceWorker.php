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
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Uri\Uri;
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
			$revision = [];
			$pwa_dirs = $option[ 'param' ][ 'pwa_dirs' ];
			foreach($pwa_dirs as $pwa_dir)
			{
				$pwa_folder = JPATH_SITE . '/' . ltrim($pwa_dir[ 'folder' ], '/');
				if(Folder::exists($pwa_folder))
				{
					switch($pwa_dir[ 'extensions' ])
					{
						case '1':
							$_pwa_files = glob($pwa_folder . '/{*.[cC][sS][sS]}', GLOB_BRACE);
							break;
						case '2':
							$_pwa_files = glob($pwa_folder . '/{*.[jJ][sS]}', GLOB_BRACE);
							break;
						case '3':
							$_pwa_files = glob($pwa_folder . '/{*.[jJ][pP][gG],*.[jJ][pP][eE][gG],*.[gG][iI][fF],*.[pP][nN][gG],*.[wW][eE][bB][pP],*.[sS][vV][gG]}', GLOB_BRACE);
							break;
						case '4':
							$_pwa_files = glob($pwa_folder . '/{*.[cC][sS][sS],*.[jJ][sS]}', GLOB_BRACE);
							break;
						case '5':
							$_pwa_files = glob($pwa_folder . '/{*.[cC][sS][sS],*.[jJ][sS],*.[jJ][pP][gG],*.[jJ][pP][eE][gG],*.[gG][iI][fF],*.[pP][nN][gG],*.[wW][eE][bB][pP],*.[sS][vV][gG]}', GLOB_BRACE);
							break;
						case '0':
						case 'default':
							$_pwa_files = glob($pwa_folder . '/*.*', GLOB_BRACE);
							break;
					}

					foreach($_pwa_files as $_pwa_file)
					{
						$path_pwa  = pathinfo($_pwa_file);
						$_pwa_file = str_replace(JPATH_SITE, Uri::root(true), $_pwa_file);
						if($path_pwa[ 'basename' ] !== 'index.html')
						{
							$revision[] = $_pwa_file;
						}
					}
				}
			}

			$pwa_data = Util::tmpl('sw', [
				'workbox'     => Data::$workbox,
				'pwa_version' => Manifest::getVersion(),
				'pwa_data'    => $revision
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
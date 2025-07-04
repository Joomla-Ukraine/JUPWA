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

		$apiKey            = trim($option[ 'param' ][ 'apiKey' ]) ?? '';
		$projectId         = trim($option[ 'param' ][ 'projectId' ]) ?? '';
		$messagingSenderId = trim($option[ 'param' ][ 'messagingSenderId' ]) ?? '';
		$appId             = trim($option[ 'param' ][ 'appId' ]) ?? '';

		if($option[ 'param' ][ 'usepwa' ] == 1)
		{
			$pwa_data = Util::tmpl('sw', [
				'workbox'     => Data::$workbox,
				'pwa_version' => Manifest::getVersion()
			]);

			$pwa_firebase = '';
			if($option[ 'param' ][ 'usepush' ] == 1 && $apiKey && $projectId && $messagingSenderId && $appId)
			{
				$pwa_firebase = Util::tmpl('firebase-messaging-sw', [
					'firebase_app'       => Data::$firebase_app,
					'firebase_messaging' => Data::$firebase_messaging,
					'config'             => [
						'apiKey'            => $apiKey,
						'projectId'         => $projectId,
						'messagingSenderId' => $messagingSenderId,
						'appId'             => $appId,
					]
				]);

				$pwa_firebase .= "\n\n";
			}

			file_put_contents(JPATH_SITE . '/sw.js', $pwa_data . $pwa_firebase);

			$pwa_offline = Util::tmpl('offline', [
				'app' => $app
			]);

			file_put_contents(JPATH_SITE . '/offline.php', $pwa_offline);

			Factory::getApplication()->enqueueMessage('File sw.js created successfully.', 'message');
			Factory::getApplication()->enqueueMessage('File offline.php created successfully.', 'message');
		}
		else
		{
			if(file_exists(JPATH_SITE . '/sw.js'))
			{
				File::delete(JPATH_SITE . '/sw.js');

				Factory::getApplication()->enqueueMessage('File sw.js deleted successfully.', 'error');
			}

			if(file_exists(JPATH_SITE . '/offline.php'))
			{
				File::delete(JPATH_SITE . '/offline.php');

				Factory::getApplication()->enqueueMessage('File offline.php deleted successfully.', 'error');
			}
		}
	}
}
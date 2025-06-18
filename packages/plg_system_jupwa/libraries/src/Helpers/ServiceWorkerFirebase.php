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
		$apiKey            = trim($option[ 'param' ][ 'apiKey' ]) ?? '';
		$projectId         = trim($option[ 'param' ][ 'projectId' ]) ?? '';
		$messagingSenderId = trim($option[ 'param' ][ 'messagingSenderId' ]) ?? '';
		$appId             = trim($option[ 'param' ][ 'appId' ]) ?? '';

		if($option[ 'param' ][ 'usepush' ] == 1 && $apiKey && $projectId && $messagingSenderId && $appId)
		{
			$pwa_data = Util::tmpl('firebase-messaging-sw', [
				'firebase_app'       => Data::$firebase_app,
				'firebase_messaging' => Data::$firebase_messaging,
				'config'             => [
					'apiKey'            => $apiKey,
					'projectId'         => $projectId,
					'messagingSenderId' => $messagingSenderId,
					'appId'             => $appId,
				]
			]);

			file_put_contents(JPATH_SITE . '/firebase-messaging-sw.js', $pwa_data);

			Factory::getApplication()->enqueueMessage('File firebase-messaging-sw.js created successfully.', 'message');
		}
		else
		{
			if(file_exists(JPATH_SITE . '/firebase-messaging-sw.js'))
			{
				File::delete(JPATH_SITE . '/firebase-messaging-sw.js');

				Factory::getApplication()->enqueueMessage('File firebase-messaging-sw.js deleted successfully.', 'message');
			}
		}
	}
}
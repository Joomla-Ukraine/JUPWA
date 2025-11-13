<?php
/**
 * @package     JUPWA\Push
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Push;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use JUPWA\Helpers\Manifest;
use JUPWA\Utils\Util;

class Push
{
	/**
	 *
	 * @param string $token
	 * @param string $title
	 * @param string $body
	 * @param string $domain
	 * @param string $link
	 *
	 * @return array
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \JsonException
	 * @since 1.0
	 */
	public static function send(string $token, string $title, string $body = '', string $domain = '', string $link = ''): array
	{
		$serviceAccountFile = JPATH_ROOT . '/.well-known/jupwa/firebase-service-account.json';
		$scopes             = [ 'https://www.googleapis.com/auth/firebase.messaging' ];
		$credentials        = new ServiceAccountCredentials($scopes, $serviceAccountFile);
		$authToken          = $credentials->fetchAuthToken();

		if(empty($authToken[ 'access_token' ]))
		{
			throw new \RuntimeException('Access token error');
		}

		$accessToken = $authToken[ 'access_token' ];
		$json        = json_decode(file_get_contents($serviceAccountFile), true, 512, JSON_THROW_ON_ERROR);
		$projectId   = $json[ 'project_id' ];

		$client = new Client([
			'base_uri' => 'https://fcm.googleapis.com/',
			'headers'  => [
				'Authorization' => 'Bearer ' . $accessToken,
				'Content-Type'  => 'application/json',
			],
		]);

		$domain = $domain ? : Uri::base();
		$domain = str_replace(JPATH_ROOT . '/cli', '', $domain);

		$data = [
			'json' => [
				'message' => [
					'token' => $token,
					'data'  => [
						'title'        => $title,
						'body'         => $body,
						'image'        => $domain . 'favicons/icon_192.png',
						'click_action' => $link ? : $domain
					],
					'apns'  => [
						'headers' => [
							'apns-priority'  => '10',
							'apns-push-type' => 'alert'
						],
						'payload' => [
							'aps' => [
								'alert'           => [
									'title' => $title,
									'body'  => $body,
								],
								'sound'           => 'default',
								'badge'           => 1,
								'mutable-content' => 1
							]
						],
					],
				]
			]
		];

		$response = $client->post('v1/projects/' . $projectId . '/messages:send', $data);

		return json_decode($response->getBody(), true, 512, JSON_THROW_ON_ERROR);
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function render(array $option = []): void
	{
		if(self::isPush($option))
		{
			$doc         = Factory::getApplication()->getDocument();
			$pwa_version = Manifest::getVersion();

			$pwapush = [
				'firebase'     => [
					'apiKey'            => $option[ 'params' ][ 'apiKey' ],
					'projectId'         => $option[ 'params' ][ 'projectId' ],
					'messagingSenderId' => $option[ 'params' ][ 'messagingSenderId' ],
					'appId'             => $option[ 'params' ][ 'appId' ],
					'vapidKey'          => $option[ 'params' ][ 'vapidKey' ]
				],
				'csrf'         => Session::getFormToken(),
				'sw'           => Uri::base() . 'sw.js?v=' . $pwa_version,
				'api'          => [
					'subscribe'   => Uri::base() . 'index.php?option=com_ajax&plugin=JUPWAPushSubscribe&format=json',
					'unsubscribe' => Uri::base() . 'index.php?option=com_ajax&plugin=JUPWAPushUnsubscribe&format=json'
				],
				'localisation' => [
					'addToMainDisplay' => Text::_('PLG_JUPWA_ADD_TO_MAIN_DISPLAY'),
					'notSupport'       => Text::_('PLG_JUPWA_NOT_SUPPORT'),
					'notGranted'       => Text::_('PLG_JUPWA_NOT_GRANTED'),
					'swNotSupport'     => Text::_('PLG_JUPWA_SW_NOT_SUPPORT'),
					'subscribe'        => Text::_('PLG_JUPWA_SUBSCRIBE'),
					'unsubscribe'      => Text::_('PLG_JUPWA_UNSUBSCRIBE'),
					'tokenNotLoad'     => Text::_('PLG_JUPWA_TOKEN_NOT_LOAD'),
					'tokenNotFound'    => Text::_('PLG_JUPWA_TOKEN_NOT_FOUND'),
					'permissionDenied' => Text::_('PLG_JUPWA_PERMISION_DENIED')
				]
			];
			$pwapush = json_encode($pwapush);

			$doc->addCustomTag('<script id="jupwa-push-setting" type="application/json">' . $pwapush . '</script>');
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function isPush(array $option = []): bool
	{
		if($option[ 'params' ][ 'usepush' ] == 1)
		{
			if($option[ 'params' ][ 'usepush_for_jusers' ] == 1)
			{
				$user = Factory::getApplication()->getIdentity();

				if($user->guest == 1)
				{
					return false;
				}
			}

			$data = [
				$option[ 'params' ][ 'firebaseServiceAccount' ],
				$option[ 'params' ][ 'apiKey' ],
				$option[ 'params' ][ 'projectId' ],
				$option[ 'params' ][ 'messagingSenderId' ],
				$option[ 'params' ][ 'appId' ],
				$option[ 'params' ][ 'vapidKey_wp' ]
			];

			if(!Util::checkFields($data))
			{
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function checkAjaxPlugin(): void
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		$query->select([ 'extension_id', 'enabled' ]);
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('name') . ' = ' . $db->Quote('plg_ajax_jupwapush'));
		$query->where($db->quoteName('folder') . ' = ' . $db->Quote('ajax'));
		$db->setQuery($query);
		$db->execute();
		$status = $db->loadObject();

		if($status->enabled == 0)
		{
			$object               = new \stdClass();
			$object->extension_id = $status->extension_id;
			$object->enabled      = 1;
			$result               = $db->updateObject('#__extensions', $object, 'extension_id', true);

			if($result)
			{
				Factory::getApplication()->enqueueMessage('Enable Ajax Plugin "JUPWA. Push"');
			}
		}
	}
}
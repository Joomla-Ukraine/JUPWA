<?php
/**
 * @package     JUPWA\Push
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Push;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use Joomla\CMS\Uri\Uri;

class Push
{
	/**
	 *
	 * @param string $token
	 * @param string $title
	 * @param string $body
	 * @param array  $data
	 *
	 * @return array
	 *
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @since 1.0
	 */
	public static function send(string $token, string $title, string $body, array $data = []): array
	{
		$serviceAccountFile = JPATH_ROOT . '/.well-known/jupwa/firebase-service-account.json';
		$scopes             = [ 'https://www.googleapis.com/auth/firebase.messaging' ];
		$credentials        = new ServiceAccountCredentials($scopes, $serviceAccountFile);
		$authToken          = $credentials->fetchAuthToken();

		if(empty($authToken[ 'access_token' ]))
		{
			throw new Exception('Не удалось получить access token');
		}

		$accessToken = $authToken[ 'access_token' ];
		$json        = json_decode(file_get_contents($serviceAccountFile), true);
		$projectId   = $json[ 'project_id' ];

		$client = new Client([
			'base_uri' => 'https://fcm.googleapis.com/',
			'headers'  => [
				'Authorization' => 'Bearer ' . $accessToken,
				'Content-Type'  => 'application/json',
			],
		]);

		$response = $client->post('v1/projects/' . $projectId . '/messages:send', [
			'json' => [
				'message' => [
					'token'        => $token,
					'notification' => [
						'title' => $title,
						'body'  => $body,
					],
					'data'         => $data,
					'android'      => [
						'notification' => [
							'image' => Uri::base() . 'favicons/icon_180.png'
						]
					],
					'webpush'      => [
						'notification' => [
							'image' => Uri::base() . 'favicons/icon_180.png'
						]
					],
					'apns'         => [
						'fcm_options' => [
							'image' => Uri::base() . 'favicons/icon_180.png'
						]
					]
				]
			]
		]);

		return json_decode($response->getBody(), true);
	}
}
<?php
/**
 * @package     JUPWA\Push
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Push;

use Curl\Curl;

class Push
{
	/**
	 *
	 * @param string $serverKey
	 * @param string $token
	 * @param string $title
	 * @param string $body
	 * @param array  $data
	 *
	 * @return array
	 *
	 * @throws \JsonException
	 * @since 1.0
	 */
	public static function send(string $serverKey, string $token, string $title, string $body, array $data = []): array
	{
		$url = 'https://fcm.googleapis.com/fcm/send';

		$curl = new Curl();
		$curl->setHeader('Authorization', 'key=' . $serverKey);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, [
			'to'           => $token,
			'notification' => [
				'title' => $title,
				'body'  => $body,
			],
			'data'         => $data,
			'priority'     => 'high',
		]);

		if($curl->error)
		{
			throw new \Exception('Curl failed: ' . $curl->getErrorMessage());
		}

		$result = $curl->response;

		return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
	}
}
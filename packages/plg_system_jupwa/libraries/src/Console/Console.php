<?php
/**
 * @package     JUPWA\Console
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Console;

use Exception;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use JUPWA\Push\Push;
use stdClass;

class Console
{
	/**
	 *
	 * @param array $result
	 *
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @since 1.0.0
	 */
	public static function send(array $result = []): mixed
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$items = self::tokens($result[ 'user' ]);

		foreach($items as $item)
		{
			try
			{
				$title = $result[ 'title' ] ? : '';
				$desc  = $result[ 'desc' ] ? : '';
				$link  = $result[ 'link' ] ? : '';

				Push::send($item->fcm_token, $title, $desc, $link);
			}
			catch (Exception $e)
			{
				self::remove_tokens($item->fcm_token);
			}
		}

		$order               = new stdClass();
		$order->status       = 1;
		$order->object_group = 'com_content';
		$order->object_id    = $result[ 'id' ];
		$order->order_url    = $result[ 'link' ];

		return $db->insertObject('#__jupwa_push_orders', $order);
	}

	/**
	 *
	 * @param string $token
	 *
	 * @since 1.0.0
	 */
	private static function remove_tokens(string $token): void
	{
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__jupwa_push_users'));
		$query->where($db->quoteName('fcm_token') . ' = ' . $db->Quote($token));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 *
	 * @param int $user
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public static function tokens(int $user = 0): array
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		$query->select([ 'fcm_token' ]);
		$query->from($db->quoteName('#__jupwa_push_users'));
		$query->where($db->quoteName('user_id') . ' = ' . $db->Quote($user));
		$db->setQuery($query);
		$db->execute();

		return $db->loadObjectList();
	}

	/**
	 *
	 * @param array $where
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public static function check(array $where = []): int
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$query = $db->getQuery(true);
		$query->select([ '*' ]);
		$query->from('#__jupwa_push_orders');

		foreach($where as $key => $value)
		{
			$query->where($db->quoteName($key) . '=' . $db->Quote($value));
		}

		$db->setQuery($query);
		$db->execute();

		return $db->getNumRows();
	}

	/**
	 *
	 * @param $params
	 * @param $input
	 * @param $command
	 *
	 * @return string|null
	 * @since 1.0.0
	 */
	public static function command($params, $input, $command): ?string
	{
		$option = $params->get($command, null);
		if($input->getOption($command))
		{
			$option = $input->getOption($command);
			$option = ltrim($option, "=");
		}

		return $option;
	}
}
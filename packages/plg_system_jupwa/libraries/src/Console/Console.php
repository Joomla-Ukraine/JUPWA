<?php
/**
 * @package     JUPWA\Console
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Console;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use stdClass;

class Console
{
	/**
	 *
	 * @param array $result
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public static function send(array $result = [])
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$order               = new stdClass();
		$order->status       = 1;
		$order->object_group = 'com_content';
		$order->object_id    = $result[ 'id' ];
		$order->order_url    = $result[ 'link' ];

		$resultID = $db->insertObject('#__jupwa_push_orders', $order);

		return $resultID;
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
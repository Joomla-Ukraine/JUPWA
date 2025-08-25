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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JUPWAPush script file.
 *
 * @since     1.0.0
 * @package   jupwapush
 */
class plgAjaxJUPWAPushInstallerScript
{
	/**
	 * Called during installation
	 *
	 * @param JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @return  bool  True on success
	 * @throws \Exception
	 * @since     1.0.0
	 */
	public function install($adapter): bool
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$query = "CREATE TABLE IF NOT EXISTS `#__jupwa_push_users` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL DEFAULT '0',
    		`fcm_token` varchar(500) NOT NULL,
            `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
    		KEY `user_id` (`user_id`),
    		KEY `fcm_token` (`fcm_token`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		try
		{
			$db->setQuery($query)->execute();

			Factory::getApplication()->enqueueMessage('Table #__jupwa_push_users created successfully.', 'message');
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage('Failed to create table: ' . $e->getMessage(), 'error');

			return false;
		}

		$query = "CREATE TABLE IF NOT EXISTS `#__jupwa_push_orders` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL DEFAULT 0,
    		`status` INT(11) NOT NULL DEFAULT 0,
      		`object_group` VARCHAR(155) DEFAULT NULL,
    		`object_id` INT(11) NOT NULL DEFAULT 0,
    		`order_id` INT(11) NOT NULL DEFAULT 0,
    		`order_url` VARCHAR(500) DEFAULT NULL,
            `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
    		KEY `user_id` (`user_id`),
    		KEY `status` (`status`),
    		KEY `object_group` (`object_group`),
    		KEY `object_id` (`object_id`),
    		KEY `order_id` (`order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		try
		{
			$db->setQuery($query)->execute();

			Factory::getApplication()->enqueueMessage('Table #__jupwa_push_orders created successfully.', 'message');
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage('Failed to create table: ' . $e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Called during installation
	 *
	 * @param JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @return  bool  True on success
	 * @throws \Exception
	 * @since     1.0.0
	 */
	public function update($adapter): bool
	{
		$db      = Factory::getContainer()->get(DatabaseInterface::class);
		$columns = $db->getTableColumns('#__jupwa_push_orders');

		if(isset($columns[ 'order_desc' ]))
		{
			$query = "ALTER TABLE `#__jupwa_push_orders` DROP `order_desc`";
			$db->setQuery($query);
			$db->execute();
		}

		if(isset($columns[ 'user_id' ]))
		{
			$columnInfo = $columns[ 'user_id' ];

			if(!(stripos($columnInfo->Type, 'int(11)') !== false && $columnInfo->Null === 'NO'))
			{
				$query = "ALTER TABLE `#__jupwa_push_orders` CHANGE `user_id` `user_id` INT(11) NOT NULL DEFAULT 0";
				$db->setQuery($query);
				$db->execute();
			}
		}

		if(isset($columns[ 'object_group' ]))
		{
			$columnInfo = $columns[ 'object_group' ];

			if(!(stripos($columnInfo->Type, 'varchar(155)') !== false && $columnInfo->Null === 'YES' && $columnInfo->Default === null))
			{
				$query = "ALTER TABLE `#__jupwa_push_orders` CHANGE `object_group` `object_group` VARCHAR(155) DEFAULT NULL";
				$db->setQuery($query);
				$db->execute();
			}
		}

		if(isset($columns[ 'order_id' ]))
		{
			$columnInfo = $columns[ 'order_id' ];

			if(!(stripos($columnInfo->Type, 'int(11)') !== false && $columnInfo->Null === 'NO'))
			{
				$query = "ALTER TABLE `#__jupwa_push_orders` CHANGE `order_id` `order_id` INT(11) NOT NULL DEFAULT 0";
				$db->setQuery($query);
				$db->execute();
			}
		}

		if(isset($columns[ 'order_url' ]))
		{
			$columnInfo = $columns[ 'order_url' ];

			if(!(stripos($columnInfo->Type, 'varchar(155)') !== false && $columnInfo->Null === 'YES' && $columnInfo->Default === null))
			{
				$query = "ALTER TABLE `#__jupwa_push_orders` CHANGE `order_url` `order_url` VARCHAR(155) DEFAULT NULL";
				$db->setQuery($query);
				$db->execute();
			}
		}

		return true;
	}

	/**
	 * Called during uninstallation
	 *
	 * @param JAdapterInstance $adapter The object responsible for running this script
	 *
	 * @return  bool  True on success
	 * @throws \Exception
	 * @since     1.0.0
	 */
	public function uninstall($adapter): bool
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		try
		{
			$query = "DROP TABLE IF EXISTS `#__jupwa_push_users`;";
			$db->setQuery($query)->execute();

			Factory::getApplication()->enqueueMessage('Table #__jupwa_push_users dropped successfully.', 'message');
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage('Failed to drop table: ' . $e->getMessage(), 'error');

			return false;
		}

		try
		{
			$query = "DROP TABLE IF EXISTS `#__jupwa_push_orders`;";
			$db->setQuery($query)->execute();

			Factory::getApplication()->enqueueMessage('Table #__jupwa_push_orders dropped successfully.', 'message');
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage('Failed to drop table: ' . $e->getMessage(), 'error');

			return false;
		}

		return true;
	}
}

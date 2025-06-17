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

defined('_JEXEC') or die;

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
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
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
            `user_id` INT(11) NOT NULL,
    		`fcm_token` varchar(255) NOT NULL,
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
            `user_id` INT(11) NOT NULL,
    		`status` INT(11) NOT NULL DEFAULT 0,
      		`object_group` VARCHAR(155) NOT NULL,
    		`order_id` INT(11) NOT NULL,
    		`order_desc` VARCHAR(155) NOT NULL,
    		`order_url` VARCHAR(500) NOT NULL,
            `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
    		KEY `user_id` (`user_id`),
    		KEY `status` (`status`),
    		KEY `object_group` (`object_group`),
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
	 * Called during uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
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

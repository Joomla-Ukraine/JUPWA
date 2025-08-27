<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWASeblod
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JU\Plugin\Console\JUPWASeblod\Extension\JUPWASeblod;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param Container $container The DI container.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function register(Container $container)
	{
		$container->set(PluginInterface::class, function (Container $container)
		{
			$dispatcher = $container->get(DispatcherInterface::class);
			$plugin     = new JUPWASeblod($dispatcher, (array) PluginHelper::getPlugin('console', 'jupwa_seblod'));

			$plugin->setApplication(Factory::getApplication());

			return $plugin;
		});
	}
};

<?php
/**
 * JUPWAPush plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JU\Plugin\Ajax\JUPWAPush\Extension\Push;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

return new class() implements ServiceProviderInterface {

	/**
	 * @param   \Joomla\DI\Container  $container
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	public function register(Container $container): void
	{
		$container->set(PluginInterface::class, function (Container $container)
		{
			$config  = (array) PluginHelper::getPlugin('ajax', 'jupwapush');
			$subject = $container->get(DispatcherInterface::class);
			$app     = Factory::getApplication();

			$plugin = new Push($subject, $config);
			$plugin->setApplication($app);

			return $plugin;
		});
	}
};
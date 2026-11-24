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

defined('_JEXEC') or die;

return new class implements ServiceProviderInterface {
    /**
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function register(Container $container): void
    {
        $container->set(PluginInterface::class, function (Container $container) {
            $dispatcher = $container->get(DispatcherInterface::class);
            $config = (array)PluginHelper::getPlugin('console', 'jupwa_seblod');

            if (version_compare(JVERSION, '6.0.0', '<')) {
                $plugin = new JUPWASeblod($dispatcher, $config);
            } else {
                $plugin = new JUPWASeblod($config);
                $plugin->setDispatcher($dispatcher);
            }

            $plugin->setApplication(Factory::getApplication());

            return $plugin;
        });
    }
};

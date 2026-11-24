<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWAContent
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JU\Plugin\Console\JUPWAContent\Extension\JUPWAContent;

return new class implements ServiceProviderInterface {
    /**
     * @param Container $container The DI container.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function register(Container $container)
    {
        $container->set(PluginInterface::class, function (Container $container) {
            $dispatcher = $container->get(DispatcherInterface::class);
            $plugin = new JUPWAContent($dispatcher, (array)PluginHelper::getPlugin('console', 'jupwa_content'));

            $plugin->setApplication(Factory::getApplication());

            return $plugin;
        });
    }
};

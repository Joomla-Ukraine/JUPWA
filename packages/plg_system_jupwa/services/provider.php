<?php
/**
 * JUPWA plugin
 *
 * @version       2.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JU\Plugin\System\JUPWA\Extension\JUPWA;

defined('_JEXEC') or die;

require_once dirname(__DIR__).'/libraries/vendor/autoload.php';

return new class implements ServiceProviderInterface {

    /**
     * @param Container $container
     *
     *
     * @since 2.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            static function (Container $container): PluginInterface {
                $config = (array)PluginHelper::getPlugin('system', 'jupwa');
                $dispatcher = $container->get(DispatcherInterface::class);

                if (version_compare(JVERSION, '6.0.0', '<')) {
                    $plugin = new JUPWA($dispatcher, $config);
                } else {
                    $plugin = new JUPWA($config);
                    $plugin->setDispatcher($dispatcher);
                }

                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};

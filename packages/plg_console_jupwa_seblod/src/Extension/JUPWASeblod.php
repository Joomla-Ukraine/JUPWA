<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWASeblod
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWASeblod\Extension;

use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use JU\Plugin\Console\JUPWASeblod\Console\JUPWASeblodCommand;

defined('_JEXEC') or die;

final class JUPWASeblod extends CMSPlugin implements SubscriberInterface
{
    /**
     *
     * @return string[]
     *
     * @since 2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
        ];
    }

    /**
     *
     *
     * @since 2.0
     */
    public function registerCommands(): void
    {
        $myCommand = new JUPWASeblodCommand();
        $myCommand->setParams($this->params);
        $this->getApplication()->addCommand($myCommand);
    }
}

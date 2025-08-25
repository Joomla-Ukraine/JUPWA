<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWAContent
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWAContent\Extension;

use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use JU\Plugin\Console\JUPWAContent\Console\JUPWAContentCommand;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

final class JUPWAContent extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Returns the event this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
		];
	}

	/**
	 * Returns the command class for the JUPWAContent CLI plugin.
	 *
	 * @return  void
	 */
	public function registerCommands(): void
	{
		$myCommand = new JUPWAContentCommand();
		$myCommand->setParams($this->params);
		$this->getApplication()->addCommand($myCommand);
	}
}
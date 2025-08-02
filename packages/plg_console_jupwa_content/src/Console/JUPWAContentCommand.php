<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWAContent
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWAContent\Console;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @property \Joomla\CMS\Date\Date $date
 * @property string                $nowdate
 */
class JUPWAContentCommand extends AbstractCommand
{
	use DatabaseAwareTrait;

	/**
	 * The default command name
	 *
	 * @since  1.0.0
	 * @var    string
	 *
	 */
	protected static $defaultName = 'jupwa_content:run';

	/**
	 * @since 1.0.0
	 * @var InputInterface
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @since 1.0.0
	 * @var SymfonyStyle
	 */
	private $ioStyle;

	/**
	 * @since 1.0.0
	 */
	protected $params;

	/**
	 * @since 1.0.0
	 */
	protected function getParams()
	{
		return $this->params;
	}

	/**
	 * @param $params
	 *
	 * @since 1.0.0
	 */
	public function setParams($params): void
	{
		$this->params = $params;
	}

	public function __construct(?string $name = null)
	{
		parent::__construct($name);

		$this->db      = Factory::getContainer()->get(DatabaseInterface::class);
		$this->date    = Factory::getDate();
		$this->nowdate = $this->date->toSql();
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   1.0.0
	 */
	protected function configure(): void
	{
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('plg_console_jupwa_content', JPATH_BASE . '/plugins/console/jupwa_content');

		$this->addArgument('action', InputArgument::REQUIRED, 'name of action');

		$this->setDescription(Text::_('PLG_CONSOLE_JUPWACONTENT_DESCRIPTION'));
	}

	/**
	 * Internal function to execute the command.
	 *
	 * @param InputInterface  $input  The input to inject into the command.
	 * @param OutputInterface $output The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @throws \Exception
	 * @since   1.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);
		$action = $this->cliInput->getArgument('action');

		$adminApp             = Factory::getContainer()->get(SiteApplication::class);
		Factory::$application = $adminApp;

		switch($action)
		{
			case 'last':
			case 'random':
				$this->content($action);
				break;
			default:
				$this->ioStyle->error("Unknwon action: $action");
				break;
		}

		$output->writeln('<info>successfully</info>');

		return true;
	}

	/**
	 * @since 1.0.0
	 */
	private function content($action): void
	{
	}

	/**
	 * Configures the IO
	 *
	 * @param InputInterface  $input  Console Input
	 * @param OutputInterface $output Console Output
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 */
	private function configureIO(InputInterface $input, OutputInterface $output): void
	{
		$this->cliInput = $input;
		$this->ioStyle  = new SymfonyStyle($input, $output);
	}
}
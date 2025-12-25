<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWAContent
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWAContent\Console;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use JUPWA\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * @since  1.0.0
 * @property string                                               $nowdate
 * @property \Joomla\CMS\Date\Date                                $date
 * @property mixed                                                $db
 * @property                                                      $query
 * @property \Joomla\CMS\Language\Language                        $lang
 * @property \Joomla\CMS\Application\CMSApplicationInterface|null $app
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
	private InputInterface $cliInput;

	/**
	 * SymfonyStyle Object
	 * @since 1.0.0
	 * @var SymfonyStyle
	 */
	private SymfonyStyle $ioStyle;

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

		$this->app     = Factory::getApplication();
		$this->db      = Factory::getContainer()->get(DatabaseInterface::class);
		$this->query   = $this->db->getQuery(true);
		$this->date    = Factory::getDate();
		$this->nowdate = $this->date->toSql();
		$this->lang    = $this->app->getLanguage();
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
		$this->addOption('live_site', "s", InputOption::VALUE_OPTIONAL, "live_site");
		$this->addOption('catid', "c", InputOption::VALUE_OPTIONAL, "catid");
		$this->addOption('featured', "f", InputOption::VALUE_OPTIONAL, "featured");

		$this->setDescription(Text::_('PLG_CONSOLE_JUPWA_CONTENT_DESCRIPTION'));
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

		$params  = $this->getParams();
		$options = [
			'action'    => $action,
			'live_site' => Console::command($params, $input, 'live_site'),
			'catid'     => Console::command($params, $input, 'catid'),
			'featured'  => Console::command($params, $input, 'featured')
		];

		switch($action)
		{
			case 'last':
			case 'random':
				$this->content($options);
				break;
			default:
				$this->ioStyle->error("Unknwon action: $action");
				break;
		}

		$output->writeln('<info>successfully</info>');

		return true;
	}

	/**
	 *
	 * @param array $options
	 *
	 * @return void
	 * @throws \Exception
	 * @since 1.0.0
	 */
	private function content(array $options = []): void
	{
		$live_site = $options[ "live_site" ];

		$items = $this->sql_query($options);
		foreach($items as $item)
		{
			$slug     = $item->id . ($item->alias ? ':' . $item->alias : '');
			$language = (Multilanguage::isEnabled() ? $item->language : '');
			$link     = Route::_(RouteHelper::getArticleRoute($slug, $item->catid, $language));
			$link     = str_replace(JPATH_CLI, '', $link);
			$link     = str_replace('set/by/console/application/', '', $link);
			$link     = $live_site . $link;

			$check = Console::check([
				'status'       => 1,
				'object_group' => 'com_content',
				'object_id'    => $item->id,
				'order_url'    => $link
			]);

			if($check == 0)
			{
				$result = [
					'user'  => 0,
					'id'    => $item->id,
					'title' => $item->category,
					'desc'  => $item->title,
					'link'  => $link
				];

				Console::send($result);
			}
		}
	}

	/**
	 *
	 * @param $options
	 *
	 * @return array
	 * @since 1.0.0
	 */
	private function sql_query($options): array
	{
		$action   = $options[ "action" ];
		$catid    = $options[ "catid" ];
		$featured = $options[ "featured" ];

		$this->query->select([
			'a.id',
			'a.state',
			'a.alias',
			'a.title',
			'cc.title AS category'
		]);
		$this->query->where($this->db->quoteName('a.state') . ' = ' . $this->db->Quote('1'));

		$cat_arr = [];
		if($catid)
		{
			$cat_arr[] = $catid;
		}

		if(is_array($catid))
		{
			$cat_arr = [];
			foreach($catid as $key => $curr)
			{
				if((int) $curr)
				{
					$cat_arr[ $key ] = (int) $curr;
				}
			}
		}

		if(is_array($catid))
		{
			$this->query->select([ 'a.catid' ]);
		}

		if(Multilanguage::isEnabled())
		{
			$this->query->select([ 'a.language' ]);
			$this->query->where($this->db->quoteName('a.language') . ' IN (' . $this->db->Quote($this->lang->getTag()) . ',' . $this->db->quote('*') . ')');
		}

		if($featured == 1)
		{
			$this->query->select([ 'a.featured' ]);
		}

		$this->query->from('#__content AS a');

		if(is_array($cat_arr) && count($cat_arr))
		{
			$this->query->where($this->db->quoteName('a.catid') . ' IN (' . implode(',', $cat_arr) . ')');
		}

		$this->query->join('LEFT', '#__categories AS cc ON cc.id = a.catid');

		$this->query->where($this->db->quoteName('a.featured') . ' = ' . $this->db->Quote($featured));

		$ordering = 'a.created DESC';
		if($action === 'random')
		{
			$ordering = 'rand()';
		}

		$this->query->order($ordering);
		$this->db->setQuery($this->query, 0, 1);

		return $this->db->loadObjectList();
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
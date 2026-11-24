<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWASeblod
 *
 * @copyright   Copyright (C) 2025 Denes Nosov.
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWASeblod\Console;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use JUPWA\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

defined('_JEXEC') or die;

class JUPWASeblodCommand extends AbstractCommand
{
    protected static $defaultName = 'jupwa_seblod:run';

    private Registry $params;

    public function setParams(Registry $params): void
    {
        $this->params = $params;
    }

    /**
     *
     *
     * @throws Exception
     * @since 2.0
     */
    protected function configure(): void
    {
        Factory::getApplication()->getLanguage()->load('plg_console_jupwa_seblod', JPATH_ADMINISTRATOR);

        $this->addArgument('action', InputArgument::REQUIRED, 'last or random');
        $this->addOption('live_site', 's', InputOption::VALUE_REQUIRED, 'Public site URL');
        $this->addOption('catid', 'c', InputOption::VALUE_REQUIRED, 'Category ID');
        $this->addOption('featured', 'f', InputOption::VALUE_REQUIRED, 'Featured filter (0 or 1)');
        $this->addOption('seblod_type', null, InputOption::VALUE_REQUIRED, 'SEBLOD content type name');
        $this->addOption(
            'seblod_menuitem',
            null,
            InputOption::VALUE_REQUIRED,
            'Site menu item ID (0 for automatic routing)'
        );

        $this->setDescription(Text::_('PLG_CONSOLE_JUPWA_SEBLOD_DESCRIPTION'));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws GuzzleException
     * @throws Exception
     * @since 2.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        if (!in_array($action, ['last', 'random'], true)) {
            $io->error("Unknown action: $action");

            return 2;
        }

        if (!ComponentHelper::isEnabled('com_cck') || !class_exists(Console::class)) {
            $io->error('Enable SEBLOD (com_cck) and the JUPWA system plugin.');

            return 1;
        }

        $app = Factory::getApplication();
        $joomlaLiveSite = $input->hasOption('live-site') ? $input->getOption('live-site') : null;
        $liveSite = $input->getOption('live_site') ?? ($this->params->get('live_site') ?: $joomlaLiveSite ?: $app->get(
            'live_site'
        ));
        $liveSite = rtrim(trim((string)$liveSite), '/');
        $url = parse_url($liveSite);

        if (!filter_var($liveSite, FILTER_VALIDATE_URL)
            || !in_array(strtolower($url['scheme'] ?? ''), ['http', 'https'], true)
            || strcasecmp($url['host'] ?? '', 'joomla.invalid') === 0
            || array_intersect(['user', 'pass', 'query', 'fragment'], array_keys($url ?: []))) {
            $io->error('Set a public HTTP or HTTPS site URL with --live_site or in the plugin settings.');

            return 2;
        }

        $categoryIds = (array)($input->getOption('catid') ?? $this->params->get('catid'));
        $featured = (string)($input->getOption('featured') ?? $this->params->get('featured', 0));
        $type = trim((string)($input->getOption('seblod_type') ?? $this->params->get('seblod_type')));
        $menuId = (string)($input->getOption('seblod_menuitem') ?? $this->params->get('seblod_menuitem') ?? '');

        foreach ($categoryIds as $categoryId) {
            if ((string)$categoryId !== '' && !ctype_digit((string)$categoryId)) {
                $io->error('Category IDs must be non-negative integers.');

                return 2;
            }
        }

        if (!in_array($featured, ['0', '1'], true) || ($menuId !== '' && !ctype_digit($menuId))) {
            $io->error('Use 0 or 1 for --featured and a non-negative integer for --seblod_menuitem.');

            return 2;
        }

        $menuId = (int)$menuId;
        $categoryIds = array_filter(
            array_map(
                'intval',
                $categoryIds
            )
        );

        $container = Factory::getContainer();
        $site = $container->get(SiteApplication::class);

        Factory::$application = $site;

        try {
            $site->loadIdentity();
            $site->loadLanguage($app->getLanguage());

            $levels = Access::getAuthorisedViewLevels(0);

            $menu = $menuId ? $site->getMenu()->getItem($menuId) : null;
            if ($menuId && (!$menu || !in_array((int)$menu->access, $levels, true))) {
                $io->error('The selected site menu item is unavailable to guests.');

                return 2;
            }

            $multilingual = Multilanguage::isEnabled();
            $db = $container->get(DatabaseInterface::class);
            $language = $multilingual ? $site->getLanguage()->getTag() : '';
            $item = $this->findArticle($db, $action, $categoryIds, (int)$featured, $type, $levels, $language);

            if (!$item) {
                $io->note('No matching SEBLOD article found.');

                return 0;
            }

            $slug = $item->id.($item->alias ? ':'.$item->alias : '');
            $route = RouteHelper::getArticleRoute($slug, (int)$item->catid, $multilingual ? $item->language : '');
            if ($menuId) {
                $route .= '&Itemid='.$menuId;
            }

            $path = Route::link('site', $route, false);
            $basePath = rtrim(Uri::root(true), '/');
            if ($basePath !== '' && str_starts_with($path, $basePath.'/')) {
                $path = substr($path, strlen($basePath));
            }

            $link = $liveSite.'/'.ltrim($path, '/');

        } finally {
            Factory::$application = $app;
        }

        if (Console::check([
            'status' => 1,
            'object_group' => 'com_content',
            'object_id' => $item->id,
            'order_url' => $link,
        ])) {
            $io->note('This article and URL have already been processed.');

            return 0;
        }

        if (!Console::send([
            'user' => 0,
            'id' => $item->id,
            'title' => $item->category,
            'desc' => $item->title,
            'domain' => $liveSite,
            'link' => $link,
        ])) {
            $io->error('The notification could not be recorded.');

            return 1;
        }

        $io->success('Notification processed.');

        return 0;
    }

    /**
     * @param DatabaseInterface $db
     * @param string $action
     * @param array $categoryIds
     * @param int $featured
     * @param string $type
     * @param array $levels
     * @param string $language
     *
     * @return object|null
     *
     * @since 2.0
     */
    private function findArticle(
        DatabaseInterface $db,
        string $action,
        array $categoryIds,
        int $featured,
        string $type,
        array $levels,
        string $language
    ): ?object {
        $cck = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__cck_core', 'cck'))
            ->where($db->quoteName('cck.pk').' = '.$db->quoteName('a.id'))
            ->where($db->quoteName('cck.storage_location').' = '.$db->quote('joomla_article'));

        $now = Factory::getDate()->toSql();
        $publishUp = $db->quoteName('a.publish_up');
        $publishDown = $db->quoteName('a.publish_down');
        $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.alias', 'a.title', 'a.catid', 'a.language']))
            ->select($db->quoteName('c.title', 'category'))
            ->from($db->quoteName('#__content', 'a'))
            ->join(
                'INNER',
                $db->quoteName('#__categories', 'c').' ON '.$db->quoteName('c.id').' = '.$db->quoteName('a.catid')
            )
            ->where([$db->quoteName('a.state').' = 1', $db->quoteName('c.published').' = 1'])
            ->whereIn($db->quoteName('a.access'), $levels)
            ->whereIn($db->quoteName('c.access'), $levels)
            ->where("($publishUp IS NULL OR $publishUp <= :publishUp)")
            ->where("($publishDown IS NULL OR $publishDown >= :publishDown)")
            ->where($db->quoteName('a.featured').' = :featured')
            ->bind([':publishUp', ':publishDown'], $now)
            ->bind(':featured', $featured, ParameterType::INTEGER);

        if ($type !== '') {
            $cck->where($db->quoteName('cck.cck').' = :cckType');
            $query->bind(':cckType', $type);
        }

        $query->where('EXISTS ('.$cck.')');

        if ($categoryIds) {
            $query->whereIn($db->quoteName('a.catid'), $categoryIds);
        }

        if ($language !== '') {
            $query->whereIn($db->quoteName('a.language'), [$language, '*'], ParameterType::STRING);
        }

        $query->order(
            $action === 'random' ? $query->rand() : $db->quoteName('a.created').' DESC, '.$db->quoteName('a.id').' DESC'
        );
        $db->setQuery($query, 0, 1);

        return $db->loadObject();
    }
}

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

namespace JU\Plugin\System\JUPWA\Extension;

use DateTime;
use DateTimeZone;
use Exception;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\AbstractEvent;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use JUPWA\Capacitor\Capacitor;
use JUPWA\Helpers\AssetLinks;
use JUPWA\Helpers\Facebook;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\Manifest;
use JUPWA\Helpers\META;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\PWAInstall;
use JUPWA\Helpers\ServiceWorker;
use JUPWA\Push\Push;
use JUPWA\Thumbs\Render;
use JUPWA\Utils\Util;

defined('_JEXEC') or die;

final class JUPWA extends CMSPlugin implements SubscriberInterface
{
    protected string $jupwa_js_version = '2.8.0';
    public int $caching = 0;

    /**
     *
     * @return string[]
     *
     * @since 2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onExtensionAfterSave' => 'onExtensionAfterSave',
            'onAfterRoute' => 'onAfterRoute',
            'onAfterDispatch' => 'onAfterDispatch',
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onAfterRender' => 'onAfterRender',
            'onContentPrepare' => 'onContentPrepare',
        ];
    }

    /**
     * @since 2.0
     */
    public function onAfterInitialise(): void
    {
        $this->loadLanguage();
    }

    /**
     * @param AbstractEvent $event
     *
     * @throws Exception
     * @since 2.0
     */
    public function onExtensionAfterSave(AbstractEvent $event): void
    {
        $app = $this->getApplication();
        if (!$app->isClient('administrator')
            || $event->getArgument('context') !== 'com_plugins.plugin'
        ) {
            return;
        }

        $extension = $event->getArgument('subject');
        if (!$extension instanceof Extension || $extension->type !== 'plugin'
            || $extension->folder !== 'system' || $extension->element !== 'jupwa'
        ) {
            return;
        }

        $params = (new Registry($extension->params))->toArray();

        Render::create($params, $app);

        if (!file_exists(JPATH_SITE.'/favicons/thumbs.json')) {
            $app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
        }

        if (($params['usepush'] ?? 0) == 1) {
            $pushData = [
                $params['firebaseServiceAccount'] ?? '',
                $params['apiKey'] ?? '',
                $params['projectId'] ?? '',
                $params['messagingSenderId'] ?? '',
                $params['appId'] ?? '',
                $params['vapidKey_wp'] ?? '',
            ];

            if (!Util::checkFields($pushData)) {
                $app->enqueueMessage(Text::_('PLG_JUPWA_REQUIRED_FILEDS_FOR_PUSH'), 'danger');
            }

            Push::checkAjaxPlugin();
        }

        Manifest::create([
            'param' => $params,
            'site' => $app->get('sitename'),
            'description' => $app->get('MetaDesc'),
        ]);

        AssetLinks::create(['param' => $params]);
        Manifest::addVersion();
        ServiceWorker::create(['param' => $params]);
    }

    /**
     * @return void
     *
     * @throws Exception
     * @since 2.0
     */
    public function onAfterRender(): void
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $currentUri = Uri::getInstance()->getPath();
        if (str_contains($currentUri, '/account')) {
            return;
        }

        if ($this->isExcluded() === true) {
            return;
        }

        if ($this->getApplication()->getDocument()->getType() !== 'html') {
            return;
        }

        Facebook::fix();

        $buffer = $this->getApplication()->getBody();
        if (empty($buffer)) {
            return;
        }

        if ($this->params->get('usepwainstall') == 1) {
            $buffer = str_replace('</body>', PWAInstall::panel($this->params).'</body>', $buffer);

            $this->checkBuffer($buffer);
        }

        if (Push::isPush(['params' => $this->params])) {
            $widget = Util::tmpl(
                'widget',
                [
                    'params' => $this->params,
                    'version' => $this->jupwa_js_version,
                ]
            );

            $buffer = str_replace('</body>', '<template id="jupwa-widget-tpl">'.$widget.'</template></body>', $buffer);

            $this->checkBuffer($buffer);
        }

        if ($this->params->get('og') == 1) {
            $buffer = preg_replace_callback('#<html(.*?)>#m', [HTML::class, 'tag_html'], $buffer);

            if (str_contains($buffer, '<!DOCTYPE html>')) {
                $buffer = str_replace(['xmlns="http://www.w3.org/1999/xhtml"', '  '], ['', ' '], $buffer);
            }

            $this->checkBuffer($buffer);
        }

        $metas = ['jgenerator' => 'generator', 'keywords' => 'keywords'];
        foreach ($metas as $param => $name) {
            if ($this->params->get($param) == 1) {
                $buffer = preg_replace('#<meta name="'.$name.'" content="(.*?)".*?>#m', '', $buffer);
            }
        }

        if ($this->params->get('jauthor') == 1) {
            $sitename = htmlspecialchars($this->getApplication()->get('sitename'), ENT_QUOTES, 'UTF-8');
            $buffer = preg_replace(
                '#<meta name="author" content="(.*?)".*?>#m',
                '<meta name="author" content="'.$sitename.'">',
                $buffer
            );
        }

        $buffer = str_replace(['_og:video', "\t\n"], ['og:video', ''], $buffer);
        $buffer = preg_replace(['#:tag_.*?_#m', '#fb:admins_(.*?)"#is'], [':tag', 'fb:admins"'], $buffer);
        $buffer = preg_replace('#<link href=".*?" rel=".*?" type="image/vnd.microsoft.icon".*?>#m', '', $buffer);

        if (!$this->params->get('source_icon_svg')) {
            $buffer = preg_replace('#<link href=".*?" rel="icon" type="image/svg\+xml".*?>#m', '', $buffer);
        }

        if ($this->params->get('htmlcompress') == 1) {
            $buffer = HTML::compress($buffer);
        }

        $this->checkBuffer($buffer);
        $this->getApplication()->setBody($buffer);
        $this->handleCaching();
    }

    /**
     * @throws Exception
     * @since 2.0
     */
    private function handleCaching(): void
    {
        if ($this->params->get('joomla_cache', 0) != 1) {
            return;
        }

        $exclusion = (string)$this->params->get('cache_exclusion', '');
        if ($exclusion !== '') {
            $urls = explode("\r\n", $exclusion);
            $current = Uri::current();

            foreach ($urls as $url) {
                if ($url && str_contains($current, $url)) {
                    return;
                }
            }
        }

        $app = $this->getApplication();
        if (!$app->getIdentity()?->guest) {
            $app->allowCache(false);
            $app->setHeader('Cache-Control', 'private, no-store', true);

            return;
        }

        $app->allowCache(true);
        $expirestime = (int)$this->params->get('expirestime', 3600);

        if ($this->params->get('cachecontrol', 0) == 1) {
            $app->setHeader('Cache-Control', 'public, max-age='.$expirestime, true);
        }

        if ($this->params->get('expires', 0) == 1) {
            $date = new DateTime(
                'now',
                new DateTimeZone('GMT')
            );
            $date->setTimestamp(time() + $expirestime);

            $app->setHeader(
                'Expires',
                $date->format('D, d M Y H:i:s \G\M\T'),
                true
            );
        }
    }

    /**
     *
     * @return void
     *
     * @throws Exception
     * @since 2.0
     */
    public function onAfterRoute(): void
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        if (str_contains(Uri::current(), '/account')) {
            return;
        }

        if ($this->isExcluded() === true) {
            return;
        }

        if ($this->params->get('joomla_cache', 0) == 1) {
            if (!$this->getApplication()->getIdentity()->guest) {
                $this->getApplication()->getConfig()->set('caching', 0);
            }

            if ($this->checkRules()) {
                $this->caching = (int)$this->getApplication()->getConfig()->get('caching');
                $this->getApplication()->getConfig()->set('caching', 0);
            }
        }
    }

    /**
     *
     * @return void
     *
     * @throws Exception
     * @since 2.0
     */
    public function onAfterDispatch(): void
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        if (str_contains(Uri::current(), '/account')) {
            return;
        }

        if ($this->isExcluded() === true) {
            return;
        }

        $doc = $this->getApplication()->getDocument();
        if (!($doc instanceof HtmlDocument)) {
            return;
        }

        $wa = $doc->getWebAssetManager();
        $v = $this->jupwa_js_version;

        if (Capacitor::isCapacitor(['params' => $this->params])) {
            $wa->registerAndUseScript(
                'app',
                Uri::root().'media/jupwa/js/app.'.$v.'.js',
                ['version' => false],
                [
                    'defer' => 'defer',
                    'fetchpriority' => 'auto',
                ]
            );

            $doc->addHeadLink(
                Uri::root().'media/jupwa/js/app.'.$v.'.js',
                'preload',
                'rel',
                [
                    'as' => 'script',
                ]
            );
        }

        if (Push::isPush(['params' => $this->params])) {
            $wa->registerAndUseStyle(
                'push',
                Uri::root().'media/jupwa/css/app.push.'.$v.'.css',
                ['version' => false]
            );

            $doc->addHeadLink(
                Uri::root().'media/jupwa/css/app.push.'.$v.'.css',
                'preload',
                'rel',
                ['as' => 'style']
            );

            $wa->registerAndUseScript(
                'push',
                Uri::root().'media/jupwa/js/push.'.$v.'.js',
                ['version' => false],
                [
                    'defer' => 'defer',
                    'fetchpriority' => 'auto',
                ]
            );

            $doc->addHeadLink(
                Uri::root().'media/jupwa/js/push.'.$v.'.js',
                'preload',
                'rel',
                [
                    'as' => 'script',
                ]
            );
        }

        if ($this->params->get('usepwainstall') == 1) {
            $wa->registerAndUseScript(
                'jupwa',
                Uri::root().'media/jupwa/js/jupwa.'.$v.'.js',
                ['version' => false],
                [
                    'defer' => 'defer',
                    'fetchpriority' => 'auto',
                ]
            );

            $doc->addHeadLink(
                Uri::root().'media/jupwa/js/jupwa.'.$v.'.js',
                'preload',
                'rel',
                [
                    'as' => 'script',
                ]
            );
        }
    }

    /**
     *
     * @return void
     *
     * @throws Exception
     * @since 2.0
     */
    public function onBeforeCompileHead(): void
    {
        if (!$this->getApplication()->isClient('site')
            || $this->getApplication()->getDocument()->getType() !== 'html'
        ) {
            return;
        }

        if (str_contains(Uri::current(), '/account')) {
            return;
        }

        if ($this->isExcluded() === true) {
            return;
        }

        $view = $this->getApplication()->getInput()->get('view');
        $component = $this->getApplication()->getInput()->getCmd('option');
        if ($component === 'com_finder') {
            return;
        }

        PluginHelper::importPlugin('jupwa');
        $event = $this->getApplication()->getDispatcher()->dispatch(
            'onJUPWAAccess',
            new Event(
                'onJUPWAAccess',
                ['context' => $component]
            )
        );
        $use_access = $event->getArgument(
            'result',
            []
        );

        if ($view !== 'article') {
            $access = in_array(true, $use_access, true);

            if ($access === false) {
                $tags = $this->coreTags();
                OG::tag([
                    'params' => $this->params,
                    'type' => 'website',
                    'title' => $tags->title,
                    'image' => $tags->img->image ?? '',
                    'image_width' => $tags->img->width ?? '',
                    'image_height' => $tags->img->height ?? '',
                    'description' => $tags->description,
                ]);
            }
        }

        $this->getApplication()->getDispatcher()->dispatch(
            'onJUPWASchema',
            new Event(
                'onJUPWASchema',
                ['params' => $this->params]
            )
        );

        if ($this->params->get('og') == 1) {
            $this->getApplication()->getDispatcher()->dispatch(
                'onJUPWAOG',
                new Event(
                    'onJUPWAOG',
                    ['params' => $this->params]
                )
            );
        }

        Capacitor::render(['params' => $this->params]);
        Push::render(['params' => $this->params]);
        META::render(['params' => $this->params]);
    }

    /**
     * @param null $plugin_image
     *
     * @return object
     * @throws Exception
     * @since 2.0
     */
    private function coreTags($plugin_image = null): object
    {
        $doc = $this->getApplication()->getDocument();
        $lang = $this->getApplication()->getLanguage();
        $menu = $this->getApplication()->getMenu();

        $imagePath = $plugin_image ?: Images::display_default(
            $this->params->get('selectimg', 0),
            $this->params->get('image', ''),
            $this->params->get('imagemain', '')
        );

        $title = HTML::text($doc->getTitle());

        $activeMenu = $menu->getActive();
        if ($activeMenu && $activeMenu !== $menu->getDefault($lang->getTag())) {
            $title = $activeMenu->title;
        }

        $description = HTML::html($doc->getMetaData('description'));

        $imgData = (object)[
            'image' => '',
            'width' => '',
            'height' => '',
        ];

        if (!empty($imagePath)) {
            try {
                $resolvedImg = Images::display($imagePath);

                if ($resolvedImg) {
                    $imgData = $resolvedImg;
                }

            } catch (Exception $e) {
            }
        }

        return (object)[
            'title' => $title,
            'description' => $description,
            'image' => $imagePath,
            'img' => $imgData,
        ];
    }

    /**
     * @param AbstractEvent $event
     *
     *
     * @throws Exception
     * @since 2.0
     */
    public function onContentPrepare(AbstractEvent $event): void
    {
        if ($event instanceof ContentPrepareEvent) {
            $this->prepareContent($event->getContext(), $event->getItem());

            return;
        }

        // Third-party extensions can still dispatch a generic Joomla event.
        $arguments = $event->getArguments();
        $context = $arguments['context'] ?? $arguments[0] ?? null;
        $article = $arguments['subject'] ?? $arguments['article'] ?? $arguments[1] ?? null;

        if ($context === null || $article === null) {
            return;
        }

        $result = $this->prepareContent($context, $article);
        if (!$event instanceof AbstractImmutableEvent) {
            $results = $event->getArgument('result', []);
            $results[] = $result;
            $event->setArgument('result', $results);
        }
    }

    /**
     * @throws Exception
     * @since 2.0
     */
    private function prepareContent(
        $context,
        $article
    ): bool {
        if (!$this->getApplication()->isClient('site')) {
            return false;
        }

        if (str_contains(Uri::current(), '/account')) {
            return false;
        }

        if ($this->isExcluded() === true) {
            return false;
        }

        if ($this->getApplication()->getDocument()->getType() !== 'html') {
            return false;
        }

        if ($context === 'com_finder.indexer') {
            return true;
        }

        PluginHelper::importPlugin('jupwa');

        $event = $this->getApplication()->getDispatcher()->dispatch(
            'onJUPWAAccess',
            new Event(
                'onJUPWAAccess',
                ['context' => $context]
            )
        );

        $use_access = $event->getArgument(
            'result',
            []
        );

        $access = in_array(true, $use_access, true);

        if ($access) {
            $this->getApplication()->getDispatcher()->dispatch(
                'onJUPWAArticleSchema',
                new Event('onJUPWAArticleSchema', [
                    'article' => $article,
                    'params' => $this->params,
                    'context' => $context,
                ])
            );

            $this->getApplication()->getDispatcher()->dispatch(
                'onJUPWAArticleOG',
                new Event('onJUPWAArticleOG', [
                    'article' => $article,
                    'params' => $this->params,
                    'context' => $context,
                ])
            );
        } else {
            $tags = $this->coreTags();

            OG::tag([
                'params' => $this->params,
                'type' => 'website',
                'title' => $tags->title ?? '',
                'image' => $tags->img->image ?? '',
                'image_width' => $tags->img->width ?? '',
                'image_height' => $tags->img->height ?? '',
                'description' => $tags->description ?? '',
            ]);
        }

        return true;
    }

    /**
     * @param string|null $buffer
     *
     * @return  void
     * @throws Exception
     * @since 2.0
     */
    private function checkBuffer(?string $buffer): void
    {
        if ($buffer === null) {
            $error = preg_last_error();
            $this->getApplication()->getLogger()->error('JUPWA Regex Error: '.$error);
        }
    }

    /**
     *
     * @return bool
     *
     * @since 2.0
     */
    private function checkRules(): bool
    {
        $defs = str_replace("\r", "", (string)$this->params->get('definitions', ''));
        $defs = array_filter(explode("\n", $defs));

        foreach ($defs as $def) {
            $result = $this->parseQueryString($def);

            if (!empty($result)) {
                $found = 0;
                foreach ($result as $key => $value) {
                    $inputValue = $this->getApplication()->getInput()->get($key);

                    if ($inputValue == $value || ($inputValue !== null && $value === '?')) {
                        $found++;
                    }
                }

                if ($found === count($result)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     *
     * @param string $str
     *
     * @return array
     * @since 2.0
     */
    private function parseQueryString(string $str): array
    {
        $op = [];
        $pairs = explode("&", $str);
        foreach ($pairs as $pair) {
            $parts = explode("=", $pair);

            if (count($parts) === 2) {
                $op[urldecode($parts[0])] = urldecode($parts[1]);
            }
        }

        return $op;
    }

    /**
     *
     * @return bool
     *
     * @since 2.0
     */
    private function isExcluded(): bool
    {
        $exclusions = $this->params->get('push_exclude', '');

        if ($exclusions) {
            $exclusions = str_replace(["\r\n", "\r"], "\n", $exclusions);
            $exclusions = explode("\n", $exclusions);
            $exclusions = array_map('trim', $exclusions);

            $filterExpression = static function ($x) {
                return $x !== '';
            };

            $exclusions = array_filter(
                $exclusions,
                $filterExpression
            );

            if ($exclusions) {
                foreach ($exclusions as $exclusion) {
                    $pos = strpos(
                        Uri::getInstance()->getPath(),
                        $exclusion
                    );

                    if ($pos !== false) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}

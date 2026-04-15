<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
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

require_once __DIR__.'/libraries/vendor/autoload.php';

#[AllowDynamicProperties]
class plgSystemJUPWA extends CMSPlugin
{
    protected $app;
    protected string $jupwa_js_version = '2.4.5';
    protected $option;
    protected $view;
    protected $layout;
    protected $plg;
    public int $caching = 0;

    /**
     * plgSystemJUPWA constructor.
     *
     * @param $subject
     * @param $config
     *
     * @throws Exception
     * @since 1.0
     */
    public function __construct(
        &$subject,
        $config
    ) {
        parent::__construct(
            $subject,
            $config
        );

        $this->app = Factory::getApplication();

        if ($this->app->isClient('administrator')) {
            $this->handleAdminTasks();
        }
    }

    /**
     * @throws Exception
     * @since 1.0
     */
    private function handleAdminTasks(): void
    {
        $this->option = $this->app->input->get('option');
        $this->layout = $this->app->input->get('layout');
        $extension_id = $this->app->input->get('extension_id');

        if ($this->option === 'com_plugins' && $this->layout === 'edit') {
            $post = $this->app->input->post->getArray();
            $this->plg = PluginHelper::getPlugin('system', 'jupwa');

            if (isset($post['jform']['params']) && (int)$extension_id === (int)$this->plg->id) {
                $post_param = $post['jform']['params'];
                $task = $this->app->input->get('task');

                if ($task === 'plugin.apply' || $task === 'plugin.save') {
                    Render::create($post_param, $this->app);

                    if (!file_exists(JPATH_SITE.'/favicons/thumbs.json')) {
                        $this->app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
                    }

                    if (!empty($post_param['usepush']) && $post_param['usepush'] == 1) {
                        $pushData = [
                            $post_param['firebaseServiceAccount'] ?? '',
                            $post_param['apiKey'] ?? '',
                            $post_param['projectId'] ?? '',
                            $post_param['messagingSenderId'] ?? '',
                            $post_param['appId'] ?? '',
                            $post_param['vapidKey_wp'] ?? '',
                        ];

                        if (!Util::checkFields($pushData)) {
                            $this->app->enqueueMessage(Text::_('PLG_JUPWA_REQUIRED_FILEDS_FOR_PUSH'), 'danger');
                        }

                        Push::checkAjaxPlugin();
                    }
                }

                Manifest::create([
                    'param' => $post_param,
                    'site' => $this->app->get('sitename'),
                    'description' => $this->app->get('MetaDesc'),
                ]);

                AssetLinks::create(['param' => $post_param]);
                Manifest::addVersion();
                ServiceWorker::create(['param' => $post_param]);
            }
        }
    }

    /**
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public function onAfterRender(): void
    {
        if (!$this->app->isClient('site')) {
            return;
        }

        $currentUri = Uri::getInstance()->getPath();
        if (strpos($currentUri, '/account') !== false) {
            return;
        }

        if ($this->app->getDocument()->getType() !== 'html') {
            return;
        }

        Facebook::fix();

        $buffer = $this->app->getBody();
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

            if (strpos($buffer, '<!DOCTYPE html>') !== false) {
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
            $sitename = htmlspecialchars($this->app->get('sitename'), ENT_QUOTES, 'UTF-8');
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
        $this->app->setBody($buffer);
        $this->handleCaching();
    }

    /**
     * @throws Exception
     * @since 1.0
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
                if ($url && strpos($current, $url) !== false) {
                    return;
                }
            }
        }

        $this->app->allowCache(true);
        $expirestime = (int)$this->params->get('expirestime', 3600);

        if ($this->params->get('cachecontrol', 0) == 1) {
            $this->app->setHeader('Cache-Control', 'public, max-age='.$expirestime, true);
        }

        if ($this->params->get('expires', 0) == 1) {
            $date = new DateTime('now', new DateTimeZone('GMT'));
            $date->setTimestamp(time() + $expirestime);

            $this->app->setHeader(
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
     * @since 1.0
     */
    public function onAfterRoute(): void
    {
        if (!$this->app->isClient('site')) {
            return;
        }

        if (strpos(Uri::current(), '/account') !== false) {
            return;
        }

        if ($this->params->get('joomla_cache', 0) == 1) {
            if (!$this->app->getIdentity()->guest) {
                $this->app->getConfig()->set('caching', 0);
            }

            if ($this->checkRules()) {
                $this->caching = (int)$this->app->getConfig()->get('caching');
                $this->app->getConfig()->set('caching', 0);
            }
        }
    }

    /**
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public function onAfterDispatch(): void
    {
        if (!$this->app->isClient('site')) {
            return;
        }

        if (strpos(Uri::current(), '/account') !== false) {
            return;
        }

        $doc = $this->app->getDocument();
        if (!($doc instanceof HtmlDocument)) {
            return;
        }

        $wa = $doc->getWebAssetManager();
        $v = $this->jupwa_js_version;

        if (Push::isPush(['params' => $this->params])) {
            $wa->registerAndUseStyle(
                'push',
                Uri::root().'media/jupwa/css/app.push.'.$v.'.css',
                ['version' => false]
            );

            $doc->addHeadLink(
                Uri::root().'media/jupwa/css/app.push.'.$v.'.css',
                'preload prefetch',
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
                'preload prefetch',
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
                'preload prefetch',
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
     * @since 1.0
     */
    public function onBeforeCompileHead(): void
    {
        if (!$this->app->isClient('site') || $this->app->getDocument()->getType() !== 'html') {
            return;
        }

        if (strpos(Uri::current(), '/account') !== false) {
            return;
        }

        $view = $this->app->input->get('view');
        $component = $this->app->input->getCmd('option');
        if ($component === 'com_finder') {
            return;
        }

        PluginHelper::importPlugin('jupwa');
        $use_access = $this->app->triggerEvent(
            'onJUPWAAccess',
            [$component]
        );

        if ($view !== 'article') {
            $access = true;
            foreach ($use_access as $ua) {
                if ($ua === false) {
                    $access = false;
                }
            }

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

                OG::twitter([
                    'params' => $this->params,
                    'title' => $tags->title,
                    'image' => $tags->image,
                    'description' => $tags->description,
                ]);
            }
        }

        // Integration
        $this->app->triggerEvent(
            'onJUPWASchema',
            [$this->params]
        );

        if ($this->params->get('tw') == 1) {
            $this->app->triggerEvent(
                'onJUPWATwitter',
                [$this->params]
            );
        }

        if ($this->params->get('og') == 1) {
            $this->app->triggerEvent(
                'onJUPWAOG',
                [$this->params]
            );
        }

        Push::render(['params' => $this->params]);
        META::render(['params' => $this->params]);
    }

    /**
     * @param null $plugin_image
     *
     * @return object
     * @throws Exception
     * @since 1.0
     */
    private function coreTags($plugin_image = null): object
    {
        $doc = $this->app->getDocument();
        $lang = $this->app->getLanguage();
        $menu = $this->app->getMenu();

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
     * @param        $context
     * @param        $article
     *
     * @return true|void
     *
     * @throws Exception
     * @since 1.0
     */
    public function onContentPrepare(
        $context,
        &$article
    ): ?bool {
        if (!$this->app->isClient('site')) {
            return false;
        }

        if (strpos(Uri::current(), '/account') !== false) {
            return false;
        }

        if ($this->app->getDocument()->getType() !== 'html') {
            return false;
        }

        $integration = PluginHelper::importPlugin('jupwa');
        $use_access = $this->app->triggerEvent('onJUPWAAccess', [$context]);

        if ($context === 'com_finder.indexer' || ($integration && !in_array($context, $use_access))) {
            return true;
        }

        $this->app->triggerEvent('onJUPWAArticleSchema', [
            $article,
            $this->params,
            $context,
        ]);

        $this->app->triggerEvent('onJUPWAArticleTwitter', [
            $article,
            $this->params,
            $context,
        ]);

        $this->app->triggerEvent('onJUPWAArticleOG', [
            $article,
            $this->params,
            $context,
        ]);

        if ($integration === null) {
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

            OG::twitter([
                'params' => $this->params,
                'title' => $tags->title ?? '',
                'image' => $tags->image ?? '',
                'description' => $tags->description ?? '',
            ]);
        }

        return true;
    }

    /**
     * Check the buffer.
     *
     * @param string|null $buffer Buffer to be checked.
     *
     * @return  void
     * @throws Exception
     * @since 1.0
     */
    private function checkBuffer(?string $buffer): void
    {
        if ($buffer === null) {
            $error = preg_last_error();
            $this->app->getLogger()->error('JUPWA Regex Error: '.$error);
        }
    }

    /**
     *
     * @return bool
     *
     * @since 1.0
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
                    $inputValue = $this->app->input->get($key);

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
     * @since 1.0
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
}
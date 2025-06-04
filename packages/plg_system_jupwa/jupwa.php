<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use JUPWA\Helpers\Assetinks;
use JUPWA\Helpers\BrowserConfig;
use JUPWA\Helpers\Facebook;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\Manifest;
use JUPWA\Helpers\META;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\PWAInstall;
use JUPWA\Helpers\Schema;
use JUPWA\Helpers\ServiceWorker;
use JUPWA\Thumbs\Render;

require_once __DIR__ . '/libraries/vendor/autoload.php';

#[AllowDynamicProperties]
class plgSystemJUPWA extends CMSPlugin
{
	public int $caching = 0;

	/**
	 * plgSystemJUPWA constructor.
	 *
	 * @param $subject
	 * @param $config
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->loadLanguage();

		$this->app = Factory::getApplication();

		if($this->app->isClient('site'))
		{
			return;
		}

		$this->plg    = PluginHelper::getPlugin('system', 'jupwa');
		$this->view   = $this->app->input->get('view');
		$this->layout = $this->app->input->get('layout');
		$this->option = $this->app->input->get('option');
		$extension_id = $this->app->input->get('extension_id');
		$post         = $this->app->input->post->getArray();

		if($this->option === 'com_plugins' && $this->layout === 'edit' && isset($post[ 'jform' ][ 'params' ]) && $extension_id == $this->plg->id)
		{
			$post_param = $post[ 'jform' ][ 'params' ];

			BrowserConfig::create($post_param);

			Manifest::create([
				'param'       => $post_param,
				'site'        => $this->app->get('sitename'),
				'description' => $this->app->get('MetaDesc'),
			]);

			Assetinks::create([ 'param' => $post_param ]);

			Manifest::addVersion();
			ServiceWorker::create([ 'param' => $post_param ]);

			if($post_param[ 'thumbs' ] == 1 && ($post[ 'task' ] === 'plugin.apply' || $post[ 'task' ] === 'plugin.save'))
			{
				Render::create($post_param, $this->app);
			}
			elseif(!file_exists(JPATH_SITE . '/favicons/thumbs.json'))
			{
				$this->app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
			}
		}
	}

	/**
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onAfterRender(): void
	{
		if(!$this->app->isClient('site'))
		{
			return;
		}

		if($this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		/*
		 * Facebook share fix gzip
		 */
		Facebook::fix();

		$buffer = $this->app->getBody();

		/*
		 * PWA Install
		 */
		if($this->params->get('usepwainstall') == 1)
		{
			$buffer = str_replace('</body>', PWAInstall::panel($this->params) . '</body>', $buffer);

			$this->checkBuffer($buffer);
		}

		/*
		 * Replace <html> for support OG-tags
		 */
		if($this->params->get('og') == 1)
		{
			$regex  = '#<html(.*?)>#m';
			$buffer = preg_replace_callback($regex, [
				HTML::class,
				'tag_html'
			], $buffer);
			$this->checkBuffer($buffer);

			if(preg_match('#<!DOCTYPE html>#', $buffer))
			{
				$buffer = str_replace([
					'xmlns="http://www.w3.org/1999/xhtml"',
					'  '
				], [ '', ' ' ], $buffer);
				$this->checkBuffer($buffer);
			}
		}

		/*
		 * Remove generator
		 */
		if($this->params->get('jgenerator') == 1)
		{
			$regex  = '#<meta name="generator" content="(.*?)".*?>#m';
			$buffer = preg_replace($regex, '', $buffer);

			$this->checkBuffer($buffer);
		}

		/*
		 * Remove keywords
		 */
		if($this->params->get('keywords') == 1)
		{
			$regex  = '#<meta name="keywords" content="(.*?)".*?>#m';
			$buffer = preg_replace($regex, '', $buffer);

			$this->checkBuffer($buffer);
		}

		/*
		 * Remove Joomla author
		 */
		if($this->params->get('jauthor') == 1)
		{
			$regex  = '#<meta name="author" content="(.*?)".*?>#m';
			$buffer = preg_replace($regex, '<meta name="author" content="' . $this->app->get('sitename') . '">', $buffer);

			$this->checkBuffer($buffer);
		}

		/*
		 * Other fixes
		 */
		$buffer = str_replace('_og:video', 'og:video', $buffer);
		$this->checkBuffer($buffer);

		$regex  = '#:tag_.*?_#m';
		$buffer = preg_replace($regex, ':tag', $buffer);
		$this->checkBuffer($buffer);

		$regex  = '#fb:admins_(.*?)"#is';
		$buffer = preg_replace($regex, 'fb:admins"', $buffer);
		$this->checkBuffer($buffer);

		/*
		 * Fix favicon
		 */
		$regex  = '#<link href=".*?" rel=".*?" type="image/vnd.microsoft.icon".*?>#m';
		$buffer = preg_replace($regex, '', $buffer);
		$this->checkBuffer($buffer);

		$regex  = '#<link href=".*?" rel="icon" type="image/svg\+xml".*?>#m';
		$buffer = preg_replace($regex, '', $buffer);
		$this->checkBuffer($buffer);

		$buffer = str_replace("	\n", '', $buffer);
		$this->checkBuffer($buffer);

		/*
		 * Compress html
		 */
		if($this->params->get('htmlcompress') == 1)
		{
			$buffer = HTML::compress($buffer);
			$this->checkBuffer($buffer);
		}

		$this->app->setBody($buffer);

		/*
		 * Add cache support
		 */
		$exclusion = $this->params->get('cache_exclusion', '');
		if($exclusion !== '')
		{
			$urls = explode("\r\n", $exclusion);
			foreach($urls as $url)
			{
				if(strpos(Uri::current(), $url) !== false)
				{
					return;
				}
			}
		}

		if($this->params->get('joomla_cache', 0) == 1)
		{
			$this->app->allowCache(true);
			if($this->params->get('pragma', 0) == 1)
			{
				$this->app->setHeader('Pragma', 'public', true);
			}

			if($this->params->get('cachecontrol', 0) == 1)
			{
				$this->app->setHeader('Cache-Control', 'public, max-age=' . $this->params->get('expirestime'), true);
			}

			if($this->params->get('expires', 0) == 1)
			{
				$date = new DateTime();
				$date->setTimezone(new DateTimeZone('GMT'));
				$expireheader = $date->setTimestamp(time() + $this->params->get('expirestime'))->format('D, d M Y H:i:s T');
				$this->app->setHeader('Expires', $expireheader, true);
			}
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onAfterRoute(): void
	{
		if(!$this->app->isClient('site'))
		{
			return;
		}

		if($this->params->get('joomla_cache', 0) == 1)
		{
			if(strpos(JURI::current(), '/account') !== false)
			{
				$this->app->getConfig()->set('caching', 0);
			}

			if(!$this->app->getIdentity()->guest)
			{
				$this->app->getConfig()->set('caching', 0);
			}

			if($this->checkRules())
			{
				$this->caching = $this->app->getConfig()->get('caching');

				$this->app->getConfig()->set('caching', 0);
			}
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onAfterDispatch(): void
	{
		if(!$this->app->isClient('site'))
		{
			return;
		}

		$doc = $this->app->getDocument();
		if(!($doc instanceof HtmlDocument))
		{
			return;
		}

		if($this->params->get('usepwainstall') == 1)
		{
			$wa                    = $doc->getWebAssetManager();
			$jupwa_install_version = '2.0';

			$wa->registerAndUseScript('jupwa', Uri::root() . 'media/jupwa/js/jupwa.' . $jupwa_install_version . '.js', [ 'version' => false ], [
				'defer'         => 'defer',
				'fetchpriority' => 'auto'
			]);

			$doc->addHeadLink(Uri::root() . 'media/jupwa/js/jupwa.' . $jupwa_install_version . '.js', 'preload prefetch', 'rel', [
				'as' => 'script'
			]);
		}
	}

	/**
	 *
	 * @return true|void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onBeforeCompileHead()
	{
		if(!$this->app->isClient('site'))
		{
			return;
		}

		if($this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		$view       = $this->app->input->get('view');
		$component  = $this->app->input->getCmd('option');
		$use_access = $this->app->triggerEvent('onJUPWAAccess', [ $component ]);

		if($component === 'com_finder')
		{
			return true;
		}

		if($view !== 'article')
		{
			$access = true;
			foreach($use_access as $ua)
			{
				$access = $ua;
			}

			if($access === false)
			{
				$image       = $this->coreTags()->image;
				$title       = $this->coreTags()->title;
				$description = $this->coreTags()->description;
				$img         = $this->coreTags($image)->img;

				if($this->params->get('og') == 1)
				{
					OG::tag([
						'params'       => $this->params,
						'type'         => 'website',
						'title'        => $title,
						'image'        => $img->image,
						'image_width'  => $img->width,
						'image_height' => $img->height,
						'description'  => $description
					]);
				}

				if($this->params->get('tw') == 1)
				{
					OG::twitter([
						'params'      => $this->params,
						'title'       => $title,
						'image'       => $image,
						'description' => $description
					]);
				}
			}
		}

		// Integration
		$this->app->triggerEvent('onJUPWASchema', [ $this->params ]);

		if($this->params->get('tw') == 1)
		{
			$this->app->triggerEvent('onJUPWATwitter', [ $this->params ]);
		}

		if($this->params->get('og') == 1)
		{
			$this->app->triggerEvent('onJUPWAOG', [ $this->params ]);
		}

		META::render([ 'params' => $this->params ]);
		META::facebook([ 'params' => $this->params ]);
		Schema::global([ 'params' => $this->params ]);
	}

	/**
	 * @param        $context
	 * @param        $article
	 *
	 * @return true|void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onContentPrepare($context, &$article)
	{
		if(!$this->app->isClient('site'))
		{
			return;
		}

		if($this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		$integration = PluginHelper::importPlugin('jupwa');
		$use_access  = $this->app->triggerEvent('onJUPWAAccess', [ $context ]);

		if($context === 'com_finder.indexer' || ($integration && !in_array($context, $use_access)))
		{
			return true;
		}

		// Integration
		$this->app->triggerEvent('onJUPWAArticleSchema', [
			$article,
			$this->params,
			$context
		]);
		$this->app->triggerEvent('onJUPWAArticleTwitter', [
			$article,
			$this->params,
			$context
		]);
		$this->app->triggerEvent('onJUPWAArticleOG', [
			$article,
			$this->params,
			$context
		]);

		if($integration === null)
		{
			$image       = $this->coreTags()->image;
			$title       = $this->coreTags()->title;
			$description = $this->coreTags()->description;
			$img         = $this->coreTags($image)->img;

			OG::tag([
				'params'       => $this->params,
				'type'         => 'website',
				'title'        => $title,
				'image'        => $img->image,
				'image_width'  => $img->width,
				'image_height' => $img->height,
				'description'  => $description
			]);

			OG::twitter([
				'params'      => $this->params,
				'title'       => $title,
				'image'       => $image,
				'description' => $description
			]);
		}

		return true;
	}

	/**
	 * @param null $plugin_image
	 *
	 * @return object
	 * @throws \Exception
	 * @since 1.0
	 */
	private function coreTags($plugin_image = null): object
	{
		$doc  = $this->app->getDocument();
		$lang = $this->app->getLanguage();

		$image = Images::display_default($this->params->get('selectimg', 0), $this->params->get('image', ''), $this->params->get('imagemain', ''));

		$title = HTML::text($doc->getTitle());
		if($this->app->getMenu()->getActive() !== $this->app->getMenu()->getDefault($lang->getTag()))
		{
			$title = $this->app->getMenu()->getActive()->title;
		}

		$description = HTML::html($doc->getMetaData('description'));

		if($plugin_image)
		{
			$image = $plugin_image;
		}

		$img = Images::display($image);

		return (object) [
			'title'       => $title,
			'description' => $description,
			'image'       => $image,
			'img'         => $img
		];
	}

	/**
	 * Check the buffer.
	 *
	 * @param string $buffer Buffer to be checked.
	 *
	 * @return  void
	 * @since 1.0
	 */
	private function checkBuffer(string $buffer): void
	{
		if($buffer === null)
		{
			switch(preg_last_error())
			{
				case PREG_BACKTRACK_LIMIT_ERROR:
					$message = 'PHP regular expression limit reached (pcre.backtrack_limit)';
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$message = 'PHP regular expression limit reached (pcre.recursion_limit)';
					break;
				case PREG_BAD_UTF8_ERROR:
					$message = 'Bad UTF8 passed to PCRE function';
					break;
				default:
					$message = 'Unknown PCRE error calling PCRE function';
			}

			throw new RuntimeException($message);
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
		$defs = str_replace("\r", "", $this->params->get('definitions', ''));
		$defs = explode("\n", $defs);

		foreach($defs as $def)
		{
			$result = $this->parseQueryString($def);
			if(is_array($result))
			{
				$found    = 0;
				$required = count($result);
				foreach($result as $key => $value)
				{
					if($this->app->getInput()->get($key) == $value || ($this->app->getInput()->get($key, null) !== null && $value === '?'))
					{
						$found++;
					}
				}
				if($found == $required)
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 *
	 * @param
	 *
	 * @return array
	 * @since 1.0
	 */
	private function parseQueryString($str): array
	{
		$op    = [];
		$pairs = explode("&", $str);
		foreach($pairs as $pair)
		{
			[ $k, $v ] = array_map("urldecode", explode("=", $pair));
			$op[ $k ] = $v;
		}

		return $op;
	}
}
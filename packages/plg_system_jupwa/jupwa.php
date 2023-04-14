<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use JUPWA\Helpers\BrowserConfig;
use JUPWA\Helpers\Facebook;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\Manifest;
use JUPWA\Helpers\META;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\Schema;
use JUPWA\Helpers\ServiceWorker;
use JUPWA\Thumbs\Render;

require_once __DIR__ . '/libraries/vendor/autoload.php';

/**
 * JUPWA System Plugin.
 *
 * @since  1.0
 */
class plgSystemJUPWA extends CMSPlugin
{
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

		$app = Factory::getApplication();

		if($app->getName() === 'site')
		{
			return;
		}

		$this->plg    = PluginHelper::getPlugin('system', 'jupwa');
		$this->view   = $app->input->get('view');
		$this->layout = $app->input->get('layout');
		$this->option = $app->input->get('option');
		$extension_id = $app->input->get('extension_id');
		$post         = $app->input->post->getArray();

		if($this->option === 'com_plugins' && $this->layout === 'edit' && isset($post[ 'jform' ][ 'params' ]) && $extension_id == $this->plg->id)
		{
			$post_param = $post[ 'jform' ][ 'params' ];

			if($post_param[ 'thumbs' ] == 1)
			{
				Render::create($post_param);
			}

			BrowserConfig::create($post_param);

			Manifest::create([
				'param' => $post_param,
				'site'  => $app->get('sitename')
			]);

			Manifest::addVersion();
			ServiceWorker::create([ 'param' => $post_param ]);

			if(!File::exists(JPATH_SITE . '/favicons/thumbs.json'))
			{
				$app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
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
		$app = Factory::getApplication();

		if($app->getName() !== 'site' || ($app->input->getCmd('format') !== 'html' && $app->input->getCmd('format')) || $app->input->getCmd('tmpl'))
		{
			return;
		}

		/*
		 * Facebook share fix gzip
		 */
		Facebook::fix();

		$buffer = $app->getBody();

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
			$buffer = preg_replace($regex, '<meta name="author" content="' . $app->get('sitename') . '">', $buffer);

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

		$regex  = '#<link href=".*?" rel="mask-icon" color=".*?".*?>#m';
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

		$app->setBody($buffer);

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

		if($this->params->get('brawser_cache', 0) == 1)
		{
			$app->allowCache(true);
			if($this->params->get('pragma', 0) == 1)
			{
				$app->setHeader('Pragma', 'public', true);
			}

			if($this->params->get('cachecontrol', 0) == 1)
			{
				$app->setHeader('Cache-Control', 'public, max-age=' . $this->params->get('expirestime'), true);
			}

			if($this->params->get('expires', 0) == 1)
			{
				$date = new DateTime();
				$date->setTimezone(new DateTimeZone('GMT'));
				$expireheader = $date->setTimestamp(time() + $this->params->get('expirestime'))->format('D, d M Y H:i:s T');
				$app->setHeader('Expires', $expireheader, true);
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
	public function onBeforeCompileHead(): void
	{
		$app  = Factory::getApplication();
		$view = $app->input->get('view');

		if($app->getName() !== 'site' || ($app->input->getCmd('format') !== 'html' && $app->input->getCmd('format')) || $app->input->getCmd('tmpl'))
		{
			return;
		}

		if($view !== 'article')
		{
			$image       = $this->coreTags()->image;
			$title       = $this->coreTags()->title;
			$description = $this->coreTags()->description;

			$plugins = $app->triggerEvent('onJUPWAImage', [ $app->input->getCmd('option') ]);
			foreach($plugins as $plugin)
			{
				$image = $plugin;
			}

			$img = $this->coreTags($image)->img;

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

			// Integration
			$app->triggerEvent('onJUPWASchema');

			if($this->params->get('tw') == 1)
			{
				$app->triggerEvent('onJUPWATwitter', [ $this->params ]);
			}

			if($this->params->get('og') == 1)
			{
				$app->triggerEvent('onJUPWAOG', [ $this->params ]);
			}
		}

		META::render([ 'params' => $this->params ]);
		META::facebook([ 'params' => $this->params ]);
		Schema::global([ 'params' => $this->params ]);
	}

	/**
	 * @param        $context
	 * @param        $article
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onContentPrepare($context, $article): bool
	{
		$app         = Factory::getApplication();
		$integration = PluginHelper::importPlugin('jupwa');
		$use_access  = $app->triggerEvent('onJUPWAAccess', [ $context ]);

		if($app->getName() !== 'site' || ($app->input->getCmd('format') !== 'html' && $app->input->getCmd('format')) || $app->input->getCmd('tmpl') || $context === 'com_finder.indexer' || ($integration && !in_array($context, $use_access)))
		{
			return true;
		}

		// Integration
		$app->triggerEvent('onJUPWAArticleSchema', [ $article ]);
		$app->triggerEvent('onJUPWAArticleTwitter', [
			$article,
			$this->params
		]);
		$app->triggerEvent('onJUPWAArticleOG', [ $article, $this->params ]);

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
	private function coreTags($plugin_image = null)
	{
		$app  = Factory::getApplication();
		$doc  = Factory::getDocument();
		$lang = Factory::getLanguage();

		$image = Images::display_default($this->params->get('selectimg', 0), $this->params->get('image', ''), $this->params->get('imagemain', ''));

		$title = HTML::text($doc->getTitle());
		if($app->getMenu()->getActive() !== $app->getMenu()->getDefault($lang->getTag()))
		{
			$title = $app->getMenu()->getActive()->title;
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
	 */
	private function checkBuffer($buffer): void
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
}
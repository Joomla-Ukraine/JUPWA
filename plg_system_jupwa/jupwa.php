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
use JUPWA\Helpers\Video;
use JUPWA\Joomla\Integration;
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

		$app = Factory::getApplication();
		if($app->getName() === 'site')
		{
			return;
		}

		$this->loadLanguage();

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
				$buffer = preg_replace('#xmlns="http://www\.w3\.org/1999/xhtml"#i', '', $buffer);
				$buffer = str_replace('  ', ' ', $buffer);
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
		$doc  = Factory::getDocument();
		$lang = Factory::getLanguage();

		$view = $app->input->get('view');

		if($app->getName() !== 'site' || ($app->input->getCmd('format') !== 'html' && $app->input->getCmd('format')) || $app->input->getCmd('tmpl'))
		{
			return;
		}

		if($view !== 'article')
		{
			if($this->params->get('og') == 1 || $this->params->get('tw') == 1)
			{
				$image = Images::display_default($this->params->get('selectimg', 0), $this->params->get('image', ''), $this->params->get('imagemain', ''));

				$components = [
					'com_jshopping' => $this->params->get('int_jshopping', 0),
					'com_k2'        => $this->params->get('int_k2', 0)
				];

				foreach($components as $com => $v)
				{
					if($v == 1)
					{
						$data  = $this->integration->component($com, [ 'option' => $app->input->getCmd('option') ]);
						$image = $data[ 'image' ];
					}
				}

				$title = HTML::text($doc->getTitle());
				if($app->getMenu()->getActive() !== $app->getMenu()->getDefault($lang->getTag()))
				{
					$title = $app->getMenu()->getActive()->title;
				}

				$description = HTML::html($doc->getMetaData('description'));

				$img = Images::display($image);

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
		}

		META::render([ 'params' => $this->params ]);

		if($view === 'article')
		{
			META::facebook([ 'params' => $this->params ]);
		}

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
		$app = Factory::getApplication();

		if($app->getName() !== 'site' || $context === 'com_finder.indexer' || !in_array($context, [
				'com_content.article',
				'com_k2.item',
				'com_k2.item-media'
			]))
		{
			return true;
		}

		if($app->getName() !== 'site' || ($app->input->getCmd('format') !== 'html' && $app->input->getCmd('format')) || $app->input->getCmd('tmpl'))
		{
			return true;
		}

		if(($this->params->get('og') != 1) || ($this->params->get('og') != 1 && $this->params->get('tw') != 1))
		{
			return true;
		}

		// Title
		$title = HTML::text($article->title);

		// Introtext
		$intro  = $article->introtext;
		$alltxt = $article->introtext . $article->fulltext;

		$yt = Video::YouTube($article);
		if($this->params->get('int_seblod', 0) == 1)
		{
			$yt = '';
		}

		if($this->params->get('int_jshopping', 0) == 1)
		{
			$yt = '';
		}

		if($this->params->get('int_k2', 0) == 1)
		{
			$yt = Video::YouTube($article);
		}

		// Description
		$desc = $article->metadesc;
		if($article->metadesc !== '' && $this->params->get('usemeta') == 1)
		{
			$desc = $article->metadesc;
		}
		elseif($intro !== null && $intro !== '')
		{
			$desc = $intro;
		}

		if($article->metadesc != '')
		{
			$desc = $article->title;
		}

		$startdate = '';
		$enddate   = '';
		$place     = '';
		$address   = '';
		$city      = '';
		$region    = '';
		$country   = '';
		$zip       = '';
		$price     = '';
		$currency  = '';
		$performer = '';
		$brand     = '';

		// component integration
		$components = [
			'com_cck' => $this->params->get('int_seblod', 0)
		];

		foreach($components as $com => $value)
		{
			if($value == 1)
			{
				$integration = Integration::is_com($com, [
					'component' => 'com_content',
					'option'    => $app->input->getCmd('option')
				]);

				if($integration === true)
				{
					$data = '\\JUPWA\\Joomla\\Integration\\' . $com::run([
							'params'  => $this->params,
							'article' => $article,
							'context' => $context
						]);

					$title     = $data[ 'title' ];
					$desc      = $data[ 'desc' ];
					$intro     = $data[ 'intro' ];
					$image     = $data[ 'image' ];
					$alltxt    = $data[ 'alltxt' ];
					$yt        = $data[ 'yt' ];
					$startdate = $data[ 'startdate' ];
					$enddate   = $data[ 'enddate' ];
					$place     = $data[ 'place' ];
					$address   = $data[ 'address' ];
					$city      = $data[ 'city' ];
					$region    = $data[ 'region' ];
					$country   = $data[ 'country' ];
					$zip       = $data[ 'zip' ];
					$price     = $data[ 'price' ];
					$currency  = $data[ 'currency' ];
					$performer = $data[ 'performer' ];
					$brand     = $data[ 'brand' ];
				}
			}
		}

		$description = strip_tags(HTML::html($desc));
		$description = HTML::compress($description);

		if(!isset($image))
		{
			$image = Images::image_storage([
				'article' => $article,
				'params'  => $this->params,
				'text'    => $alltxt,
				'alltxt'  => $alltxt,
			]);
		}

		$img = Images::display($image);

		OG::tag([
			'type'           => 'article',
			'title'          => $title,
			'image'          => $img->image,
			'image_width'    => $img->width,
			'image_height'   => $img->height,
			'description'    => $description,
			'article'        => $article,
			'youtube'        => $yt,
			'use_rating'     => $this->params->get('use_rating'),
			'schema_product' => $this->params->get('schema_product'),
			'schema_event'   => $this->params->get('schema_event')
		]);

		OG::twitter([
			'params'       => $this->params,
			'title'        => $title,
			'image'        => $img->image,
			'image_width'  => $img->width,
			'image_height' => $img->height,
			'description'  => $description,
			'youtube'      => $yt
		]);

		Schema::schema([
			'params'       => $this->params,
			'title'        => $title,
			'image'        => $img->image,
			'image_width'  => $img->width,
			'image_height' => $img->height,
			'description'  => $description,
			'intro'        => $intro,
			'article'      => $article,
			'yt'           => $yt,
			'startdate'    => $startdate,
			'enddate'      => $enddate,
			'place'        => $place,
			'address'      => $address,
			'city'         => $city,
			'region'       => $region,
			'country'      => $country,
			'zip'          => $zip,
			'price'        => $price,
			'currency'     => $currency,
			'performer'    => $performer,
			'brand'        => $brand
		]);

		return true;
	}

	/**
	 * @param $buffer
	 *
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private function checkBuffer($buffer): void
	{
		$app = Factory::getApplication();

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
					break;
			}

			$app->enqueueMessage($message, 'error');
		}
	}
}
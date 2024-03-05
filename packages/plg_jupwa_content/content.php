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

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\Schema;

defined('_JEXEC') or die;

class PlgJUPWAContent extends CMSPlugin
{
	/**
	 * PlgJUPWASeblod constructor.
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

		$this->app = Factory::getApplication();
	}

	/**
	 * @param $article
	 * @param $params
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleSchema($article, $params): void
	{
		$option = [
			'params'       => $this->params,
			'title'        => $this->core($article)->title,
			'image'        => $this->image($article, $params)->image,
			'image_width'  => $this->image($article, $params)->width,
			'image_height' => $this->image($article, $params)->height,
			'description'  => $this->core($article)->description,
			'intro'        => $this->core($article)->intro,
			'article'      => $article
		];

		Schema::article_news($option);
		Schema::article($option);
		Schema::article_blogposting($option);
	}

	/**
	 * @param $article
	 * @param $params
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleOG($article, $params): void
	{
		if($params->get('og') == 1)
		{
			$itemid          = $this->app->input->getInt('Itemid');
			$og_type_website = $this->params->get('og_type_website', 0);
			$og_website      = $this->params->get('og_website_menus');

			$type = 'article';
			if(is_array($og_website) && $og_type_website && in_array($itemid, $og_website))
			{
				$type = 'website';
			}

			OG::tag([
				'params'       => $params,
				'type'         => $type,
				'title'        => $this->core($article)->title,
				'image'        => $this->image($article, $params)->image,
				'image_width'  => $this->image($article, $params)->width,
				'image_height' => $this->image($article, $params)->height,
				'description'  => $this->core($article)->description
			], [
				'headline' => $this->core($article)->title
			]);

			if($og_type_website == 0)
			{
				OG::tagArticle([
					'params'  => $params,
					'article' => $article
				]);
			}
		}
	}

	/**
	 * @param $article
	 * @param $params
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleTwitter($article, $params): void
	{
		if($params->get('tw') == 1)
		{
			OG::twitter([
				'params'       => $params,
				'title'        => $this->core($article)->title,
				'image'        => $this->image($article, $params)->image,
				'image_width'  => $this->image($article, $params)->width,
				'image_height' => $this->image($article, $params)->height,
				'description'  => $this->core($article)->description
			]);
		}
	}

	/**
	 * @param $article
	 * @param $params
	 *
	 * @return false|object
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private function image($article, $params)
	{
		$image = Images::image_storage([
			'article' => $article,
			'params'  => $params,
			'text'    => $this->core($article)->text,
			'alltxt'  => $this->core($article)->text,
		]);

		if($image !== '')
		{
			return Images::display($image);
		}

		$default_image = Images::display_default($params->get('selectimg'), $params->get('image'), $params->get('imagemain'));

		return Images::display($default_image);
	}

	/**
	 * @param $article
	 *
	 * @return object
	 *
	 * @since 1.0
	 */
	private function core($article)
	{
		// Title
		$title = HTML::text(($article->title ? : ''));

		// Introtext
		$intro = $article->introtext;
		$text  = $article->introtext . $article->fulltext;

		// Description
		$desc = ($article->metadesc ? : '');
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

		$description = strip_tags(HTML::html($desc));
		$description = HTML::compress($description);

		return (object) [
			'title'       => $title,
			'intro'       => $intro,
			'text'        => $text,
			'description' => $description,
		];
	}

	/**
	 * @param $context
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function onJUPWAAccess($context): bool
	{
		if($context === 'com_content.article')
		{
			return true;
		}

		return false;
	}
}
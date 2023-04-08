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

use Joomla\CMS\Plugin\CMSPlugin;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\Schema;
use JUPWA\Helpers\Video;

defined('_JEXEC') or die;

class PlgJUPWAContent extends CMSPlugin
{
	/**
	 * @param $article
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onGetArticleSchema($article): void
	{
		$option = [
			'params'       => $this->params,
			'title'        => $this->core($article)->title,
			'image'        => $this->image($article)->image,
			'image_width'  => $this->image($article)->width,
			'image_height' => $this->image($article)->height,
			'description'  => $this->core($article)->description,
			'intro'        => $this->core($article)->intro,
			'article'      => $article
		];

		Schema::article_news($option);
		Schema::article($option);
		Schema::youtube($option);
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
	public function onGetArticleOG($article, $params): void
	{
		if($params->get('og') == 1)
		{
			OG::tag([
				'params'       => $params,
				'type'         => 'article',
				'title'        => $this->core($article)->title,
				'image'        => $this->image($article)->image,
				'image_width'  => $this->image($article)->width,
				'image_height' => $this->image($article)->height,
				'description'  => $this->core($article)->description
			]);

			OG::tagArticle([
				'params'  => $params,
				'article' => $article
			]);

			OG::tagYouTube([
				'params'  => $params,
				'youtube' => $this->youtube($article)
			]);
		}
	}

	/**
	 * @param $article
	 * @param $params
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public function onGetArticleTwitter($article, $params): void
	{
		if($params->get('tw') == 1)
		{
			OG::twitter([
				'params'       => $params,
				'title'        => $this->core($article)->title,
				'image'        => $this->image($article)->image,
				'image_width'  => $this->image($article)->width,
				'image_height' => $this->image($article)->height,
				'description'  => $this->core($article)->description,
				'youtube'      => $this->youtube($article)
			]);
		}
	}

	/**
	 * @param $article
	 *
	 * @return object
	 *
	 * @since 1.0
	 */
	private function image($article)
	{
		$image = Images::image_storage([
			'article' => $article,
			'params'  => $this->params,
			'text'    => $this->core($article)->text,
			'alltxt'  => $this->core($article)->text,
		]);

		return Images::display($image);
	}

	/**
	 * @param $article
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private function youtube($article): string
	{
		return Video::YouTube($article);
	}

	private function core($article)
	{
		// Title
		$title = HTML::text($article->title);

		// Introtext
		$intro = $article->introtext;
		$text  = $article->introtext . $article->fulltext;

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
	public function onAccess($context): bool
	{
		if($context === 'com_content.article')
		{
			return true;
		}

		return false;
	}
}
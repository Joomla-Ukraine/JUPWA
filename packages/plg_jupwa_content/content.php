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

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\Video;

defined('_JEXEC') or die;

class PlgJUPWAContent extends CMSPlugin
{
	/**
	 * @param $article
	 * @param $text
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function onGetArticleImage($article, $text): string
	{
		return Images::image_storage([
			'article' => $article,
			'params'  => $this->params,
			'text'    => $text,
			'alltxt'  => $text,
		]);
	}

	/**
	 * @param $article
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public function onGetArticleYouTube($article): string
	{
		return Video::YouTube($article);
	}

	/**
	 * @param $article
	 * @param $text
	 *
	 * @return true
	 *
	 * @since 1.0
	 */
	public function onGetArticleSchema($article, $text): bool
	{
		$doc = Factory::getDocument();

		return true;
	}

	/**
	 * @param $context
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function onContentAccess($context): bool
	{
		return $context === 'com_content.article';
	}
}
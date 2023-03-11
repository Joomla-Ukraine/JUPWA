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
use JUPWA\Helpers\Images;

defined('_JEXEC') or die;

class PlgJUPWAContent extends CMSPlugin
{
	/**
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onGetData($article, $text): array
	{
		$image = Images::image_storage([
			'article' => $article,
			'params'  => $this->params,
			'text'    => $text,
			'alltxt'  => $text,
		]);

		return [ 'image' => $image ];
	}

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onContentAccess($context): bool
	{
		return $context === 'com_content.article';
	}
}
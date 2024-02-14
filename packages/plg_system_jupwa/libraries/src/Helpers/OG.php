<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class OG
{
	/**
	 *
	 * @param array $option
	 * @param array $parameters
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function tag(array $option = [], array $parameters = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(isset($option[ 'params' ]) && $option[ 'params' ]->get('og') == 1)
		{
			$app  = Factory::getApplication();
			$lang = $app->getLanguage();

			$doc->setMetaData('og:locale', str_replace('-', '_', $lang->getTag()), 'property');
			$doc->setMetaData('og:type', $option[ 'type' ], 'property');
			$doc->setMetaData('og:title', $option[ 'title' ], 'property');
			$doc->setMetaData('og:description', $option[ 'description' ], 'property');
			$doc->setMetaData('og:url', Uri::current(), 'property');
			$doc->setMetaData('og:site_name', $app->get('sitename'), 'property');

			if(isset($option[ 'image' ]))
			{
				$doc->setMetaData('og:image', HTMLHelper::cleanImageURL($option[ 'image' ])->url, 'property');

				if((isset($option[ 'image_width' ]) && $option[ 'image_width' ] > 0) || (isset($option[ 'image_height' ]) && $option[ 'image_height' ] > 0))
				{
					$doc->setMetaData('og:image:width', $option[ 'image_width' ], 'property');
					$doc->setMetaData('og:image:height', $option[ 'image_height' ], 'property');
				}
			}

			foreach($parameters as $k => $v)
			{
				$doc->setMetaData('og:' . $k, $v, 'property');
			}
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function tagYouTube(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(isset($option[ 'params' ]) && $option[ 'params' ]->get('ogvideo_youtube') == 1 && $option[ 'params' ]->get('og') == 1)
		{
			if(preg_match_all('#(youtube.com)/embed/([0-9A-Za-z-_]+)#i', $option[ 'youtube' ], $match) || preg_match_all('#(youtube.com)/watch\?v=([0-9A-Za-z-_]+)#i', $option[ 'youtube' ], $match))
			{
				$doc->setMetaData('og:video:url', 'https://' . $match[ 0 ][ 0 ], 'property');
				$doc->setMetaData('og:video:secure_url', 'https://' . $match[ 0 ][ 0 ], 'property');
				$doc->setMetaData('og:video:type', 'text/html', 'property');
				$doc->setMetaData('og:video:width', '640', 'property');
				$doc->setMetaData('og:video:height', '480', 'property');
				$doc->setMetaData('_og:video', 'https://' . str_replace([
						'/embed/',
						'/watch?v='
					], '/v/', $match[ 0 ][ 0 ]), 'property');
				$doc->setMetaData('_og:video:secure_url', 'https://' . str_replace([
						'/embed/',
						'/watch?v='
					], '/v/', $match[ 0 ][ 0 ]), 'property');
				$doc->setMetaData('_og:video:type', 'application/x-shockwave-flash', 'property');
				$doc->setMetaData('_og:video:width', '640', 'property');
				$doc->setMetaData('_og:video:height', '480', 'property');
			}
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function tagArticle(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(isset($option[ 'params' ]) && $option[ 'params' ]->get('og') == 1)
		{
			if(isset($option[ 'article' ]->modified) && !($option[ 'article' ]->modified === '' || $option[ 'article' ]->modified === '0000-00-00 00:00:00'))
			{
				$doc->setMetaData('og:updated_time', date('c', strtotime($option[ 'article' ]->modified)), 'property');
				$doc->setMetaData('article:modified_time', date('c', strtotime($option[ 'article' ]->modified)), 'property');
			}

			if(isset($option[ 'article' ]->publish_up) !== '')
			{
				$doc->setMetaData('article:published_time', date('c', strtotime($option[ 'article' ]->publish_up)), 'property');
			}

			if(isset($option[ 'article' ]->category_title) !== '')
			{
				$doc->setMetaData('article:section', $option[ 'article' ]->category_title, 'property');
			}

			if(isset($option[ 'article' ]->metakey) != '')
			{
				if(Facebook::bot() === false)
				{
					$doc->setMetaData('news_keywords', $option[ 'article' ]->metakey, 'property');
				}

				$_metakeys = explode(',', $option[ 'article' ]->metakey);
				$i         = 0;
				foreach($_metakeys as $_metakey)
				{
					$doc->setMetaData('article:tag_' . $i . '_', trim($_metakey), 'property');
					$i++;
				}
			}
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function twitter(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(isset($option[ 'params' ]) && $option[ 'params' ]->get('tw') == 1)
		{
			$doc->setMetaData('twitter:card', 'summary_large_image');

			if($option[ 'description' ])
			{
				$doc->setMetaData('twitter:description', $option[ 'description' ]);
			}

			if($option[ 'title' ])
			{
				$doc->setMetaData('twitter:title', $option[ 'title' ]);
			}

			if($option[ 'params' ]->get('twsite'))
			{
				$doc->setMetaData('twitter:site', $option[ 'params' ]->get('twsite'));
			}

			if($option[ 'params' ]->get('twcreator'))
			{
				$doc->setMetaData('twitter:creator', $option[ 'params' ]->get('twcreator'));
			}

			if(isset($option[ 'image' ]))
			{
				$doc->setMetaData('twitter:image:src', $option[ 'image' ]);
			}

			if(isset($option[ 'youtube' ]) && $option[ 'youtube' ] && preg_match_all('#(youtube.com)/embed/([0-9A-Za-z]+)#i', $option[ 'youtube' ], $match))
			{
				$doc->setMetaData('twitter:player', 'https://' . $match[ 0 ][ 0 ]);
				$doc->setMetaData('twitter:player:width', '640');
				$doc->setMetaData('twitter:player:height', '480');
			}
		}
	}
}
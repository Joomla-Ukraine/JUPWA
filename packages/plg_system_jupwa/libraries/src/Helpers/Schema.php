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

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;
use JUPWA\Utils\Util;

defined('_JEXEC') or die();

class Schema
{
	/**
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function schema(array $option = []): void
	{
		self::article_news($option);
		self::article($option);
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
	public static function global(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if($option[ 'params' ]->get('schema_search') == 1 && $option[ 'params' ]->get('schema_search_query'))
		{
			$json = Util::LD([
				'@context'        => 'https://schema.org',
				'@type'           => 'WebSite',
				'name'            => $app->get('sitename'),
				'url'             => Uri::base(),
				'potentialAction' => [
					'@type'       => 'SearchAction',
					'target'      => $option[ 'params' ]->get('schema_search_query') . '{search_term_string}',
					'query-input' => 'required name=search_term_string'
				]
			]);

			$doc->addCustomTag($json);
		}

		if($option[ 'params' ]->get('schema_sitename') == 1 && $option[ 'params' ]->get('schema_search') != 1)
		{
			$option_sitename_alt = $option[ 'params' ]->get('schema_sitename_alt');

			$json = Util::LD([
				'@context'      => 'https://schema.org',
				'@type'         => 'WebSite',
				'name'          => $app->get('sitename'),
				'alternateName' => $option_sitename_alt,
				'url'           => Uri::base()
			]);

			$doc->addCustomTag($json);
		}

		if($option[ 'params' ]->get('schema_logo') == 1)
		{
			$file = 'favicons/icon_512.png';
			if(file_exists(JPATH_SITE . '/' . $file))
			{
				$logo = Uri::root() . $file;
				$json = Util::LD([
					'@context' => 'https://schema.org',
					'@type'    => 'Organization',
					'url'      => Uri::base(),
					'logo'     => $logo
				]);

				$doc->addCustomTag($json);
			}
		}

		if($option[ 'params' ]->get('schema_social'))
		{
			$socials = (array) $option[ 'params' ]->get('schema_social_link');
			if($socials)
			{
				$social_link = [];
				foreach($socials as $social)
				{
					$social_link[] = $social->link;
				}

				if($social_link)
				{
					$json = Util::LD([
						'@context' => 'https://schema.org',
						'@type'    => $option[ 'params' ]->get('schema_social_type'),
						'name'     => $option[ 'params' ]->get('schema_social_type') === 'Person' ? $option[ 'params' ]->get('schema_social_person') : $app->get('sitename'),
						'url'      => Uri::base(),
						'sameAs'   => [
							$social_link
						]
					]);

					$doc->addCustomTag($json);
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
	public static function article_news(array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = $app->getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, $option[ 'params' ]->get('schema_news_article') ? : []))
		{
			$logo     = Uri::root() . Util::get_thumbs()->{'article_logo'};
			$sitename = $app->get('sitename');
			$url      = str_replace('[id]', $option[ 'article' ]->created_by, $option[ 'params' ]->get('schema_news_article_person', ''));

			$json = [
				'@context'         => 'https://schema.org',
				'@type'            => 'NewsArticle',
				'headline'         => $option[ 'title' ],
				'name'             => $option[ 'title' ],
				'description'      => $option[ 'description' ],
				'articleBody'      => StringHelper::substr(strip_tags($option[ 'intro' ]), 0, 260),
				'mainEntityOfPage' => [
					'@type' => 'WebPage',
					'@id'   => Uri::current()
				],
				'thumbnailUrl'     => $option[ 'image' ],
				'image'            => [
					'@type'  => 'ImageObject',
					'url'    => $option[ 'image' ],
					'height' => $option[ 'image_height' ],
					'width'  => $option[ 'image_width' ]
				],
				'dateCreated'      => date('c', strtotime($option[ 'article' ]->created)),
				'dateModified'     => date('c', strtotime($option[ 'article' ]->modified)),
				'datePublished'    => date('c', strtotime($option[ 'article' ]->publish_up)),
				'interactionCount' => $option[ 'article' ]->hits,
				'author'           => [
					'@type' => 'Person',
					'name'  => $option[ 'article' ]->author,
					'url'   => (isset($url) ? : '')
				],
				'publisher'        => [
					'@type' => 'Organization',
					'name'  => $sitename,
					'logo'  => [
						'@type'  => 'ImageObject',
						'url'    => $logo,
						'height' => 60,
						'width'  => 600
					],
				]
			];

			$doc->addCustomTag(Util::LD($json));
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
	public static function article_blogposting(array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = $app->getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, $option[ 'params' ]->get('schema_blogposting') ? : []))
		{
			$logo     = Uri::root() . Util::get_thumbs()->{'article_logo'};
			$sitename = $app->get('sitename');
			$url      = str_replace('[id]', $option[ 'article' ]->created_by, $option[ 'params' ]->get('schema_article_blogposting_person', ''));

			$json = [
				'@context'         => 'https://schema.org',
				'@type'            => 'BlogPosting',
				'@id'              => Uri::current(),
				'mainEntityOfPage' => [
					'@type' => 'WebPage',
					'@id'   => Uri::current()
				],
				'headline'         => $option[ 'title' ],
				'name'             => $option[ 'title' ],
				'description'      => $option[ 'description' ],
				'dateCreated'      => date('c', strtotime($option[ 'article' ]->created)),
				'datePublished'    => date('c', strtotime($option[ 'article' ]->publish_up)),
				'dateModified'     => date('c', strtotime($option[ 'article' ]->modified)),
				'author'           => [
					'@type' => 'Person',
					'name'  => $option[ 'article' ]->author,
					'url'   => $url,
				],
				'publisher'        => [
					'@type' => 'Organization',
					'name'  => $sitename,
					'logo'  => [
						'@type'  => 'ImageObject',
						'url'    => $logo,
						'height' => 60,
						'width'  => 600
					],
				],
				'image'            => [
					'@type'  => 'ImageObject',
					'url'    => $option[ 'image' ],
					'height' => $option[ 'image_height' ],
					'width'  => $option[ 'image_width' ]
				],
				'url'              => Uri::current(),
				'articleBody'      => StringHelper::substr(strip_tags($option[ 'intro' ]), 0),
				'thumbnailUrl'     => $option[ 'image' ]
			];

			$doc->addCustomTag(Util::LD($json));
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
	public static function article(array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = $app->getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, $option[ 'params' ]->get('schema_article') ? : []))
		{
			$logo     = (isset(Util::get_thumbs()->{'article_logo'}) ? Uri::root() . Util::get_thumbs()->{'article_logo'} : '');
			$sitename = $app->get('sitename');
			$url      = str_replace('[id]', $option[ 'article' ]->created_by, $option[ 'params' ]->get('schema_article_person', ''));

			$json = [
				'@context'         => 'https://schema.org',
				'@type'            => 'Article',
				'name'             => $option[ 'title' ],
				'url'              => Uri::current(),
				'description'      => $option[ 'description' ],
				'image'            => [
					'@type'  => 'ImageObject',
					'url'    => $option[ 'image' ],
					'height' => $option[ 'image_height' ],
					'width'  => $option[ 'image_width' ]
				],
				'publisher'        => [
					'@type' => 'Organization',
					'name'  => $sitename,
					'logo'  => [
						'@type'  => 'ImageObject',
						'url'    => $logo,
						'height' => 60,
						'width'  => 600
					],
				],
				'dateCreated'      => date('c', strtotime($option[ 'article' ]->created)),
				'dateModified'     => date('c', strtotime($option[ 'article' ]->modified)),
				'datePublished'    => date('c', strtotime($option[ 'article' ]->publish_up)),
				'author'           => [
					'@type' => 'Person',
					'name'  => $option[ 'article' ]->author,
					'url'   => $url,
				],
				'articleBody'      => StringHelper::substr(strip_tags($option[ 'intro' ]), 0, 260),
				'mainEntityOfPage' => [
					'@type' => 'WebPage',
					'@id'   => Uri::current()
				],
				'headline'         => $option[ 'title' ]
			];

			$doc->addCustomTag(Util::LD($json));
		}
	}
}
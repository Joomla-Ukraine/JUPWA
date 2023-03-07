<?php
/**
 * @package     JUPWA\Helpers
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\StringHelper;
use Joomla\CMS\Uri\Uri;
use JUPWA\Utils\Util;

defined('_JEXEC') or die();

class Schema
{
	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function global(array $option = []): bool
	{
		$app = Factory::getApplication();
		$doc = Factory::getDocument();

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

		if($option[ 'params' ]->get('schema_logo') == 1 && $option[ 'params' ]->get('schema_logo_img'))
		{
			$option_logo_img = Uri::base() . $option[ 'params' ]->get('schema_logo_img');

			$json = Util::LD([
				'@context' => 'https://schema.org',
				'@type'    => 'Organization',
				'url'      => Uri::base(),
				'logo'     => $option_logo_img
			]);

			$doc->addCustomTag($json);
		}

		if($option[ 'params' ]->get('schema_social') == 1)
		{
			$schama_sl = [
				$option[ 'params' ]->get('schema_social_l1') ? : '',
				$option[ 'params' ]->get('schema_social_l2') ? : '',
				$option[ 'params' ]->get('schema_social_l3') ? : '',
				$option[ 'params' ]->get('schema_social_l4') ? : '',
				$option[ 'params' ]->get('schema_social_l5') ? : '',
				$option[ 'params' ]->get('schema_social_l6') ? : '',
				$option[ 'params' ]->get('schema_social_l7') ? : '',
				$option[ 'params' ]->get('schema_social_l8') ? : '',
				$option[ 'params' ]->get('schema_social_l9') ? : '',
				$option[ 'params' ]->get('schema_social_l10') ? : '',
				$option[ 'params' ]->get('schema_social_l11') ? : '',
				$option[ 'params' ]->get('schema_social_l12') ? : '',
				$option[ 'params' ]->get('schema_social_l13') ? : '',
				$option[ 'params' ]->get('schema_social_l14') ? : '',
				$option[ 'params' ]->get('schema_social_l15') ? : '',
				$option[ 'params' ]->get('schema_social_l16') ? : '',
				$option[ 'params' ]->get('schema_social_l17') ? : ''
			];

			$json = Util::LD([
				'@context' => 'https://schema.org',
				'@type'    => $option[ 'params' ]->get('schema_social_type'),
				'name'     => $option[ 'params' ]->get('schema_social_type') === 'Person' ? $option[ 'params' ]->get('schema_social_person') : $app->get('sitename'),
				'url'      => Uri::base(),
				'sameAs'   => [
					array_filter($schama_sl)
				]
			]);

			$doc->addCustomTag($json);
		}

		return true;
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function article_news(array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = Factory::getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, $option[ 'params' ]->get('schema_news_article') ? : [], true))
		{
			$sitename = '';
			if($option[ 'params' ]->get('news_article_logo'))
			{
				$news_article_logo = Uri::base() . $option[ 'params' ]->get('news_article_logo');
				$sitename          = $app->get('sitename');
			}

			$url = str_replace('[id]', $option[ 'article' ]->created_by, $option[ 'params' ]->get('schema_article_person', ''));

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
					'url'   => $url,
				],
				'publisher'        => [
					'@type' => 'Organization',
					'name'  => $sitename,
					'logo'  => [
						'@type'  => 'ImageObject',
						'url'    => $news_article_logo,
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
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function article(array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = Factory::getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, $option[ 'params' ]->get('schema_article') ? : [], true))
		{
			$sitename = '';
			if($option[ 'params' ]->get('article_logo'))
			{
				$article_logo = Uri::base() . $option[ 'params' ]->get('article_logo');
				$sitename     = $app->get('sitename');
			}

			$url = str_replace('[id]', $option[ 'article' ]->created_by, $option[ 'params' ]->get('schema_article_person', ''));

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
						'url'    => $article_logo,
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

	/**
	 *
	 * @param   array  $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function rating(array $option = []): array
	{
		$rating = [];
		if(isset($option[ 'use_rating' ]) && $option[ 'use_rating' ] == 1 && $option[ 'article' ]->rating_count > 0)
		{
			$rating = [
				'aggregateRating' => [
					'@type'       => 'AggregateRating',
					'bestRating'  => 5,
					'ratingValue' => $option[ 'article' ]->rating,
					'reviewCount' => $option[ 'article' ]->rating_count
				]
			];
		}

		return $rating;
	}

	/**
	 *
	 * @param   array  $rating
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function product(array $rating = [], array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = Factory::getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, isset($option[ 'schema_product' ]) && $option[ 'schema_product' ] ? : [], true))
		{
			$json = [
				'@context'         => 'https://schema.org',
				'@type'            => 'Product',
				'name'             => $option[ 'title' ],
				'image'            => $option[ 'image' ],
				'description'      => $option[ 'description' ],
				'brand'            => [
					'@type' => 'Thing',
					'name'  => $option[ 'brand' ]
				],
				'offers'           => [
					'@type'           => 'Offer',
					'priceCurrency'   => $option[ 'currency' ],
					'price'           => $option[ 'price' ],
					'priceValidUntil' => date('c', strtotime($option[ 'enddate' ])),
					'itemCondition'   => 'https://schema.org/UsedCondition',
					'availability'    => 'https://schema.org/InStock',
					'url'             => Uri::current(),
					'validFrom'       => date('c', strtotime($option[ 'startdate' ]))
				],
				'interactionCount' => $option[ 'article' ]->hits,
			];

			if(!$option[ 'brand' ])
			{
				unset($json[ 'brand' ]);
			}

			if(!$option[ 'startdate' ])
			{
				unset($json[ 'offers' ][ 'validFrom' ]);
			}

			if(!$option[ 'enddate' ])
			{
				unset($json[ 'offers' ][ 'priceValidUntil' ]);
			}

			if(!$option[ 'price' ])
			{
				unset($json[ 'offers' ]);
			}

			if(!$option[ 'currency' ])
			{
				unset($json[ 'offers' ][ 'priceCurrency' ]);
			}

			if($option[ 'params' ]->get('use_rating') == 1)
			{
				$json = array_merge($json, $rating);
			}

			$doc->addCustomTag(Util::LD($json));
		}
	}

	/**
	 *
	 * @param   array  $rating
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function event(array $rating = [], array $option = []): void
	{
		$app    = Factory::getApplication();
		$doc    = Factory::getDocument();
		$Itemid = $app->input->getInt('Itemid');

		if(in_array($Itemid, isset($option[ 'schema_event' ]) && $option[ 'schema_event' ] ? : [], true))
		{
			$json = [
				'@context'         => 'https://schema.org',
				'@type'            => 'Event',
				'name'             => $option[ 'title' ],
				'startDate'        => date('c', strtotime($option[ 'startdate' ])),
				'location'         => [
					'@type'   => 'Place',
					'name'    => $option[ 'place' ],
					'address' => [
						'@type'           => 'PostalAddress',
						'streetAddress'   => $option[ 'address' ],
						'addressLocality' => $option[ 'city' ],
						'postalCode'      => $option[ 'zip' ],
						'addressRegion'   => $option[ 'region' ],
						'addressCountry'  => $option[ 'country' ]
					],
				],
				'image'            => [
					$option[ 'image' ]
				],
				'description'      => $option[ 'description' ],
				'endDate'          => date('c', strtotime($option[ 'enddate' ])),
				'offers'           => [
					'@type'         => 'Offer',
					'url'           => Uri::current(),
					'price'         => $option[ 'price' ],
					'priceCurrency' => $option[ 'currency' ],
					'availability'  => 'https://schema.org/InStock',
					'validFrom'     => date('c', strtotime($option[ 'startdate' ]))
				],
				'performer'        => [
					'@type' => 'PerformingGroup',
					'name'  => $option[ 'performer' ]
				],
				'interactionCount' => $option[ 'article' ]->hits,
			];

			if(!$option[ 'performer' ])
			{
				unset($json[ 'performer' ]);
			}

			if(!$option[ 'price' ])
			{
				unset($json[ 'offers' ][ 'price' ]);
			}

			if(!$option[ 'currency' ])
			{
				unset($json[ 'offers' ][ 'priceCurrency' ]);
			}

			if($option[ 'params' ]->get('use_rating') == 1)
			{
				$json = array_merge($json, $rating);
			}

			$doc->addCustomTag(Util::LD($json));
		}
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function youtube(array $option = []): void
	{
		$doc = Factory::getDocument();
		if(isset($option[ 'yt' ]))
		{
			$json = Util::LD([
				'@context'     => 'https://schema.org',
				'@type'        => 'VideoObject',
				'name'         => $option[ 'title' ],
				'description'  => $option[ 'description' ],
				'thumbnailUrl' => Video::parse($option[ 'yt' ]),
				'uploadDate'   => date('c', strtotime($option[ 'article' ]->created)),
				'contentUrl'   => str_replace('/embed/', '/watch?v=', $option[ 'youtube' ]),
				'embedUrl'     => str_replace('/watch?v=', '/embed/', $option[ 'youtube' ])
			]);

			$doc->addCustomTag($json);
		}
	}

	/**
	 * @param   array  $option
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function schema(array $option = []): bool
	{
		//$image     = '';
		//$use_image = false;
		/*	if((isset($option[ 'image' ]) && $option[ 'image' ]))
			{
				$use_image     = true;
				$FastImageSize = new FastImageSize();
				$imageSize     = $FastImageSize->getImageSize($option[ 'image' ]);

				$image = $option[ 'image' ];
				if(URL::is_url($option[ 'image' ]) === false)
				{
					$image = Uri::base() . $option[ 'image' ];
				}
			}
	*/

		self::article_news($option);
		self::article($option);

		$rating = self::rating($option);

		self::product($rating, $option);
		self::event($rating, $option);

		return true;
	}
}
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
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\OG;
use JUPWA\Utils\Util;

defined('_JEXEC') or die;

class PlgJUPWAJShopping extends CMSPlugin
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
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWASchema($params): void
	{
		$use_schema = $this->params->get('use_schema', 0);

		if($use_schema == 1)
		{
			$id = $this->app->input->getInt('product_id');

			if($id > 0)
			{
				$doc         = $this->app->getDocument();
				$jshopConfig = JSFactory::getConfig();

				$product = $this->core();
				$title   = $product->title;
				$text    = $product->text;

				$json = [
					'@context'         => 'https://schema.org',
					'@type'            => 'Product',
					'name'             => $title,
					'description'      => $text,
					'sku'              => $product->product_ean,
					'image'            => [
						'@type'  => 'ImageObject',
						'url'    => $this->image($params)->image,
						'height' => $this->image($params)->width,
						'width'  => $this->image($params)->height
					],
					'offers'           => [
						'@type'         => 'AggregateOffer',
						'url'           => Uri::current(),
						'offerCount'    => $product->product_quantity,
						'price'         => $product->price,
						'priceCurrency' => htmlspecialchars($jshopConfig->currency_code_iso),
						'availability'  => 'https://schema.org/InStock',
						'validFrom'     => date('c', strtotime($product->product_date_added))
					],
					'url'              => Uri::current(),
					'interactionCount' => $product->hits
				];

				$reviews = $this->product_review();

				$review = [];
				if(is_countable($reviews) && count($reviews) > 0)
				{
					foreach($reviews as $r)
					{
						$review[] = [
							'@type'         => 'Review',
							'author'        => [
								'@type' => 'Person',
								'name'  => $r->user_name
							],
							'datePublished' => $r->time,
							'reviewBody'    => $r->review
						];
					}

					$review = [
						'review' => $review
					];
				}

				$brand = [];
				if($product->product_manufacturer_id > 0)
				{
					$brand = [
						'brand' => [
							'@type' => 'Brand',
							'name'  => $this->brand($product->product_manufacturer_id)
						]
					];
				}

				$rating = [];
				if($product->reviews_count > 0)
				{
					$rating = [
						'aggregateRating' => [
							'@type'       => 'AggregateRating',
							'bestRating'  => $jshopConfig->max_mark,
							'ratingValue' => $product->average_rating,
							'ratingCount' => $product->reviews_count
						]
					];
				}

				$json = array_merge($json, $brand, $rating, $review);

				$doc->addCustomTag(Util::LD($json));
			}
		}
	}

	/**
	 * @param $params
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAOG($params): void
	{
		$id = $this->app->input->getInt('product_id');

		if($id > 0 && $params->get('og') == 1)
		{
			$jshopConfig = JSFactory::getConfig();

			OG::tag([
				'params'       => $params,
				'type'         => 'product',
				'title'        => $this->core()->title,
				'image'        => $this->image($params)->image,
				'image_width'  => $this->image($params)->width,
				'image_height' => $this->image($params)->height,
				'description'  => $this->core()->description
			], [
				'price:amount'   => $this->core()->price,
				'price:currency' => htmlspecialchars($jshopConfig->currency_code_iso)
			]);
		}
	}

	/**
	 * @param $params
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWATwitter($params): void
	{
		$id = $this->app->input->getInt('product_id');

		if($id > 0 && $params->get('tw') == 1)
		{
			OG::twitter([
				'params'       => $params,
				'title'        => $this->core()->title,
				'image'        => $this->image($params)->image,
				'image_width'  => $this->image($params)->width,
				'image_height' => $this->image($params)->height,
				'description'  => $this->core()->intro
			]);
		}
	}

	/**
	 * @return false|object
	 *
	 * @since 1.0
	 */
	private function image($params): object|bool
	{
		$jshopConfig = JSFactory::getConfig();
		$image       = $this->core()->image;

		if($image !== '')
		{
			$image = str_replace(JPATH_ROOT . '/', '', $jshopConfig->image_product_path) . '/' . 'full_' . $image;

			return Images::display($image);
		}

		$default_image = Images::display_default($params->get('selectimg'), $params->get('image'), $params->get('imagemain'));

		return Images::display($default_image);
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	private function brand($id): mixed
	{
		$lang     = $this->app->getLanguage();
		$lang_tag = $lang->getTag();

		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		$query->select([
			$db->quoteName('name_' . $lang_tag) . ' as name',
		]);
		$query->from('#__jshopping_manufacturers');
		$query->where($db->quoteName('manufacturer_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadObject()->name;
	}

	/**
	 * @return object
	 *
	 * @since 1.0
	 */
	private function product_review(): object
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$id    = $this->app->input->getInt('product_id');
		$query = $db->getQuery(true);

		$query->select([
			'review_id',
			'user_id',
			'user_name',
			'user_email',
			'time',
			'review',
			'mark'
		]);
		$query->from('#__jshopping_products_reviews');
		$query->where($db->quoteName('publish') . ' = ' . $db->quote(1));
		$query->where($db->quoteName('product_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);

		return (object) $db->loadObjectList();
	}

	/**
	 * @return object
	 *
	 * @since 1.0
	 */
	private function core(): object
	{
		$db       = Factory::getContainer()->get(DatabaseInterface::class);
		$lang     = $this->app->getLanguage();
		$lang_tag = $lang->getTag();

		$id    = $this->app->input->getInt('product_id');
		$query = $db->getQuery(true);

		$query->select([
			'image',
			'hits',
			'average_rating',
			'reviews_count',
			'product_date_added',
			'product_manufacturer_id',
			'product_quantity',
			'product_price as price',
			$db->quoteName('name_' . $lang_tag) . ' as name',
			$db->quoteName('short_description_' . $lang_tag) . ' as short',
			$db->quoteName('description_' . $lang_tag) . ' as description',
			$db->quoteName('meta_description_' . $lang_tag) . ' as meta'
		]);
		$query->from('#__jshopping_products');
		$query->where($db->quoteName('product_id') . ' = ' . $db->quote($id));
		$db->setQuery($query);
		$row = $db->loadObject();

		// Title
		$title = HTML::text($row->name);

		// Introtext
		$intro       = $row->short;
		$description = $row->description;
		$text        = $intro . ' ' . $description;

		// Description
		$desc = ($row->meta ? : '');
		if($row->meta !== '' && $this->params->get('usemeta') == 1)
		{
			$desc = $row->meta;
		}
		elseif($intro !== null && $intro !== '')
		{
			$desc = $intro;
		}

		if($row->meta != '')
		{
			$desc = $title;
		}

		$description = strip_tags(HTML::html($desc));
		$description = HTML::compress($description);

		$text = strip_tags(HTML::html($text));
		$text = HTML::compress($text);

		return (object) [
			'title'                   => $title,
			'image'                   => (isset($row->image) && $row->image ? $row->image : ''),
			'intro'                   => $intro,
			'text'                    => $text,
			'description'             => $description,
			'product_date_added'      => $row->product_date_added,
			'price'                   => $row->price,
			'hits'                    => $row->hits,
			'average_rating'          => $row->average_rating,
			'product_manufacturer_id' => $row->product_manufacturer_id,
			'product_ean'             => (isset($row->product_ean) && $row->product_ean ? $row->product_ean : $id),
			'product_quantity'        => (int) $row->product_quantity,
			'reviews_count'           => $row->reviews_count
		];
	}

	/**
	 * @param $component
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function onJUPWAAccess($component): bool
	{
		if($component === 'com_jshopping')
		{
			return true;
		}

		return false;
	}
}
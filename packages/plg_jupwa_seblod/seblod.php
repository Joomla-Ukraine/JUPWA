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
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\CMSPlugin;
use JUPWA\Helpers\HTML;
use JUPWA\Helpers\Images;
use JUPWA\Helpers\OG;
use JUPWA\Helpers\Schema;

defined('_JEXEC') or die;

require_once __DIR__ . '/SeblodAPI.php';

#[AllowDynamicProperties]
class PlgJUPWASeblod extends CMSPlugin
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

		$this->app    = Factory::getApplication();
		$this->loaded = [];
	}

	/**
	 * @param $article
	 * @param $params
	 * @param $context
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleSchema($article, $params, $context): void
	{
		$option = [
			'params'       => $this->params,
			'title'        => $this->core($article, $context)->title,
			'image'        => $this->image($article, $params, $context)->image,
			'image_width'  => $this->image($article, $params, $context)->width,
			'image_height' => $this->image($article, $params, $context)->height,
			'description'  => $this->core($article, $context)->description,
			'intro'        => $this->core($article, $context)->intro,
			'article'      => $article
		];

		Schema::article_news($option);
		Schema::article($option);
		Schema::article_blogposting($option);
	}

	/**
	 * @param $article
	 * @param $params
	 * @param $context
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleOG($article, $params, $context): void
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
				'title'        => $this->core($article, $context)->title,
				'image'        => $this->image($article, $params, $context)->image,
				'image_width'  => $this->image($article, $params, $context)->width,
				'image_height' => $this->image($article, $params, $context)->height,
				'description'  => $this->core($article, $context)->description
			], [
				'headline' => $this->core($article, $context)->title
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
	 * @param $context
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function onJUPWAArticleTwitter($article, $params, $context): void
	{
		if($params->get('tw') == 1)
		{
			OG::twitter([
				'params'       => $params,
				'title'        => $this->core($article, $context)->title,
				'image'        => $this->image($article, $params, $context)->image,
				'image_width'  => $this->image($article, $params, $context)->width,
				'image_height' => $this->image($article, $params, $context)->height,
				'description'  => $this->core($article, $context)->description
			]);
		}
	}

	/**
	 * @param $article
	 * @param $params
	 * @param $context
	 *
	 * @return false|object
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private function image($article, $params, $context): object|bool
	{
		$image = $this->core($article, $context)->image;

		if($image !== '')
		{
			return Images::display($image);
		}

		$default_image = Images::display_default($params->get('selectimg'), $params->get('image'), $params->get('imagemain'));

		return Images::display($default_image);
	}

	private function core($article, $context): object
	{
		$id     = $this->app->input->getInt('id');
		$seblod = $this->seblod($id, [
			'params'  => $this->params,
			'option'  => $this->app->input->getCmd('option'),
			'article' => $article,
			'context' => $context
		]);

		$text  = $seblod[ 'desc' ];
		$intro = $seblod[ 'intro' ];

		// Title
		$title = HTML::text($seblod[ 'title' ]);

		// Description
		if($article->metadesc !== '' && $this->params->get('usemeta') == 1)
		{
			$desc = $article->metadesc;
		}
		elseif($intro !== null && $intro !== '')
		{
			$desc = $intro;
		}
		else
		{
			$desc = $title;
		}

		$description = strip_tags(HTML::html($desc));
		$description = HTML::compress($description);

		return (object) [
			'title'       => $title,
			'intro'       => $intro,
			'text'        => $text,
			'description' => $description,
			'image'       => $seblod[ 'image' ]
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

	/**
	 * @param      $id
	 * @param null $attr
	 *
	 * @return array|bool
	 *
	 * @since 4.0
	 */
	private function seblod($id, $attr = null): bool|array
	{
		$article = $attr[ 'article' ];
		$context = $attr[ 'context' ];

		if($article->text && strpos($article->text, '::/cck::') !== false)
		{
			$lang           = $this->app->getLanguage();
			$multilang      = Multilanguage::isEnabled();
			$cck            = new SeblodAPI();
			$content        = $cck->loadContent($id)->properties;
			$cck_id         = $cck->loadContent($id)->id;
			$seblod_images  = $attr[ 'params' ]->get('seblod_images');
			$seblod_gallery = $attr[ 'params' ]->get('seblod_gallery');
			$seblod_intro   = $attr[ 'params' ]->get('seblod_intro');

			if($multilang === true)
			{
				$lang_tag     = $lang->getTag();
				$lang_code    = explode('-', $lang_tag)[ 0 ];
				$seblod_intro = str_replace('[lang]', $lang_code, $seblod_intro);
			}

			$data  = [];
			$_next = 1;

			if(isset($seblod_images))
			{
				$_rows = explode(',', $seblod_images);
				$image = [];
				foreach($_rows as $_row)
				{
					$_image = trim($_row);
					$_image = $content->{$_image};

					if($_image)
					{
						if($this->isJSON($_image))
						{
							$jsonimages = json_decode($_image);
							$image[]    = $jsonimages->image_location;
						}
						else
						{
							$image[] = $_image;
						}
					}
				}

				$data[ 'image' ] = implode($image);
				if($data[ 'image' ])
				{
					$_next = 0;
				}
			}

			if($seblod_gallery && $_next == 1)
			{
				$_rows = explode(',', $seblod_gallery);

				$image = [];
				foreach($_rows as $_row)
				{
					$_image = trim($_row);
					$_image = $content->{$_image};

					if($_image)
					{
						$fieldx  = explode('|0|', $_image);
						$fx      = explode('::', $fieldx[ 1 ]);
						$image[] = $fx[ 1 ];
					}
				}

				$data[ 'image' ] = ($image ? implode($image) : '');
			}

			if($seblod_intro)
			{
				$_rows = explode(',', $seblod_intro);

				$intro = [];
				foreach($_rows as $_row)
				{
					$_intro = trim($_row);
					$_intro = $content->{$_intro};
					if($_intro)
					{
						$intro[] = $_intro;
					}
				}

				$article->introtext = implode($intro);
			}

			$data[ 'title' ] = $article->title;
			$data[ 'desc' ]  = $article->metadesc;
			$data[ 'intro' ] = $article->introtext;

			if($multilang === true)
			{
				$cck          = $this->cck($cck_id);
				$property     = 'text';
				$descriptions = '';
				$titles       = '';

				if(!is_object($cck))
				{
					return true;
				}

				$contentType = (string) $cck->cck;
				$client      = $this->client($context, $cck, $article, $property);

				if(!isset($this->loaded[ $contentType . '_' . $client . '_options' ]))
				{
					$lang->load('pkg_app_cck_' . $cck->folder_app, JPATH_SITE, null, false, false);

					$registry = new JRegistry;
					$registry->loadString($cck->{'options_' . $client});
					$this->loaded[ $contentType . '_' . $client . '_options' ] = $registry->toArray();

					if(isset($this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ]) && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ] != '' && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ][ 0 ] == '{')
					{
						$descriptions                                                            = json_decode($this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ]);
						$lang_tag                                                                = $lang->getTag();
						$this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ] = $descriptions->$lang_tag ?? '';
					}

					if(isset($this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ]) && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ] != '' && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ][ 0 ] == '{')
					{
						$titles                                                               = json_decode($this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ]);
						$lang_tag                                                             = $lang->getTag();
						$this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ] = $titles->$lang_tag ?? '';
					}
				}

				if($titles)
				{
					$title           = $titles->{$lang_tag};
					$data[ 'title' ] = $content->{$title};
				}

				if($descriptions)
				{
					$desc           = $descriptions->{$lang_tag};
					$data[ 'desc' ] = $content->{$desc};
				}
			}

			if(strpos($data[ 'intro' ], '::/cck::'))
			{
				$data[ 'intro' ] = $data[ 'desc' ];
			}

			return $data;
		}

		return false;
	}

	/**
	 * @param $string
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	private function isJSON($string): bool
	{
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}

	/**
	 * @param $cck_id
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	private function cck($cck_id): mixed
	{
		$join        = ' LEFT JOIN #__cck_core_folders AS f ON f.id = b.folder';
		$join_select = ', f.app as folder_app';
		$query       = 'SELECT a.id, a.pk, a.pkb, a.cck, a.storage_location, a.store_id, a.author_id AS author, b.id AS type_id, b.indexed, b.parent, b.parent_inherit, b.stylesheets,' . ' b.options_content, b.options_intro, c.template AS content_template, c.params AS content_params, d.template AS intro_template, d.params AS intro_params' . $join_select . ' FROM #__cck_core AS a' . ' LEFT JOIN #__cck_core_types AS b ON b.name = a.cck' . ' LEFT JOIN #__template_styles AS c ON c.id = b.template_content' . ' LEFT JOIN #__template_styles AS d ON d.id = b.template_intro' . $join . ' WHERE a.id = "' . $cck_id . '"';

		return JCckDatabase::loadObject($query);
	}

	/**
	 * @param $context
	 * @param $cck
	 * @param $article
	 * @param $property
	 *
	 * @return bool|string
	 *
	 * @since 1.0
	 */
	private function client($context, $cck, $article, $property): bool|string
	{
		if($context === 'text')
		{
			$client = 'intro';
		}
		elseif($context === 'com_finder.indexer')
		{
			if($cck->indexed === 'none')
			{
				$article->$property = '';

				return true;
			}

			$client = (empty($cck->indexed)) ? 'intro' : $cck->indexed;
		}
		elseif($cck->storage_location != '')
		{
			$properties = [ 'contexts' ];
			$properties = JCck::callFunc('plgCCK_Storage_Location' . $cck->storage_location, 'getStaticProperties', $properties);
			$client     = (in_array($context, $properties[ 'contexts' ])) ? 'content' : 'intro';
		}
		else
		{
			$client = 'intro';
		}

		return $client;
	}
}
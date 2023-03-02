<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Joomla;

use JCck;
use JCckDatabase;
use JLanguageMultilang;
use Joomla\CMS\Factory;
use JRegistry;
use SeblodAPI;

require_once __DIR__ . '/classes/com_cck.php';

class com_cck
{
	/**
	 * @var array
	 */
	protected $loaded = [];

	/**
	 *
	 * @param   array  $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function run(array $option = []): array
	{
		$article = $option[ 'article' ];
		$context = $option[ 'context' ];

		if(strpos($article->text, '::/cck::'))
		{
			$app       = Factory::getApplication();
			$lang      = Factory::getLanguage();
			$multilang = JLanguageMultilang::isEnabled();

			$id      = $app->input->getInt('id');
			$cck     = new SeblodAPI();
			$content = $cck->loadContent($id)->properties;
			$cck_id  = $cck->loadContent($id)->id;

			$seblod_images  = $option[ 'params' ]->get('seblod_images');
			$seblod_gallery = $option[ 'params' ]->get('seblod_gallery');
			$seblod_video   = $option[ 'params' ]->get('seblod_video');
			$seblod_intro   = $option[ 'params' ]->get('seblod_intro');
			$seblod_full    = $option[ 'params' ]->get('seblod_full');

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
						if($option[ 'julib' ]->isJSON($_image))
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
					$data[ 'imgtxt' ] = 0;
					$_next            = 0;
				}
			}

			if($seblod_video && $_next == 1)
			{
				$_rows = explode(',', $seblod_video);
				$image = [];
				$yt    = [];

				foreach($_rows as $_row)
				{
					$_image = trim($_row);
					$_image = $content->{$_image};

					$yt[] = $content->{$seblod_video};

					if($_image)
					{
						$image[] = $option[ 'julib' ]->video($_image);
					}
				}

				$data[ 'image' ] = implode($image);

				if($data[ 'image' ])
				{
					$data[ 'imgtxt' ] = 0;
					$_next            = 0;
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

					$_next = 1;
					if($_image)
					{
						$fieldx  = explode('|0|', $_image);
						$fx      = explode('::', $fieldx[ 1 ]);
						$image[] = $fx[ 1 ];
					}
				}

				$data[ 'image' ] = ($image ? implode($image) : '');

				if($data[ 'image' ])
				{
					$data[ 'imgtxt' ] = 0;
					$_next            = 0;
				}
			}

			if($seblod_video)
			{
				$_rows = explode(',', $seblod_video);

				$yt = [];
				foreach($_rows as $_row)
				{
					$_yt = trim($_row);
					$_yt = $content->{$_yt};
					if($_yt)
					{
						$yt[] = $_yt;
					}
				}

				$data[ 'yt' ] = implode($yt);
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

				$intro_txt       = implode($intro);
				$data[ 'intro' ] = $intro_txt;
			}

			$full_txt = '';
			if($seblod_full)
			{
				$_rows = explode(',', $seblod_full);

				$full = [];
				foreach($_rows as $_row)
				{
					$_full = trim($_row);
					$_full = $content->{$_full};

					if($_full)
					{
						$full[] = $_full;
					}
				}

				$full_txt = implode($full);

				if($data[ 'yt' ] == '')
				{
					$data[ 'yt' ] = $full_txt;
				}
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

					if(isset($this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ]))
					{
						if($this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ] != '' && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ][ 0 ] == '{')
						{
							$descriptions                                                            = json_decode($this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ]);
							$lang_tag                                                                = Factory::getLanguage()->getTag();
							$this->loaded[ $contentType . '_' . $client . '_options' ][ 'metadesc' ] = (isset($descriptions->$lang_tag)) ? $descriptions->$lang_tag : '';
						}
					}

					if(isset($this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ]))
					{
						if($this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ] != '' && $this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ][ 0 ] == '{')
						{
							$titles                                                               = json_decode($this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ]);
							$lang_tag                                                             = Factory::getLanguage()->getTag();
							$this->loaded[ $contentType . '_' . $client . '_options' ][ 'title' ] = (isset($titles->$lang_tag)) ? $titles->$lang_tag : '';
						}
					}
				}

				if(isset($titles))
				{
					$title           = $titles->{$lang_tag};
					$data[ 'title' ] = $content->{$title};
				}

				if(isset($descriptions))
				{
					$desc           = $descriptions->{$lang_tag};
					$data[ 'desc' ] = $content->{$desc};
				}
			}

			if(strpos($data[ 'intro' ], '::/cck::'))
			{
				$data[ 'intro' ] = $data[ 'desc' ];
			}

			$data[ 'startdate' ] = $this->_field($content, $option[ 'params' ]->get('seblod_startdate'));
			$data[ 'enddate' ]   = $this->_field($content, $option[ 'params' ]->get('seblod_enddate'));
			$data[ 'place' ]     = $this->_field($content, $option[ 'params' ]->get('seblod_place'));
			$data[ 'address' ]   = $this->_field($content, $option[ 'params' ]->get('seblod_address'));
			$data[ 'city' ]      = $this->_field($content, $option[ 'params' ]->get('seblod_city'));
			$data[ 'region' ]    = $this->_field($content, $option[ 'params' ]->get('seblod_region'));
			$data[ 'country' ]   = $this->_field($content, $option[ 'params' ]->get('seblod_country'));
			$data[ 'zip' ]       = $this->_field($content, $option[ 'params' ]->get('seblod_zip'));
			$data[ 'price' ]     = $this->_field($content, $option[ 'params' ]->get('seblod_price'));
			$data[ 'currency' ]  = $this->_field($content, $option[ 'params' ]->get('seblod_currency'));
			$data[ 'performer' ] = $this->_field($content, $option[ 'params' ]->get('seblod_performer'));
			$data[ 'brand' ]     = $this->_field($content, $option[ 'params' ]->get('seblod_brand'));
			$data[ 'alltxt' ]    = $intro_txt . $full_txt;

			return $data;
		}

		return [];
	}

	/**
	 * @param $data
	 * @param $str
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	public function _field($data, $str)
	{
		$html = '';

		if($str)
		{
			$_rows = explode(',', $str);
			$html  = [];
			foreach($_rows as $_row)
			{
				$_str = trim($_row);
				$_str = $data->{$_str};
				if($_str)
				{
					$html[] = $_str;
				}
			}

			$html = implode($html);
		}

		return $html;
	}

	/**
	 * @param $cck_id
	 *
	 * @return mixed
	 */
	private function cck($cck_id)
	{
		$join        = ' LEFT JOIN #__cck_core_folders AS f ON f.id = b.folder';
		$join_select = ', f.app as folder_app';
		$query       = 'SELECT a.id, a.pk, a.pkb, a.cck, a.storage_location, a.store_id, a.author_id AS author, b.id AS type_id, b.alias AS type_alias, b.indexed, b.parent, b.parent_inherit, b.stylesheets,' . ' b.options_content, b.options_intro, c.template AS content_template, c.params AS content_params, d.template AS intro_template, d.params AS intro_params' . $join_select . ' FROM #__cck_core AS a' . ' LEFT JOIN #__cck_core_types AS b ON b.name = a.cck' . ' LEFT JOIN #__template_styles AS c ON c.id = b.template_content' . ' LEFT JOIN #__template_styles AS d ON d.id = b.template_intro' . $join . ' WHERE a.id = "' . $cck_id . '"';

		return JCckDatabase::loadObject($query);
	}

	/**
	 * @param $context
	 * @param $cck
	 * @param $article
	 * @param $property
	 *
	 * @return bool|string
	 */
	private function client($context, $cck, $article, $property)
	{
		if($context == 'text')
		{
			$client = 'intro';
		}
		elseif($context == 'com_finder.indexer')
		{
			if($cck->indexed == 'none')
			{
				$article->$property = '';

				return true;
			}

			$client = (empty($cck->indexed)) ? 'intro' : $cck->indexed;
		}
		else
		{
			if($cck->storage_location != '')
			{
				$properties = [ 'contexts' ];
				$properties = JCck::callFunc('plgCCK_Storage_Location' . $cck->storage_location, 'getStaticProperties', $properties);
				$client     = (in_array($context, $properties[ 'contexts' ])) ? 'content' : 'intro';
			}
			else
			{
				$client = 'intro';
			}
		}

		return $client;
	}
}
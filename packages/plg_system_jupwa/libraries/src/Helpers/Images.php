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

use DOMDocument;
use FastImageSize\FastImageSize;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use JUPWA\Utils\Util;

class Images
{
	/**
	 * @param array $option
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function image_storage(array $option = []): string
	{
		if(self::is_gallery($option[ 'text' ]))
		{
			$image = self::gallery($option[ 'text' ]);
		}
		elseif($_image = self::article($option[ 'article' ]->images))
		{
			$image = $_image;
		}
		elseif(self::is_html($option[ 'text' ]) === true)
		{
			$image = self::html($option[ 'text' ]);
		}
		elseif(self::is_YouTube($option[ 'alltxt' ]) === true)
		{
			$image = self::YouTube($option[ 'alltxt' ]);
		}
		else
		{
			$image = self::display_default($option[ 'params' ]->get('selectimg'), $option[ 'params' ]->get('image'), $option[ 'params' ]->get('imagemain'));
		}

		return $image;
	}

	/**
	 * @param $image
	 *
	 * @return object
	 *
	 * @since 1.0
	 */
	public static function display($image): object
	{
		$width  = 0;
		$height = 0;
		$local  = true;

		if(URL::is_url($image) === true)
		{
			$domain     = parse_url(Uri::base(), PHP_URL_HOST);
			$img_domain = parse_url($image, PHP_URL_HOST);

			$local = false;
			if($domain === $img_domain)
			{
				$local = true;
				$image = str_replace(Uri::base(), '', $image);
			}
		}

		if($local === true && $image)
		{
			$FastImageSize = new FastImageSize();
			$image         = ltrim($image, '/');
			$imageSize     = $FastImageSize->getImageSize(JPATH_SITE . '/' . $image);

			if($imageSize !== false)
			{
				$width  = $imageSize[ 'width' ];
				$height = $imageSize[ 'height' ];
			}
		}

		return (object) [
			'image'  => Uri::base() . $image,
			'width'  => $width,
			'height' => $height,
		];
	}

	/**
	 * @param $selectimg
	 * @param $img
	 * @param $imgmain
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function display_default($selectimg, $img, $imgmain): string
	{
		$img     = HTMLHelper::cleanImageURL($img)->url;
		$imgmain = HTMLHelper::cleanImageURL($imgmain)->url;
		$image   = Uri::base() . 'favicons/og_cover.png';

		if($selectimg == 1)
		{
			$rand_img = self::random();
			if($rand_img !== '')
			{
				$image = Uri::base() . $rand_img;
			}
		}

		if($selectimg == 0 && ($img || $imgmain))
		{
			if(isset($img) && is_file(JPATH_SITE . '/' . $img))
			{
				$image = $img;
			}

			if(isset($imgmain) && is_file(JPATH_SITE . '/' . $imgmain))
			{
				$image = HTMLHelper::cleanImageURL($imgmain)->url;
			}
		}

		return $image;
	}

	/**
	 * @param $text
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	private static function is_gallery($text): bool
	{
		return strpos($text, '{gallery') !== false;
	}

	/**
	 * @param $text
	 *
	 * @return mixed
	 *
	 * @since 1.0
	 */
	private static function gallery($text): mixed
	{
		if(strpos($text, '{gallery') === false)
		{
			return '';
		}

		if(preg_match('/{gallery\s+(.*?)}/i', $text, $imgsource))
		{
			$folder_match = $imgsource[ 1 ];
			$imglist      = explode('|', $folder_match);
			$imgsource    = $imglist[ 0 ];
			$root         = JPATH_BASE . '/';
			$folder       = 'images/' . $imgsource;
			$img_folder   = $root . $folder;
			$galleries    = glob($img_folder . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

			if(count($galleries) > 0 && is_dir($img_folder))
			{
				$i    = 0;
				$html = [];
				natcasesort($galleries);
				foreach($galleries as $gallery)
				{
					if($i > 0)
					{
						break;
					}

					$html[] = str_replace(JPATH_BASE . '/', '', $gallery);
					$i++;
				}

				return $html[ 0 ];
			}
		}

		return '';
	}

	/**
	 * @param $jsonimages
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private static function article($jsonimages): string
	{
		$html   = '';
		$images = json_decode($jsonimages);

		if(isset($images))
		{
			$image_intro = ($images->image_intro ?? '');
			$image_full  = ($images->image_fulltext ?? '');

			$_intro = '';
			if(!empty($image_intro))
			{
				$_intro = $image_intro;
			}

			$_full = '';
			if(!empty($image_full))
			{
				$_full = $image_full;
			}

			$html .= ($_intro == '' ? $_full : $_intro);
		}

		return $html;
	}

	/**
	 * @param $text
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	private static function is_html($text): bool
	{
		return strpos($text, '<img') !== false;
	}

	/**
	 * @param $text
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private static function html($text): string
	{
		$dom            = new DOMDocument();
		$internalErrors = libxml_use_internal_errors(true);

		$dom->loadHTML($text);
		libxml_use_internal_errors($internalErrors);

		$images = $dom->getElementsByTagName('img');

		$i = 0;
		foreach($images as $image)
		{
			if($i > 0)
			{
				break;
			}

			$src = $image->getAttribute('src');

			$i++;
		}

		return $src;
	}

	/**
	 * @param $text
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	private static function is_YouTube($text): bool
	{
		return strpos($text, 'youtube.com') !== false;
	}

	/**
	 * @param $text
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	private static function YouTube($text): string
	{
		$youtube = str_replace([
			'//www.youtube.com',
			'//youtube.com',
			'https://www.youtube.com',
			'https://youtube.com',
			'http://www.youtube.com',
			'http://youtube.com'
		], 'https://www.youtube.com', $text);

		$image = '';
		if(preg_match_all('#(youtube.com)/embed/([0-9A-Za-z-_]+)#i', $youtube, $match))
		{
			$image = Util::HTTP('https://' . $match[ 0 ][ 0 ]);
		}

		return $image;
	}

	/**
	 * @return string
	 *
	 * @since 1.0
	 */
	private static function random(): string
	{
		$folder = '/images/jupwa/images';
		$images = Folders::files($folder);

		$html = '';
		if($images)
		{
			$html = $images[ array_rand($images) ];
		}

		return $html;
	}
}
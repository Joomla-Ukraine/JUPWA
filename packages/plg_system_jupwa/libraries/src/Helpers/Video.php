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

namespace JUPWA\Helpers;

use JUPWA\Utils\Util;

class Video
{
	/**
	 * @param         $article
	 *
	 * @param   bool  $scheme
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function YouTube($article, bool $scheme = true): string
	{
		$youtube = str_replace([
			'//www.youtube.com',
			'//youtube.com',
			'https://www.youtube.com',
			'https://youtube.com',
			'http://www.youtube.com',
			'http://youtube.com'
		], 'https://www.youtube.com', $article->text);

		$url = '';
		if(preg_match_all('#(youtube.com)/embed/([0-9A-Za-z-_]+)#i', $youtube, $match))
		{
			$url = ($scheme === true ? 'https://' : '') . $match[ 0 ][ 0 ];
		}

		return $url;
	}

	/**
	 * @param $url
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function parse($url): bool|string
	{
		$urls = parse_url($url);
		$yid  = '';
		$vid  = '';

		if($urls[ 'host' ] === 'vimeo.com')
		{
			$vid = ltrim($urls[ 'path' ], '/');
		}
		elseif($urls[ 'host' ] === 'youtu.be')
		{
			$yid = ltrim($urls[ 'path' ], '/');
		}
		elseif(strpos($urls[ 'path' ], 'embed') == 1)
		{
			$path = explode('/', $urls[ 'path' ]);
			$yid  = end($path);
		}
		elseif(strpos($url, '/') === false)
		{
			$yid = $url;
		}
		else
		{
			$feature = '';

			parse_str($urls[ 'query' ], $output);
			$yid = $output[ 'v' ];

			if(!empty($feature))
			{
				$query = explode('v=', $urls[ 'query' ]);
				$yid   = end($query);
				$arr   = explode('&', $yid);
				$yid   = $arr[ 0 ];
			}
		}

		if($yid)
		{
			$ytpath = 'https://img.youtube.com/vi/' . $yid;
			$img    = $ytpath . '/default.jpg';
			if(Util::HTTP($ytpath . '/maxresdefault.jpg') == '200')
			{
				$img = $ytpath . '/maxresdefault.jpg';
			}
			elseif(Util::HTTP($ytpath . '/hqdefault.jpg') == '200')
			{
				$img = $ytpath . '/hqdefault.jpg';
			}
			elseif(Util::HTTP($ytpath . '/mqdefault.jpg') == '200')
			{
				$img = $ytpath . '/mqdefault.jpg';
			}

			return $img;
		}

		if($vid)
		{
			$vimeoObject = json_decode(file_get_contents('https://vimeo.com/api/v2/video/' . $vid . '.json'));

			if(!empty($vimeoObject))
			{
				return $vimeoObject[ 0 ]->thumbnail_large;
			}
		}

		return true;
	}
}
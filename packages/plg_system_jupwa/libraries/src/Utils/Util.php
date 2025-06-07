<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Utils
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Utils;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;

class Util
{
	/**
	 * @param          $name
	 * @param array    $variables
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function tmpl($name, array $variables = []): string
	{
		$template = Factory::getApplication()->getTemplate();
		$search   = JPATH_SITE . '/templates/' . $template . '/html/jupwa/';
		$tmpl     = JPATH_SITE . '/plugins/system/jupwa/tmpl/';
		$filename = $search . '/' . $name . '.php';

		if(file_exists($filename))
		{
			return (new FileLayout($name, $search))->render($variables);
		}

		return (new FileLayout($name, $tmpl))->render($variables);
	}

	/**
	 * @param array $json
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function LD(array $json = []): string
	{
		return '<script type="application/ld+json">' . json_encode(array_filter($json)) . '</script>';
	}

	/**
	 * @return mixed
	 *
	 * @since 1.0
	 */
	public static function get_thumbs(): mixed
	{
		$json = JPATH_SITE . '/favicons/thumbs.json';
		if(file_exists($json))
		{
			$json = file_get_contents($json);

			return json_decode($json);
		}

		return '';
	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 *
	 * @since 1.0
	 */
	public static function HTTP($url): bool|string
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		$header = curl_exec($ch);

		return substr($header, 9, 3);
	}
}
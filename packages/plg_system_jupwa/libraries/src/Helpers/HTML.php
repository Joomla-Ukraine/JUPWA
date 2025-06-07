<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use JUPWA\Classes\Minify;

class HTML
{
	/**
	 * @param string $text
	 *
	 * @return string|null
	 *
	 * @since 1.0
	 */
	public static function text(string $text): ?string
	{
		$text = rtrim($text, ' ');
		$text = trim($text);

		return str_replace([
			"\n",
			"\r",
			"\t",
			"\n\r"
		], '', $text);
	}

	/**
	 * @param string|string[]|null $html
	 *
	 * @return mixed|string|string[]|null
	 *
	 * @since 1.0
	 */
	public static function html($html): mixed
	{
		if($html)
		{
			$html = self::clean($html);
			$html = preg_replace('/<a(.*?)>(.*?)<\/a>/is', '\\2', $html);
			$html = preg_replace('/<iframe.*?>(.*?)<\/iframe>/is', '', $html);
			$html = preg_replace('#<p(.*)>"#is', '<p>', $html);

			if(preg_match('#<p[^>]*>(.*)<\/p>#isU', $html, $matches))
			{
				$html = $matches[ 0 ];
			}
		}

		return $html;
	}

	/**
	 * @param string $html
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function clean(string $html): string
	{
		$html = preg_replace('/<script.+?<\/script>/is', '', $html);
		$html = preg_replace('/<script.*?>(.*?)<\/script>/is', '', $html);
		$html = preg_replace('/<style.*?>(.*?)<\/style>/is', '', $html);
		$html = preg_replace('/<noscript>.*?<\/noscript>/is', '', $html);
		$html = preg_replace('/\sonload\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/\sonclick\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/\sondblclick\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/\sonchange\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/\sonmouseover\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/\sonmouseout\s*=\s*[^\s>]*/i', '', $html);
		$html = preg_replace('/<p>\s*{([a-zA-Z0-9\-_]*)\s*(.*?)}\s*<\/p>/i', '', $html);
		$html = preg_replace('/{([a-zA-Z0-9\-_]*)\s*(.*?)}/i', '', $html);
		$html = preg_replace('/\[(.*?)\s?.*?\].*?\[\/(.*?)\]/i', '', $html);
		$html = preg_replace('/::cck::(.*?)::\/cck::/i', '', $html);
		$html = preg_replace('/::introtext::(.*?)::\/introtext::/i', '\\1', $html);

		return preg_replace('/::fulltext::(.*?)::\/fulltext::/i', '\\2', $html);
	}

	/**
	 * @param array $matches
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function tag_html(array $matches = []): string
	{
		$buffer = $matches[ 1 ];
		if(preg_match('#xml:lang=".*?"#is', $buffer))
		{
			$buffer = preg_replace('#xml:lang=".*?"#is', '', $buffer);
		}

		if(preg_match('#xmlns:fb="http://www\.facebook\.com/2008/fbml"#i', $buffer))
		{
			$buffer = preg_replace('#xmlns:fb="http://www\.facebook\.com/2008/fbml"#i', '', $buffer);
		}

		if(preg_match('#prefix="fb: http://www\.facebook\.com/2008/fbml"#i', $buffer))
		{
			$buffer = preg_replace('#prefix="fb: http://www\.facebook\.com/2008/fbml"#i', '', $buffer);
		}

		if(preg_match('#xmlns:og="http://opengraphprotocol\.org/schema/"#i', $buffer))
		{
			$buffer = preg_replace('#xmlns:og="http://opengraphprotocol\.org/schema/"#i', '', $buffer);
		}

		if(preg_match('#prefix="og: http://opengraphprotocol\.org/schema/"#i', $buffer))
		{
			$buffer = preg_replace('#prefix="og: http://opengraphprotocol\.org/schema/"#i', '', $buffer);
		}

		if(preg_match('#prefix="og: http://ogp\.me/ns\#"#i', $buffer))
		{
			$buffer = preg_replace('#prefix="og: http://ogp\.me/ns\#"#i', '', $buffer);
		}

		$buffer = str_replace($buffer, $buffer . ' prefix="og: https://ogp.me/ns# fb: https:///www.facebook.com/2008/fbml og: https://opengraphprotocol.org/schema/"', $buffer);

		return '<html' . $buffer . '>';
	}

	/**
	 * @param string $html
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function compress(string $html): string
	{
		preg_match_all('!(<(?:code|pre|textarea|script).*?>.*?</(?:code|pre|textarea|script)>)!si', $html, $pre);
		$html = preg_replace('!<(?:code|pre|textarea|script).*?>.*?</(?:code|pre|textarea|script)>!si', '#pre#', $html);

		$html = Minify::minify($html, [
			'jsMinifier' => [
				'jsCleanComments',
				'minify'
			],
		]);

		$html = preg_replace('/[\r\n\t]+/', ' ', $html);

		if(!empty($pre[ 0 ]))
		{
			foreach($pre[ 0 ] as $tag)
			{
				$html = preg_replace('!#pre#!', $tag, $html, 1);
				$html = preg_replace('#</script>\s*#', '</script>', $html);
				$html = preg_replace('#\s*<script#', '<script', $html);
			}
		}

		return $html;
	}
}
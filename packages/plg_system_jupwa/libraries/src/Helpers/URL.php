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

use Joomla\CMS\Uri\Uri;

class URL
{
	/**
	 * @param string $url
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public static function is_url($url): bool
	{
		$html = false;
		if(filter_var($url, FILTER_VALIDATE_URL))
		{
			$html = true;
		}

		return $html;
	}

	/**
	 * @param $html
	 *
	 * @return string|string[]|null
	 *
	 * @since 1.0
	 */
	public static function absolute($html): array|string|null
	{
		$root_url = Uri::base();

		$html = str_replace([
			'href="//',
			'src="//'
		], [
			'href="https://',
			'src="https://'
		], $html);

		$html = preg_replace('@href="(?!http://)(?!https://)(?!mailto:)([^"]+)"@i', "href=\"$root_url\${1}\"", $html);

		return preg_replace('@src="(?!http://)(?!https://)([^"]+)"@i', "src=\"$root_url\${1}\"", $html);
	}
}
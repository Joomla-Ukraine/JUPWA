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

use DOMDocument;
use Joomla\Filesystem\File;

class BrowserConfig
{
	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function create(array $option = []): void
	{
		$favicons = JPATH_SITE . '/favicons/';
		$file     = $favicons . '/browserconfig.xml';
		$icon150  = file_exists($favicons . '/icon_150.png');
		$icon310  = file_exists($favicons . '/icon_310.png');

		if($icon150 || $icon310)
		{
			$xml = new DOMDocument("1.0", "UTF-8");
			$xml->normalizeDocument();
			$xml->formatOutput = true;

			$browserconfig = $xml->createElement("browserconfig");
			$node          = $xml->appendChild($browserconfig);

			$msapplication      = $xml->createElement("msapplication");
			$msapplication_node = $node->appendChild($msapplication);

			$tile      = $xml->createElement("tile");
			$tile_node = $msapplication_node->appendChild($tile);

			if($icon150)
			{
				$square150      = $xml->createElement("square150x150logo");
				$square150_node = $tile_node->appendChild($square150);
				$square150_node->setAttribute("src", "/favicons/icon_150.png");
			}

			if($icon310)
			{
				$square310      = $xml->createElement("square310x310logo");
				$square310_node = $tile_node->appendChild($square310);
				$square310_node->setAttribute("src", "/favicons/icon_310.png");
			}

			$tile_node->appendChild($xml->createElement("TileColor", $option[ 'msapplication_tilecolor' ]));

			$data = $xml->saveXML();

			File::write($file, $data);
		}
	}
}
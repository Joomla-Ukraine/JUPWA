<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Data
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Data;

class Data
{
	public static $icons_sm = [
		16,
		32,
		48
	];

	public static $icons = [
		72,
		76,
		96,
		120,
		144,
		152,
		150,
		168,
		192,
		310,
		512
	];

	public static $splash = [
		[ 640, 1136 ],
		[ 750, 1294 ],
		[ 1125, 2436 ],
		[ 1242, 2148 ],
		[ 1536, 2048 ],
		[ 1668, 2224 ],
		[ 1668, 2388 ],
		[ 2048, 2732 ]
	];

	public static $manifest = [
		'name'             => '',
		'short_name'       => '',
		'start_url'        => '',
		'display'          => '',
		'description'      => '',
		'scope'            => '',
		'orientation'      => '',
		'features'         => [],
		'screenshots'      => [],
		'icons'            => [
			[
				'src'     => '',
				'sizes'   => '192x192',
				'type'    => 'image/png',
				'purpose' => 'any'
			],
			[
				'src'     => '',
				'sizes'   => '192x192',
				'type'    => 'image/png',
				'purpose' => 'maskable'
			],
			[
				'src'     => '',
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'any'
			],
			[
				'src'     => '',
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'maskable'
			]
		],
		'shortcuts'        => [],
		'categories'       => [],
		'gcm_sender_id'    => '482941778795',
		'theme_color'      => '',
		'background_color' => '',
	];
}
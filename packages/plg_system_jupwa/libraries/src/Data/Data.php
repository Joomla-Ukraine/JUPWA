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
	public static $splash = [
		[ 2048, 2732 ],
		[ 1668, 2388 ],
		[ 1668, 2224 ],
		[ 1536, 2048 ],
		[ 1620, 2160 ],
		[ 1290, 2796 ]
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
				'src'     => 'icon-192.png',
				'sizes'   => '192x192',
				'type'    => 'image/png',
				'purpose' => 'any'
			],
			[
				'src'     => 'icon-192.png',
				'sizes'   => '192x192',
				'type'    => 'image/png',
				'purpose' => 'maskable'
			],
			[
				'src'     => 'icon-512.png',
				'sizes'   => '512x512',
				'type'    => 'image/png',
				'purpose' => 'any'
			],
			[
				'src'     => 'icon-512.png',
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
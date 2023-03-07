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
		[ 1136, 640 ],
		[ 2260, 1488 ],
		[ 2048, 1536 ],
		[ 2160, 1620 ],
		[ 2224, 1668 ],
		[ 2360, 1640 ],
		[ 2380, 1668 ],
		[ 2732, 2048 ],
		[ 1337, 750 ],
		[ 2208, 1242 ],
		[ 1792, 828 ],
		[ 2688, 1242 ],
		[ 2436, 1125 ],
		[ 2532, 1170 ],
		[ 2778, 1284 ],
		[ 2556, 1179 ],
		[ 2796, 1290 ],
		[ 1179, 2556 ]
	];

	public static $manifest_icons = [
		192,
		512
	];

	public static $manifest = [
		'name'                  => '',
		'short_name'            => '',
		'start_url'             => '',
		'display'               => '',
		'description'           => '',
		'scope'                 => '',
		'orientation'           => '',
		'features'              => [],
		'screenshots'           => [],
		'icons'                 => [],
		'shortcuts'             => [],
		'categories'            => [],
		'gcm_sender_id'         => '482941778795',
		'gcm_user_visible_only' => true,
		'theme_color'           => '',
		'background_color'      => '',
		'author'                => [
			'name'    => 'Denys Nosov',
			'website' => 'https://joomla-ua.org',
			'github'  => 'https://github.com/Joomla-Ukraine/JUPWA'
		]
	];
}
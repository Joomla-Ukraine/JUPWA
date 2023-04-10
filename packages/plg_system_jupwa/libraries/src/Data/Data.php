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
		48,
		72,
		76,
		96,
		120,
		144,
		152,
		150,
		168,
		180
	];

	public static $icons = [
		192,
		310,
		512
	];

	public static $favicons = [
		'apple-touch-icon' => [ 180 ],
		'icon'             => [ 16, 32, 192 ]
	];

	public static $splash = [
		[
			'width'       => 640,
			'height'      => 1136,
			'd_width'     => 320,
			'd_height'    => 568,
			'orientation' => 'portrait'
		],
		[
			'width'       => 1136,
			'height'      => 640,
			'd_width'     => 320,
			'd_height'    => 568,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1488,
			'height'      => 2266,
			'd_width'     => 744,
			'd_height'    => 1133,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2266,
			'height'      => 1488,
			'd_width'     => 744,
			'd_height'    => 1133,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1536,
			'height'      => 2048,
			'd_width'     => 768,
			'd_height'    => 1024,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2048,
			'height'      => 1536,
			'd_width'     => 768,
			'd_height'    => 1024,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1620,
			'height'      => 2160,
			'd_width'     => 810,
			'd_height'    => 1080,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2160,
			'height'      => 1620,
			'd_width'     => 810,
			'd_height'    => 1080,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1640,
			'height'      => 2360,
			'd_width'     => 820,
			'd_height'    => 1180,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2360,
			'height'      => 1640,
			'd_width'     => 820,
			'd_height'    => 1180,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1668,
			'height'      => 2388,
			'd_width'     => 834,
			'd_height'    => 1194,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2388,
			'height'      => 1668,
			'd_width'     => 834,
			'd_height'    => 1194,
			'orientation' => 'landscape'
		],

		[
			'width'       => 2048,
			'height'      => 2732,
			'd_width'     => 1024,
			'd_height'    => 1366,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2732,
			'height'      => 2048,
			'd_width'     => 1024,
			'd_height'    => 1366,
			'orientation' => 'landscape'
		],

		[
			'width'       => 750,
			'height'      => 1334,
			'd_width'     => 375,
			'd_height'    => 667,
			'orientation' => 'portrait'
		],
		[
			'width'       => 1334,
			'height'      => 750,
			'd_width'     => 375,
			'd_height'    => 667,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1242,
			'height'      => 2208,
			'd_width'     => 414,
			'd_height'    => 736,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2208,
			'height'      => 1242,
			'd_width'     => 414,
			'd_height'    => 736,
			'orientation' => 'landscape'
		],

		[
			'width'       => 828,
			'height'      => 1792,
			'd_width'     => 414,
			'd_height'    => 896,
			'orientation' => 'portrait'
		],
		[
			'width'       => 1792,
			'height'      => 828,
			'd_width'     => 414,
			'd_height'    => 896,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1242,
			'height'      => 2688,
			'd_width'     => 414,
			'd_height'    => 896,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2688,
			'height'      => 1242,
			'd_width'     => 414,
			'd_height'    => 896,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1125,
			'height'      => 2436,
			'd_width'     => 375,
			'd_height'    => 812,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2436,
			'height'      => 1125,
			'd_width'     => 375,
			'd_height'    => 812,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1170,
			'height'      => 2532,
			'd_width'     => 390,
			'd_height'    => 844,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2532,
			'height'      => 1170,
			'd_width'     => 390,
			'd_height'    => 844,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1284,
			'height'      => 2778,
			'd_width'     => 428,
			'd_height'    => 926,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2778,
			'height'      => 1284,
			'd_width'     => 428,
			'd_height'    => 926,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1179,
			'height'      => 2556,
			'd_width'     => 393,
			'd_height'    => 852,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2556,
			'height'      => 1179,
			'd_width'     => 393,
			'd_height'    => 852,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1290,
			'height'      => 2796,
			'd_width'     => 430,
			'd_height'    => 932,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2796,
			'height'      => 1290,
			'd_width'     => 430,
			'd_height'    => 932,
			'orientation' => 'landscape'
		],

		[
			'width'       => 1179,
			'height'      => 2556,
			'd_width'     => 430,
			'd_height'    => 932,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2556,
			'height'      => 1179,
			'd_width'     => 393,
			'd_height'    => 852,
			'orientation' => 'landscape'
		]
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

	public static $workbox = 'https://storage.googleapis.com/workbox-cdn/releases/6.5.4/workbox-sw.js';

	public static $preconnect = [
		'google'           => [ 'https://www.google.com' ],
		'google-analytics' => [
			'https://www.google.com',
			'https://www.google-analytics.com',
			'https://www.googletagmanager.com'
		],
		'google-fonts'     => [
			'https://www.google.com',
			'https://fonts.googleapis.com'
		],
		'google-ads'       => [
			'https://www.google.com',
			'https://pagead2.googlesyndication.com',
			'https://googleads.g.doubleclick.net',
			'https://tpc.googlesyndication.com',
			'https://adservice.google.com',
			'https://partner.googleadservices.com',
			'https://fonts.googleapis.com'
		],
		'google-cse'       => [
			'https://www.google.com',
			'https://cse.google.com',
			'https://ssl.gstatic.com',
			'https://clients1.google.com',
			'https://www.googleapis.com',
		],
		'google-maps'      => [
			'https://maps.gstatic.com',
			'https://maps.googleapis.com',
			'https://fonts.gstatic.com',
			'https://fonts.googleapis.com',
		],
		'cloudflare'       => [ 'https://cdnjs.cloudflare.com' ],
		'youtube'          => [
			'https://www.youtube.com',
			'https://i.ytimg.com',
			'https://s.ytimg.com',
			'https://yt3.ggpht.com',
			'https://fonts.gstatic.com',
		],
		'facebook'         => [ 'https://graph.facebook.com' ],
		'twitter'          => [ 'https://dn.api.twitter.com' ],
	];
}
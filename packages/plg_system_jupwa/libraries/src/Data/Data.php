<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Data
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Data;

class Data
{
	public static array $icons_sm = [
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

	public static array $icons = [
		192,
		310,
		512
	];

	public static array $favicons = [
		'apple-touch-icon' => [ 180 ],
		'icon'             => [ 16, 32, 192 ]
	];

	public static array $splash = [
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
			'width'       => 1668,
			'height'      => 2224,
			'd_width'     => 834,
			'd_height'    => 1194,
			'orientation' => 'portrait'
		],
		[
			'width'       => 2224,
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
		]
	];

	public static array $manifest_icons = [
		192,
		384,
		512,
		1024
	];

	public static array $manifest = [
		'name'                        => '',
		'short_name'                  => '',
		'start_url'                   => '',
		'id'                          => '',
		'display'                     => '',
		'display_override'            => [],
		'description'                 => '',
		'lang'                        => '',
		'dir'                         => '',
		'scope'                       => '',
		'orientation'                 => '',
		'icons'                       => [],
		'shortcuts'                   => [],
		'categories'                  => [],
		'theme_color'                 => '',
		'background_color'            => '',
		'screenshots'                 => [],
		'gcm_sender_id'               => '482941778795',
		'gcm_user_visible_only'       => true,
		'prefer_related_applications' => '',
		'related_applications'        => [],
		'handle_links'                => '',
		'launch_handler'              => [],
		'scope_extensions'            => [],
		'edge_side_panel'             => [],
		'author'                      => [
			'name'    => 'Joomla! Ukraine',
			'website' => 'https://joomla-ua.org',
			'github'  => 'https://github.com/Joomla-Ukraine/JUPWA'
		]
	];

	public static array $assetlinks = [
		'relation' => [ 'delegate_permission/common.query_webapk' ],
		'target'   => [
			'namespace' => 'web',
			'site'      => ''
		]
	];

	public static string $workbox = 'https://storage.googleapis.com/workbox-cdn/releases/7.0.0/workbox-sw.js';

	public static array $preconnect = [
		'google'            => [ 'https://www.google.com' ],
		'google-analytics'  => [
			'https://www.google-analytics.com',
			'https://www.googletagmanager.com'
		],
		'google-fonts'      => [
			'https://fonts.googleapis.com'
		],
		'google-ads'        => [
			'https://www.google.com',
			'https://pagead2.googlesyndication.com',
			'https://googleads.g.doubleclick.net',
			'https://tpc.googlesyndication.com',
			'https://adservice.google.com',
			'https://partner.googleadservices.com',
			'https://fonts.googleapis.com'
		],
		'google-cse'        => [
			'https://www.google.com',
			'https://cse.google.com',
			'https://ssl.gstatic.com',
			'https://clients1.google.com',
			'https://www.googleapis.com',
		],
		'google-maps'       => [
			'https://maps.gstatic.com',
			'https://maps.googleapis.com',
			'https://fonts.gstatic.com',
			'https://fonts.googleapis.com',
		],
		'cloudflare'        => [ 'https://cdnjs.cloudflare.com' ],
		'cloudflare-static' => [ 'https://static.cloudflareinsights.com' ],
		'youtube'           => [
			'https://www.youtube.com',
			'https://i.ytimg.com',
			'https://s.ytimg.com',
			'https://yt3.ggpht.com',
			'https://fonts.gstatic.com',
			'https://play.google.com',
			'https://jnn-pa.googleapis.com',
		],
		'facebook'          => [ 'https://graph.facebook.com' ],
		'twitter'           => [ 'https://platform.twitter.com' ],
	];
}
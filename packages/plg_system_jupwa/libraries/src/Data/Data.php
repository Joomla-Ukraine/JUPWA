<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Data
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
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
		96,
		120,
		144,
		152,
		150,
		168,
		180,
		192,
		512
	];

	public static array $favicons = [
		'apple-touch-icon' => [ 180, 192 ],
		'icon'             => [ 16, 32, 96, 192 ]
	];

	public static array $manifest_icons = [
		180,
		192,
		512
	];

	public static array $splash_icons = [
		512
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
		'prefer_related_applications' => '',
		'related_applications'        => [],
		'handle_links'                => '',
		'launch_handler'              => [],
		'scope_extensions'            => [],
		'edge_side_panel'             => []
	];

	public static array $assetlinks = [];

	public static string $workbox = 'https://storage.googleapis.com/workbox-cdn/releases/7.3.0/workbox-sw.js';

	public static string $firebase_app = 'https://www.gstatic.com/firebasejs/12.5.0/firebase-app-compat.js';
	public static string $firebase_messaging = 'https://www.gstatic.com/firebasejs/12.5.0/firebase-messaging-compat.js';

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
			'https://www.google.com',
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
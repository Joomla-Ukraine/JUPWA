<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 * @formatter:off
 **/

defined('_JEXEC') or die('Restricted access');

/** @var array $displayData */
$data = (object) $displayData;

?>
importScripts('<?php echo $data->workbox; ?>');

workbox.setConfig({
	debug: false
});

const {registerRoute, setCatchHandler} = workbox.routing;
const {NetworkFirst, StaleWhileRevalidate, CacheFirst} = workbox.strategies;
const {CacheableResponsePlugin} = workbox.cacheableResponse;
const {ExpirationPlugin} = workbox.expiration;
const {precacheAndRoute, matchPrecache} = workbox.precaching;

const versionPrecache = '<?php echo $data->pwa_version; ?>';

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', () => self.clients.claim());

// Offline
setCatchHandler(async ({event}) => {
	if (event.request.destination === 'document') {
		return new matchPrecache('/offline.php');
	}

    return new Response.error();
});

// files
precacheAndRoute([
<?php
$i = 0;
$numItems = count($data->pwa_data);
foreach($data->pwa_data as $data) {
	echo "  {'revision': versionPrecache, 'url': '$data'}";
	echo (++$i !== $numItems ? ',':'') ."\n";
}
?>
]);

// Cache assets
registerRoute(
	({request}) =>
		request.destination === 'style' ||
		request.destination === 'script' ||
		request.destination === 'font',
	new StaleWhileRevalidate({
		cacheName: 'assets',
		plugins: [
			new CacheableResponsePlugin({
				statuses: [0, 200]
			})
		]
	})
);

// Cache images
registerRoute(
	({request}) => request.destination === 'image',
	new CacheFirst({
		cacheName: 'images',
		plugins: [
			new CacheableResponsePlugin({
				statuses: [0, 200],
			}),
			new ExpirationPlugin({
				maxEntries: 100,
				maxAgeSeconds: 30 * 24 * 60 * 60,
				purgeOnQuotaError: true
			})
		]
	})
);

// Cache pages
registerRoute(
	({request}) => request.mode === 'navigate',
	new NetworkFirst({
		cacheName: 'pages',
		plugins: [
			new CacheableResponsePlugin({
				statuses: [0, 200],
			}),
			new ExpirationPlugin({
				maxAgeSeconds: 30 * 24 * 60 * 60,
				purgeOnQuotaError: true
			})
		]
	})
);

precacheAndRoute([]);
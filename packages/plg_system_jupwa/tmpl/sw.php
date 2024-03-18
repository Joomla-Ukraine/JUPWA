<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 * @formatter:off
 **/

defined('_JEXEC') or die('Restricted access');

/** @var array $displayData */
$data = (object) $displayData;

?>
const CACHE = 'jupwa-pages';
const offlineFallbackPage = "/offline.php";

importScripts('<?php echo $data->workbox; ?>');

const {registerRoute, setCatchHandler} = workbox.routing;
const {NetworkFirst, StaleWhileRevalidate, CacheFirst} = workbox.strategies;
const {CacheableResponsePlugin} = workbox.cacheableResponse;
const {ExpirationPlugin} = workbox.expiration;
const {precacheAndRoute, matchPrecache} = workbox.precaching;

self.addEventListener("message", (event) => {
	if (event.data && event.data.type === "SKIP_WAITING") {
		self.skipWaiting();
	}
});

// Offline
self.addEventListener('activate', () => self.clients.claim());

self.addEventListener('install', async (event) => {
	event.waitUntil(
		caches.open(CACHE)
		.then((cache) => cache.add(offlineFallbackPage))
	);
});

setCatchHandler(async ({event}) => {
	if (event.request.destination === 'document') {
		return new matchPrecache(offlineFallbackPage);
	}

	return new Response.error();
});

// Preload
if (workbox.navigationPreload.isSupported()) {
	workbox.navigationPreload.enable();
}

// Cache assets
registerRoute(
	({request}) =>
		request.destination === 'style' ||
		request.destination === 'script' ||
		request.destination === 'font',
	new StaleWhileRevalidate({
		cacheName: 'jupwa-assets',
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
		cacheName: 'jupwa-images',
		plugins: [
			new CacheableResponsePlugin({
				statuses: [0, 200]
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
		cacheName: CACHE,
		plugins: [
			new CacheableResponsePlugin({
				statuses: [0, 200]
			}),
			new ExpirationPlugin({
				maxEntries: 100,
				maxAgeSeconds: 30 * 24 * 60 * 60,
				purgeOnQuotaError: true
			})
		]
	})
);

self.addEventListener('fetch', (event) => {
	if (event.request.mode === 'navigate') {
		event.respondWith((async () => {
			try {
				const preloadResp = await event.preloadResponse;
				if (preloadResp) {
					return preloadResp;
				}
				const networkResp = await fetch(event.request);
				return networkResp;
			} catch (error) {
				const cache = await caches.open(CACHE);
				const cachedResp = await cache.match(offlineFallbackPage);
				return cachedResp;
			}
		})());
	}
});
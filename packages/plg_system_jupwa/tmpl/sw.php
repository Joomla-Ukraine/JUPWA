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

const CACHE = "jupwa-offline";
const offlineFallbackPage = "/offline.php";

self.addEventListener("message", (event) => {
	if (event.data && event.data.type === "SKIP_WAITING") {
		self.skipWaiting();
	}
});

self.addEventListener('install', async (event) => {
	event.waitUntil(
		caches.open(CACHE).then((cache) => cache.add(offlineFallbackPage))
	);
});

if (workbox.navigationPreload.isSupported()) {
	workbox.navigationPreload.enable();
}

workbox.routing.registerRoute(
	new RegExp('/*'),
	new workbox.strategies.StaleWhileRevalidate({
		cacheName: CACHE
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
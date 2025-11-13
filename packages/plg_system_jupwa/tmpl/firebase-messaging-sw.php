<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 * @formatter:off
 **/

use Joomla\CMS\Uri\Uri;defined('_JEXEC') or die();

/** @var array $displayData */
$data = (object) $displayData;
$site = str_replace('/administrator','',Uri::base());

?>
// Firebase settings
workbox.routing.registerRoute(
	({ url }) => !url.href.includes('fcm.googleapis.com'),
	new workbox.strategies.NetworkFirst()
);

firebase.initializeApp({
	apiKey: '<?= $data->config['apiKey']; ?>',
	projectId: '<?= $data->config['projectId']; ?>',
	messagingSenderId: '<?= $data->config['messagingSenderId']; ?>',
	appId: '<?= $data->config['appId']; ?>',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(payload => {
	if (payload.notification) {
		return;
	}

	const data = payload.data || {};

	const title = data.title || '-';
	const body = data.body || '-';
	const icon = data.image || '<?= $site; ?>favicons/icon_192.png';
	const click_action = data.click_action || '<?= $site; ?>';

	const notificationOptions = {
		body: body,
		icon: icon,
		data: {
			click_action
		}
	};

	self.registration.showNotification(title, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
	event.notification.close();

	const url = event.notification?.data?.click_action || '/';

	event.waitUntil(
		clients.matchAll({ type: "window", includeUncontrolled: true }).then(clientList => {
			for (const client of clientList) {
				if (client.url === url && 'focus' in client) {
					return client.focus();
				}
			}

			if (clients.openWindow) {
				return clients.openWindow(url);
			}
		})
	);
});
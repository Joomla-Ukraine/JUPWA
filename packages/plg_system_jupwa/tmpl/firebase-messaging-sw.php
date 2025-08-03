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

self.addEventListener('notificationclick', function(event) {
	event.notification.close();

	const clickAction = event.notification.data?.click_action;

	if (clickAction) {
		event.waitUntil(clients.openWindow(clickAction));
	} else {
		event.waitUntil(clients.openWindow('https://sci314.com/news'));
	}
});

messaging.onBackgroundMessage(payload => {
	const { title, body, click_action } = payload.data || {};

	const notificationOptions = {
		body: body || '',
		icon: '<?= $site; ?>favicons/icon_192.png' || '/favicon.ico',
		data: {
			click_action: click_action || 'https://sci314.com'
		}
	};

	self.registration.showNotification(title || 'Повідомлення', notificationOptions);
});
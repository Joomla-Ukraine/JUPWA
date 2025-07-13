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

use Joomla\CMS\Uri\Uri;defined('_JEXEC') or die('Restricted access');

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
	console.log('[sw.js] Received background message:', payload);

	const notificationTitle = payload.notification?.title || 'Background Message';
	const notificationOptions = {
		body: payload.notification?.body || 'Background message body.',
		icon: '<?= $site; ?>favicons/icon_180.png'
	};
	self.registration.showNotification(notificationTitle, notificationOptions);
});

console.log('Service Worker loaded with Workbox and Firebase');
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

defined('_JEXEC') or die('Restricted access');

/** @var array $displayData */
$data = (object) $displayData;

?>


// Firebase settings
workbox.routing.registerRoute(
	({ url }) => !url.href.includes('fcm.googleapis.com'),
	new workbox.strategies.NetworkFirst()
);

importScripts('<?= $data->firebase_app; ?>');
importScripts('<?= $data->firebase_messaging; ?>');

firebase.initializeApp({
	apiKey: '<?= $data->config['apiKey']; ?>',
	projectId: '<?= $data->config['projectId']; ?>',
	messagingSenderId: '<?= $data->config['messagingSenderId']; ?>',
	appId: '<?= $data->config['appId']; ?>',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(payload => {
	const { title, body } = payload.notification;
	self.registration.showNotification(title, { body });
});
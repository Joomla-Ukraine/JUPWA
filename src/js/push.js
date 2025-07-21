"use strict";

import '../scss/notification.scss';

import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage} from "firebase/messaging";
import {subscribe} from './push/subscribe';
import {unsubscribe} from './push/unsubscribe';
import jupwaNotification from './modules/notification';

(() => {
    document.addEventListener('DOMContentLoaded', async () => {

        const jupwaConfigs = document.getElementById('pwapush'),
            subscribeButton = document.getElementById('subscribeButton'),
            unsubscribeButton = document.getElementById('unsubscribeButton');

        if (jupwaConfigs) {

            const jupwaFirebase = JSON.parse(jupwaConfigs.textContent),
                firebaseConfig = jupwaFirebase.firebase,
                csrfToken = jupwaFirebase.csrf,
                urlSW = jupwaFirebase.sw,
                urlSubscribe = jupwaFirebase.api.subscribe,
                urlUnSubscribe = jupwaFirebase.api.unsubscribe;

            const app = initializeApp(firebaseConfig),
                messaging = getMessaging(app);

            let swRegistration = null;
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', async () => {

                    if (/iPhone|iPad|iPod/.test(navigator.userAgent) && !navigator.standalone) {
                        const pwaInstall = document.getElementsByTagName('pwa-install')[0];
                        pwaInstall.showDialog();

                        subscribeButton.disabled = true;

                        jupwaNotification('Додайте сайт на головний екран, щоб увімкнути сповіщення.');

                        return;
                    }

                    swRegistration = await navigator.serviceWorker.register(urlSW);

                    if (!('PushManager' in window)) {

                        jupwaNotification('Push-сповіщення не підтримуються в цьому браузері.');

                        subscribeButton.disabled = true;

                        return;
                    }

                    if (Notification.permission === 'default') {
                        // 'Перевірка кнопок'
                    } else if (Notification.permission === 'granted') {
                        const tokenStorage = localStorage.getItem('jupwaFCMToken');
                        if (tokenStorage) {
                            unsubscribeButton.disabled = false;
                            subscribeButton.disabled = true;
                        }
                    } else {
                        jupwaNotification('Дозвіл на сповіщення відхилено. Натисніть кнопку, щоб запитати знову.');
                    }
                });

            } else {
                subscribeButton.disabled = true;
                unsubscribeButton.disabled = true;

                jupwaNotification('Service Worker не підтримується в цьому браузері.');
            }

            onMessage(messaging, (payload) => {
                const {title, body, image} = payload.notification;

                if (Notification.permission === 'granted') {
                    new Notification(title, {
                        body,
                        icon: image || '/favicon.ico'
                    });
                }

                jupwaNotification(`${payload.notification.title} — ${payload.notification.body}`);

            });

            subscribeButton.addEventListener('click', () => {
                subscribe({
                    csrfToken: csrfToken,
                    urlSW: urlSW,
                    urlSubscribe: urlSubscribe,
                    urlUnSubscribe: urlUnSubscribe,
                    unsubscribeButton: unsubscribeButton,
                    subscribeButton: subscribeButton,
                    messaging: messaging,
                    firebaseConfig: firebaseConfig,
                    swRegistration: swRegistration
                });
            });

            unsubscribeButton.addEventListener('click', () => {
                unsubscribe({
                    csrfToken: csrfToken,
                    urlSW: urlSW,
                    urlSubscribe: urlSubscribe,
                    urlUnSubscribe: urlUnSubscribe,
                    unsubscribeButton: unsubscribeButton,
                    subscribeButton: subscribeButton,
                    messaging: messaging,
                    firebaseConfig: firebaseConfig,
                    swRegistration: swRegistration
                });
            });
        }

    });
})();
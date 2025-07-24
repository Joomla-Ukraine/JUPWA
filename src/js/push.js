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
                urlSW = jupwaFirebase.sw;

            const urlSubscribe = jupwaFirebase.api.subscribe,
                urlUnSubscribe = jupwaFirebase.api.unsubscribe;

            const lang = jupwaFirebase.lang;

            const app = initializeApp(firebaseConfig),
                messaging = getMessaging(app);

            if (subscribeButton && unsubscribeButton) {
                let swRegistration = null;
                if ('serviceWorker' in navigator) {
                    window.addEventListener('load', async () => {
                        if (/iPhone|iPad|iPod/.test(navigator.userAgent) && !navigator.standalone) {
                            unsubscribeButton.disabled = true;
                            subscribeButton.disabled = true;

                            jupwaNotification(lang.addToMainDisplay);

                            return;
                        }

                        swRegistration = await navigator.serviceWorker.register(urlSW);

                        if (!('PushManager' in window)) {
                            jupwaNotification(lang.notSupport);
                            subscribeButton.disabled = true;

                            return;
                        }

                        if (Notification.permission === 'granted') {
                            const tokenStorage = localStorage.getItem('jupwaFCMToken');
                            if (tokenStorage) {
                                unsubscribeButton.disabled = false;
                                subscribeButton.disabled = true;
                            }
                        } else {
                            jupwaNotification(lang.notGranted);
                        }
                    });
                } else {
                    subscribeButton.disabled = true;
                    unsubscribeButton.disabled = true;

                    jupwaNotification(lang.swNotSupport);
                }

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
                        swRegistration: swRegistration,
                        lang: lang
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
                        swRegistration: swRegistration,
                        lang: lang
                    });
                });
            }

            onMessage(messaging, (payload) => {
                const {title, body, image} = payload.notification;
                if (Notification.permission === 'granted') {
                    new Notification(title, {
                        body,
                        icon: image || '/favicon.ico'
                    });
                }

                jupwaNotification(payload.notification.title, payload.notification.body);
            });
        }
    });
})();
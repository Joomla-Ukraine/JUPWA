"use strict";

import "../scss/style.scss";
import '../scss/notification.scss';
import {initializeApp} from 'firebase/app';
import {getMessaging, onMessage} from "firebase/messaging";
import {subscribe} from './push/subscribe';
import {unsubscribe} from './push/unsubscribe';
import jupwaNotification from './modules/notification';
import {widget} from "./modules/widget";

(() => {
    document.addEventListener('DOMContentLoaded', async () => {

        const jupwaConfigs = document.getElementById('jupwa-push-setting');

        let subscribeButton = document.getElementById('jupwa-subscribe-btn'),
            unsubscribeButton = document.getElementById('jupwa-unsubscribe-btn'),
            widgetButton = document.getElementById('jupwa-button');

        if (!jupwaConfigs || !jupwaConfigs.textContent) {
            return;
        }

        if (jupwaConfigs) {
            const jupwaFirebase = JSON.parse(jupwaConfigs.textContent),
                firebaseConfig = jupwaFirebase.firebase,
                csrfToken = jupwaFirebase.csrf,
                urlSW = jupwaFirebase.sw,
                lang = jupwaFirebase.localisation;

            const urlSubscribe = jupwaFirebase.api.subscribe,
                urlUnSubscribe = jupwaFirebase.api.unsubscribe;

            if (!(subscribeButton || unsubscribeButton)) {
                new widget();

                subscribeButton = document.getElementById('jupwa-subscribe-btn');
                unsubscribeButton = document.getElementById('jupwa-unsubscribe-btn');
                widgetButton = document.getElementById('jupwa-button');
            }

            const app = initializeApp(firebaseConfig),
                messaging = getMessaging(app);

            let swRegistration = null;
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', async () => {
                    if (/iPhone|iPad|iPod/.test(navigator.userAgent) && !navigator.standalone) {
                        if (subscribeButton) {
                            subscribeButton.disabled = true;
                        }

                        if (unsubscribeButton) {
                            unsubscribeButton.disabled = true;
                        }

                        widgetButton.classList.add('jupwa-button-subscrided');

                        jupwaNotification(lang.addToMainDisplay);

                        return;
                    }

                    swRegistration = await navigator.serviceWorker.register(urlSW);

                    if (!('PushManager' in window)) {
                        jupwaNotification(lang.notSupport);

                        if (subscribeButton) {
                            subscribeButton.disabled = true;
                        }

                        widgetButton.classList.add('jupwa-button-subscrided');

                        return;
                    }

                    widgetButton.classList.remove('jupwa-button-subscrided');
                    
                    if (Notification.permission === 'granted') {
                        const tokenStorage = localStorage.getItem('jupwaFCMToken');
                        if (tokenStorage) {
                            if (unsubscribeButton) {
                                unsubscribeButton.disabled = false;
                            }

                            if (subscribeButton) {
                                subscribeButton.disabled = true;
                            }

                            widgetButton.classList.add('jupwa-button-subscrided');
                        }
                    } else {
                        jupwaNotification(lang.notGranted);

                        widgetButton.classList.add('jupwa-button-subscrided');
                    }
                });
            } else {
                if (unsubscribeButton) {
                    unsubscribeButton.disabled = true;
                }

                if (subscribeButton) {
                    subscribeButton.disabled = true;
                }

                widgetButton.classList.add('jupwa-button-subscrided');

                jupwaNotification(lang.swNotSupport);
            }

            if (subscribeButton) {
                subscribeButton.addEventListener('click', () => {
                    subscribe({
                        csrfToken: csrfToken,
                        urlSW: urlSW,
                        urlSubscribe: urlSubscribe,
                        urlUnSubscribe: urlUnSubscribe,
                        widgetButton: widgetButton,
                        unsubscribeButton: unsubscribeButton,
                        subscribeButton: subscribeButton,
                        messaging: messaging,
                        firebaseConfig: firebaseConfig,
                        swRegistration: swRegistration,
                        lang: lang
                    });
                });
            }

            if (unsubscribeButton) {
                unsubscribeButton.addEventListener('click', () => {
                    unsubscribe({
                        csrfToken: csrfToken,
                        urlSW: urlSW,
                        urlSubscribe: urlSubscribe,
                        urlUnSubscribe: urlUnSubscribe,
                        widgetButton: widgetButton,
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
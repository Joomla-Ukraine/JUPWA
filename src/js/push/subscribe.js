"use strict";

import {sendToken} from './sendToken';
import {getToken} from "firebase/messaging";
import jupwaNotification from "../modules/notification";

export async function subscribe(params = {}) {
    try {
        const permission = await Notification.requestPermission();

        if (permission === 'granted') {
            const token = await getToken(params.messaging, {
                serviceWorkerRegistration: await navigator.serviceWorker.register(params.urlSW),
                vapidKey: params.firebaseConfig.vapidKey
            });

            if (token) {
                await sendToken({
                    token: token,
                    csrfToken: params.csrfToken,
                    urlSW: params.urlSW,
                    urlSubscribe: params.urlSubscribe,
                    messageDiv: params.tokenDiv,
                    errorDiv: params.errorDiv,
                });

                params.unsubscribeButton.disabled = false;
                params.subscribeButton.disabled = true;

                //jupwaNotification('Ви підписалися на сповіщення');
            } else {
                jupwaNotification('Токен не отримано. Запросіть дозвіл.');
            }

        } else {
            jupwaNotification('Дозвіл на сповіщення відхилено.');
        }

    } catch (err) {
        jupwaNotification(`Помилка: ${err.message}`);
    }
}
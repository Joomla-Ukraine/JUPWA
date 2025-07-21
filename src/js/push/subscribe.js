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
                params.unsubscribeButton.disabled = false;
                params.subscribeButton.disabled = true;

                jupwaNotification(params.lang.subscribe);

                await sendToken({
                    token: token,
                    csrfToken: params.csrfToken,
                    urlSW: params.urlSW,
                    urlSubscribe: params.urlSubscribe
                });
            } else {
                jupwaNotification(params.lang.tokenNotLoad, 'warning');
            }
        } else {
            jupwaNotification(params.lang.permissionDenied, 'error');
        }
    } catch (err) {
        jupwaNotification(err.message, 'error');
    }
}
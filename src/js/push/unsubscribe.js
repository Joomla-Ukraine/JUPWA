"use strict";

import {deleteToken, getToken} from "firebase/messaging";
import {deleteFCMToken} from './deleteFCMToken';
import jupwaNotification from "../modules/notification";

export async function unsubscribe(params = {}) {
    try {
        const token = await getToken(params.messaging, {
            serviceWorkerRegistration: await navigator.serviceWorker.register(params.urlSW),
            vapidKey: params.firebaseConfig.vapidKey
        });

        if (token) {
            params.unsubscribeButton.disabled = true;
            params.subscribeButton.disabled = false;

            jupwaNotification(params.lang.unsubscribe);

            await deleteToken(params.messaging);
            await deleteFCMToken({
                token: token,
                csrfToken: params.csrfToken,
                urlSW: params.urlSW,
                urlUnSubscribe: params.urlUnSubscribe
            });

            localStorage.removeItem('jupwaFCMToken');
        } else {
            jupwaNotification(params.lang.tokenNotFound, 'warning');
        }
    } catch (err) {
        jupwaNotification(err.message, 'error');
    }
}
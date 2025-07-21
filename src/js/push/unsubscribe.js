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
            await deleteToken(params.messaging);

            await deleteFCMToken({
                token: token,
                csrfToken: params.csrfToken,
                urlSW: params.urlSW,
                urlUnSubscribe: params.urlUnSubscribe,
                messageDiv: params.tokenDiv,
                errorDiv: params.errorDiv,
            });

            localStorage.removeItem('jupwaFCMToken');

            //jupwaNotification('Ви успішно відписалися від сповіщень');

            params.unsubscribeButton.disabled = true;
            params.subscribeButton.disabled = false;
        } else {
            jupwaNotification('Немає токена для видалення');
        }

    } catch (err) {
        jupwaNotification(`Помилка при відписці: ${err.message}`);
    }
}
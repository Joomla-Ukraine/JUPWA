"use strict";

import axios from 'axios';
import {showMessage} from './notify.js';

export async function requestPermission({messaging, swRegistration, firebaseConfig, csrfToken, urlSubscribe}) {
    const permission = await Notification.requestPermission();

    if (permission !== 'granted') throw new Error('Дозвіл на сповіщення відхилено.');

    showMessage('Дозвіл на сповіщення отримано.');

    const token = await messaging.getToken({
        vapidKey: firebaseConfig.vapidKey,
        serviceWorkerRegistration: swRegistration
    });

    if (!token) throw new Error('Токен не отримано');

    await sendTokenToServer(token, csrfToken, urlSubscribe);

    return token;
}

export async function unsubscribe({messaging, firebaseConfig, swRegistration, csrfToken, urlUnSubscribe}) {

    const token = await messaging.getToken({
        vapidKey: firebaseConfig.vapidKey,
        serviceWorkerRegistration: swRegistration
    });

    if (!token) throw new Error('Немає токена для видалення.');

    await messaging.deleteToken();
    await deleteTokenFromServer(token, csrfToken, urlUnSubscribe);

    return token;
}

async function sendTokenToServer(token, csrfToken, urlSubscribe) {
    const formData = new FormData();
    formData.append('fcm_token', token);

    const res = await axios.post(
        urlSubscribe,
        formData,
        {
            headers: {'X-CSRF-Token': csrfToken}
        }
    );

    showMessage('Токен збережено на сервері.<br>// ' + res.data.data);
}

async function deleteTokenFromServer(token, csrfToken, urlUnSubscribe) {
    const formData = new FormData();
    
    formData.append('fcm_token', token);

    const res = await axios.post(
        urlUnSubscribe,
        formData,
        {
            headers: {'X-CSRF-Token': csrfToken}
        }
    );

    showMessage('Токен видалено з сервера.<br>// ' + res.data.data);
}

"use strict";

import {requestPermission, unsubscribe} from './messaging.js';
import {showError, showNotification} from './notify.js';

export function setupHandlers({messaging, swRegistration, firebaseConfig, csrfToken, urlSubscribe, urlUnSubscribe}) {

    const subscribeBtn = document.getElementById('subscribeButton'),
        unsubscribeBtn = document.getElementById('unsubscribeButton'),
        tokenDiv = document.getElementById('token');

    subscribeBtn.addEventListener('click',
        async () => {
            try {
                const token = await requestPermission({
                    messaging,
                    swRegistration,
                    firebaseConfig,
                    csrfToken,
                    urlSubscribe
                });

                tokenDiv.innerHTML = 'Токен: ' + token;
                subscribeBtn.disabled = true;
                unsubscribeBtn.disabled = false;

            } catch (err) {
                showError(err.message);
            }
        }
    );

    unsubscribeBtn.addEventListener('click', async () => {
        try {
            await unsubscribe({
                messaging,
                firebaseConfig,
                swRegistration,
                csrfToken,
                urlUnSubscribe
            });

            tokenDiv.innerHTML = '';
            subscribeBtn.disabled = false;
            unsubscribeBtn.disabled = true;

        } catch (err) {
            showError(err.message);
        }
    });

    messaging.onMessage(showNotification);
}

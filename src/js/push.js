"use strict";

import {getJupwaConfig} from './push/config.js';
import {registerServiceWorker} from './push/serviceWorker.js';

(() => {
    document.addEventListener('DOMContentLoaded', async () => {

        // Notification
        if (document.querySelector('.jupwa-js-notification')) {
            window.jupwaNotification = import(
                /* webpackChunkName: "jupwa-notification" */
                './modules/notification'
                )
                .then(module => {
                    module.default();
                });
        }

        const config = getJupwaConfig();

        firebase.initializeApp(config.firebase);
        const messaging = firebase.messaging();

        try {
            const swRegistration = await registerServiceWorker(config.sw);

            const {setupHandlers} = await import(/* webpackPreload: true */ './push/handlers.js');

            setupHandlers({
                messaging,
                swRegistration,
                firebaseConfig: config.firebase,
                csrfToken: config.csrf,
                urlSubscribe: config.api.subscribe,
                urlUnSubscribe: config.api.unsubscribe
            });

        } catch (err) {
            const {showError} = await import('./push/notify.js');
            showError(err.message);
        }

    });
})();
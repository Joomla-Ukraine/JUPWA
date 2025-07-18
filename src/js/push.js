"use strict";

(() => {
    document.addEventListener('DOMContentLoaded', () => {

        // Notification
        if (document.querySelector('.jupwa-js-notification')) {
            import(
                /* webpackChunkName: "jupwa-notification" */
                './modules/notification'
                )
                .then(module => {
                    module.default();
                });
        }

    });
})();
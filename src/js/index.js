"use strict";

(() => {
    document.addEventListener('DOMContentLoaded', () => {

        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        if (isIOS && document.getElementById('pwa-install')) {
            import(
                /* webpackChunkName: "jupwa-install" */
                /* webpackPrefetch: true */
                '@khmyznikov/pwa-install');
        }

        if (document.getElementById('pwaicons')) {
            import(
                /* webpackChunkName: "jupwa-splash" */
                /* webpackPrefetch: true */
                './modules/iOsSplash')
                .then(m => m.default());
        }

    });
})();
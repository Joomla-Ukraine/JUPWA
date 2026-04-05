"use strict";

import {generateIosPwaSplash} from 'ios-pwa-splash-screen';

(() => {
    document.addEventListener('DOMContentLoaded', async () => {

        let jupwaConfigIcons = document.getElementById('pwaicons');
        jupwaConfigIcons = JSON.parse(jupwaConfigIcons.textContent);

        generateIosPwaSplash({
            icon: {
                url: jupwaConfigIcons.icon,
                backgroundColor: jupwaConfigIcons.color,
                margin: 20,
            },
            crossOrigin: 'anonymous',
            ensureMetaTags: true,
            debug: false,
        });

    });
})();
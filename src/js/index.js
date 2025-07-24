"use strict";

import '@khmyznikov/pwa-install';
import iOSSplash from "./modules/splash";

(() => {
    document.addEventListener('DOMContentLoaded', async () => {

        if (/iPhone|iPad|iPod/.test(navigator.userAgent)) {
            let jupwaConfigIcons = document.getElementById('pwaicons');
            jupwaConfigIcons = JSON.parse(jupwaConfigIcons.textContent);

            iOSSplash(jupwaConfigIcons.icon, jupwaConfigIcons.color);
        }

    });
})();
"use strict";

import {Capacitor} from '@capacitor/core';
import {InAppReview} from '@capacitor-community/in-app-review';

export const platform = 'native';

/**
 * @returns {Promise<boolean>}
 */
async function appReview() {
    if (!Capacitor.isNativePlatform()) {
        return false;
    }

    if (!['ios', 'android'].includes(Capacitor.getPlatform())) {
        return false;
    }

    try {
        await InAppReview.requestReview();

        return true;

    } catch (error) {
        console.error('[InAppReview] Failed to request review:', error);

        return false;
    }
}

export default async function init() {
    await appReview();

    document.querySelectorAll('.jupwa-app-appreview')
        .forEach((element) => {
            element.hidden = false;
        });
}

window.appReview = appReview;

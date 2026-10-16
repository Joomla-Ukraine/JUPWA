"use strict";

import {SplashScreen} from '@capacitor/splash-screen';

export const platform = 'native';

export default async function init(options = {}) {
    const {
        visible = false,
        fadeInDuration,
        fadeOutDuration,
    } = options;

    if (visible) {
        await SplashScreen.show({
            autoHide: false,
            ...(Number.isFinite(Number(fadeInDuration)) && fadeInDuration !== undefined
                ? {fadeInDuration: Number(fadeInDuration)}
                : {}),
        });

        return;
    }

    await SplashScreen.hide(
        Number.isFinite(Number(fadeOutDuration)) && fadeOutDuration !== undefined
            ? {fadeOutDuration: Number(fadeOutDuration)}
            : {}
    );
}

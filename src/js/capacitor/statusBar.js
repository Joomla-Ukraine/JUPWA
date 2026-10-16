"use strict";

import {StatusBar, Style} from '@capacitor/status-bar';

export const platform = 'native';

export default async function init(options = {}) {
    const {
        statusBarStyle,
        backgroundColor,
        overlaysWebView,
        visible,
    } = options;

    // --- Status Bar Style ---
    if (statusBarStyle) {
        const styleMap = {
            DARK: Style.Dark,
            LIGHT: Style.Light,
            DEFAULT: Style.Default,
        };

        const style = styleMap[statusBarStyle.toUpperCase()] || Style.Default;
        await StatusBar.setStyle({style});
    }

    // --- Background Color (Android) ---
    if (backgroundColor) {
        await StatusBar.setBackgroundColor({color: backgroundColor});
    }

    // --- Overlays WebView ---
    if (typeof overlaysWebView === 'boolean') {
        await StatusBar.setOverlaysWebView({overlay: overlaysWebView});
    }

    // --- Visible / Hidden ---
    if (typeof visible === 'boolean') {
        if (visible) {
            await StatusBar.show();
        } else {
            await StatusBar.hide();
        }
    }
}

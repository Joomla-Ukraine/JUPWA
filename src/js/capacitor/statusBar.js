"use strict";

import {StatusBar, Style} from '@capacitor/status-bar';

export const platform = 'native';

export default async function init(options = {}) {
    // --- Status Bar Style ---
    if (options.statusBarStyle) {
        const styleMap = {
            DARK: Style.Dark,
            LIGHT: Style.Light,
            DEFAULT: Style.Default,
        };

        const style = styleMap[options.statusBarStyle.toUpperCase()] || Style.Default;
        await StatusBar.setStyle({style});
    }

    // --- Background Color (Android) ---
    if (options.backgroundColor) {
        await StatusBar.setBackgroundColor({color: options.backgroundColor});
    }

    // --- Overlays WebView ---
    if (typeof options.overlaysWebView === 'boolean') {
        await StatusBar.setOverlaysWebView({overlay: options.overlaysWebView});
    }

    // --- Visible / Hidden ---
    if (typeof options.visible === 'boolean') {
        if (options.visible) {
            await StatusBar.show();
        } else {
            await StatusBar.hide();
        }
    }
}
"use strict";

import {Capacitor} from '@capacitor/core';

function getAppSettings() {
    const el = document.getElementById('jupwa-app-setting');
    if (!el) {
        return null;
    }

    try {
        return JSON.parse(el.textContent);

    } catch (e) {
        console.error('[AppSettings] Failed to parse JSON', e);

        return null;
    }
}

function getOptions(config) {
    if (config === true) {
        return {};
    }

    if (typeof config === 'object' && config !== null) {
        return config;
    }

    return {};
}

async function loadModule(name, config) {
    try {
        const mod = await import(
            /* webpackChunkName: "jupwa-app-[request]" */
            `./capacitor/${name}`);

        const platform = mod.platform || 'native';
        const isNative = Capacitor.isNativePlatform();

        const shouldRun =
            platform === 'both' ||
            (platform === 'native' && isNative) ||
            (platform === 'web' && !isNative);

        if (!shouldRun) {
            console.log(`[${name}] skipped (platform: ${platform})`);

            return;
        }

        const init = mod.default || mod.init;
        if (typeof init === 'function') {
            await init(getOptions(config));
        }

    } catch (e) {
        console.error(`[${name}] Failed to load`, e);
    }
}

async function loadModules() {
    const modules = getAppSettings()?.modules;
    if (!modules) {
        return;
    }

    const tasks = [];

    /*    if (modules.push) {
            tasks.push(loadModule('push', modules.push));
        }*/

    if (modules.statusBar) {
        tasks.push(loadModule('statusBar', modules.statusBar));
    }

    if (modules.appReview) {
        tasks.push(loadModule('appReview', modules.appReview));
    }

    /*    if (modules.appUpdate) {
            tasks.push(loadModule('appUpdate', modules.appUpdate));
        }*/

    await Promise.allSettled(tasks);
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        loadModules
    );

} else {
    loadModules();
}
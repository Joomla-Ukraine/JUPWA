"use strict";

import {AppUpdate} from '@capawesome/capacitor-app-update';

export const platform = 'native';

function parseVersion(version) {
    return String(version || '0')
        .split('.')
        .map((part) => parseInt(part, 10) || 0);
}

function isVersionBelow(current, minimum) {
    const currentParts = parseVersion(current);
    const minimumParts = parseVersion(minimum);
    const length = Math.max(currentParts.length, minimumParts.length);

    for (let i = 0; i < length; i++) {
        const currentPart = currentParts[i] || 0;
        const minimumPart = minimumParts[i] || 0;

        if (currentPart < minimumPart) {
            return true;
        }

        if (currentPart > minimumPart) {
            return false;
        }
    }

    return false;
}

function showForceOverlay(title, message, buttonLabel, onUpdate) {
    let element = document.getElementById('jupwa-force-update');

    if (!element) {
        element = document.createElement('div');
        element.id = 'jupwa-force-update';
        element.innerHTML = `
            <div class="force-update-card">
                <div class="force-update-title"></div>
                <p class="force-update-msg"></p>
                <button type="button" class="force-update-btn"></button>
            </div>
        `;
        document.body.appendChild(element);
        element.querySelector('.force-update-btn').addEventListener('click', onUpdate);
    }

    element.querySelector('.force-update-title').textContent = title;
    element.querySelector('.force-update-msg').textContent = message;
    element.querySelector('.force-update-btn').textContent = buttonLabel;
    element.style.display = 'flex';
    document.documentElement.classList.add('force-update-active');
}

async function openStore(options = {}) {
    const {storeUrlAndroid, storeUrlIos} = options;

    try {
        await AppUpdate.openAppStore();

        return;

    } catch (_) {
    }

    const platformName = window.Capacitor?.getPlatform?.() || '';
    const url = platformName === 'ios' ? storeUrlIos : storeUrlAndroid;

    if (url) {
        window.location.href = url;
    }
}

export default async function init(options = {}) {
    const {
        minVersion = null,
        force = false,
        checkOnStart = true,
        delayMs = 2000,
        autoOpenStore = false,
        storeUrlAndroid = '',
        storeUrlIos = '',
        language = {},
    } = options;

    // --- Force update ---
    if (minVersion && force) {
        let currentVersion = null;

        try {
            const {App} = await import('@capacitor/app');
            currentVersion = (await App.getInfo()).version;

        } catch (_) {
        }

        try {
            const info = await AppUpdate.getAppUpdateInfo();

            if (!currentVersion && info?.currentVersionName) {
                currentVersion = info.currentVersionName;
            }

        } catch (_) {
        }

        if (currentVersion && isVersionBelow(currentVersion, minVersion)) {
            showForceOverlay(
                language.forceUpdateTitle || 'Update required',
                language.forceUpdateMessage || 'Please update the app to continue.',
                language.forceUpdateButton || 'Update',
                () => openStore({storeUrlAndroid, storeUrlIos})
            );

            return {required: true};
        }
    }

    // --- Soft update ---
    if (!checkOnStart || force) {
        return;
    }

    setTimeout(async () => {
        try {
            const info = await AppUpdate.getAppUpdateInfo();
            const available = info.updateAvailability === 'UPDATE_AVAILABLE' || info.updateAvailability === 2;

            if (!available) {
                return;
            }

            const template = language.updateAvailableMessage || 'A new version{version} is available. Update now?';
            const versionPart = info.availableVersionName ? ` (${info.availableVersionName})` : '';
            const message = template.replace('{version}', versionPart);

            if (autoOpenStore || window.confirm(message)) {
                await openStore({storeUrlAndroid, storeUrlIos});
            }

        } catch (error) {
            console.warn('[appUpdate] check failed:', error);
        }
    }, Number(delayMs) || 2000);
}

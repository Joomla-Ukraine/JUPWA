"use strict";

import {Capacitor} from '@capacitor/core';
import {FirebaseMessaging} from '@capacitor-firebase/messaging';
import {SettingsLauncher} from '@capawesome/capacitor-settings-launcher';
import {FCM_STORAGE_KEY, getItemWithExpiry} from '../push/utils/storage';

export const platform = 'native';

const DISABLED_KEY = 'jupwaCapacitorPushDisabled';
const status = {
    permission: 'unknown',
    subscribed: Boolean(getItemWithExpiry(FCM_STORAGE_KEY)),
    loading: false,
    message: '',
    error: '',
};

let currentToken = null;
let handlers = {};
let listenersReady = false;
let language = {};

function message(key, fallback) {
    return language[key] || fallback;
}

function isDisabled() {
    return localStorage.getItem(DISABLED_KEY) === 'true';
}

function getPushControls(menu) {
    const controls = [...menu.querySelectorAll('input[name="push-notifications"]')];
    const checkbox = controls.find((control) => control.type === 'checkbox');

    if (checkbox) {
        return {type: 'checkbox', inputs: [checkbox]};
    }

    return {
        type: 'radio',
        inputs: controls.filter((control) => control.type === 'radio'),
    };
}

function publishToken(token) {
    currentToken = token || null;

    window.jupwaPushToken = currentToken;
    window.PushManager = window.PushManager || {};
    window.PushManager.getToken = () => currentToken;
}

function renderStatus() {
    const permissionElement = document.getElementById('jupwa-push-permission');
    const subscriptionElement = document.getElementById('jupwa-push-subscription');
    const loadingElement = document.getElementById('jupwa-push-loading');
    const resultElement = document.getElementById('jupwa-push-result');
    const errorElement = document.getElementById('jupwa-push-error');
    const permissionLabels = {
        granted: message('permissionGranted', 'Granted'),
        denied: message('permissionDenied', 'Denied'),
        prompt: message('permissionPrompt', 'Not requested yet'),
        'prompt-with-rationale': message('permissionPromptRationale', 'Confirmation required'),
        unknown: message('permissionUnknown', 'Unknown'),
    };

    if (permissionElement) {
        permissionElement.textContent = permissionLabels[status.permission] || message('permissionUnknown', 'Unknown');
    }

    if (subscriptionElement) {
        subscriptionElement.textContent = status.subscribed
            ? message('subscriptionActive', 'Active')
            : message('subscriptionInactive', 'Inactive');
    }

    if (loadingElement) {
        loadingElement.hidden = !status.loading;
    }

    if (resultElement) {
        resultElement.textContent = status.message;
        resultElement.hidden = !status.message;
    }

    if (errorElement) {
        errorElement.textContent = status.error;
        errorElement.hidden = !status.error;
    }

    const menu = document.getElementById('jupwa-capacitor-push-menu');
    const enabled = status.permission === 'granted' && status.subscribed;

    if (menu) {
        const {type, inputs} = getPushControls(menu);

        inputs.forEach((input) => {
            input.checked = type === 'checkbox'
                ? enabled
                : input.value === (enabled ? 'enabled' : 'disabled');
            input.disabled = status.loading;
        });
    }
}

function setSubscriptionState(subscribed) {
    status.subscribed = Boolean(subscribed);

    renderStatus();
}

function setRequestState({loading = false, message = '', error = ''} = {}) {
    status.loading = loading;
    status.message = message;
    status.error = error;

    renderStatus();
}

async function enablePush() {
    setRequestState({loading: true});

    try {
        const permission = await FirebaseMessaging.requestPermissions();

        status.permission = permission.receive || 'unknown';

        renderStatus();

        if (permission.receive !== 'granted') {
            setRequestState({
                error: message('permissionNotGranted', 'Notification permission was not granted. Check your device settings.')
            });

            return;
        }

        localStorage.removeItem(DISABLED_KEY);
        const {token} = await FirebaseMessaging.getToken();

        if (!token || typeof handlers.onToken !== 'function') {
            throw new Error(message('tokenTransferFailed', 'Could not send the token to JUPWA. Please try again.'));
        }

        publishToken(token);
        await handlers.onToken(token);
        setSubscriptionState(true);
        setRequestState({
            message: message('enableSuccess', 'Push notifications enabled.')
        });

    } catch (error) {
        console.error('[push] enable failed:', error);

        setRequestState({
            error: error?.message || message('enableFailed', 'Could not enable notifications.')
        });
    }
}

async function disablePush() {
    setRequestState({loading: true});

    try {
        let token = currentToken || getItemWithExpiry(FCM_STORAGE_KEY);

        if (!token) {
            try {
                token = (await FirebaseMessaging.getToken()).token;

            } catch (_) {
                token = null;
            }
        }

        if (token && typeof handlers.onDisable !== 'function') {
            throw new Error(message('unsubscribeFailed', 'Could not connect to JUPWA to unsubscribe. Please try again.'));
        }

        if (token && handlers.onDisable) {
            await handlers.onDisable(token);
        }

        localStorage.setItem(DISABLED_KEY, 'true');
        publishToken(null);
        setSubscriptionState(false);

        if (token) {
            await FirebaseMessaging.deleteToken();
        }

        setRequestState({
            message: message('disableSuccess', 'Push notifications disabled.')
        });

    } catch (error) {
        console.error('[push] disable failed:', error);

        setRequestState({
            error: error?.message || message('disableFailed', 'Could not disable notifications.')
        });
    }
}

async function createMenu() {
    const menu = document.getElementById('jupwa-capacitor-push-menu');

    if (!menu) {
        console.warn('[push] Menu #jupwa-capacitor-push-menu was not found in the page template');

        return;
    }

    menu.hidden = !Capacitor.isNativePlatform();

    const {type, inputs} = getPushControls(menu);

    inputs.forEach((input) => {
        input.addEventListener('change', () => {
            const enabled = type === 'checkbox'
                ? input.checked
                : input.value === 'enabled';

            if (enabled) {
                enablePush();
            } else {
                disablePush();
            }
        });
    });

    menu.querySelector('[data-push-action="settings"]')?.addEventListener('click', async (event) => {
        event.preventDefault();
        const platformName = Capacitor.getPlatform();

        try {
            if (platformName === 'android') {
                await SettingsLauncher.openNotificationSettings();

                return;
            }

            if (platformName === 'ios') {
                window.location.href = 'app-settings:';
            }

        } catch (error) {
            console.error('[push] could not open notification settings:', error);

            setRequestState({
                error: error?.message || message('settingsOpenFailed', 'Could not open notification settings.')
            });
        }
    });

    await refreshStatus();
}

async function refreshStatus() {
    status.subscribed = Boolean(getItemWithExpiry(FCM_STORAGE_KEY)) && !isDisabled();

    try {
        const permission = await FirebaseMessaging.checkPermissions();

        status.permission = permission.receive || 'unknown';

    } catch (error) {
        console.warn('[push] permission check failed:', error);

        status.permission = 'unknown';
    }

    renderStatus();
}

export default async function init(options = {}) {
    language = options.language || {};

    const {autoRegister = true} = options;

    publishToken(null);

    const menuReady = createMenu();

    if (!listenersReady) {
        listenersReady = true;

        FirebaseMessaging.addListener('tokenReceived', async ({token}) => {
            if (isDisabled()) {
                await FirebaseMessaging.deleteToken();

                return;
            }

            publishToken(token);
            if (typeof handlers.onToken === 'function') {
                const requestInProgress = status.loading;

                if (!requestInProgress) {
                    setRequestState({loading: true});
                }

                try {
                    await handlers.onToken(token);

                    setSubscriptionState(true);

                    if (!requestInProgress) {
                        setRequestState();
                    }

                } catch (error) {
                    if (!requestInProgress) {
                        setRequestState({
                            error: error?.message || message('subscriptionUpdateFailed', 'Could not update the JUPWA subscription.')
                        });
                    }
                }
            }
        });

        FirebaseMessaging.addListener('notificationActionPerformed', ({notification}) => {
            const data = notification?.data || {};
            const target = data.url || data.click_action || data.link || data.route;

            if (typeof target === 'string') {
                try {
                    const url = new URL(target, window.location.origin);

                    if (url.origin === window.location.origin) {
                        window.location.href = url.href;
                    }

                } catch (_) {
                }
            }
        });
    }

    window.jupwaCapacitorPush = {
        setHandlers(nextHandlers = {}) {
            handlers = nextHandlers;
        },
        enable: enablePush,
        disable: disablePush,
        getToken: () => currentToken,
        setSubscriptionState,
        setRequestState,
        refreshStatus,
    };

    if (window.jupwaCapacitorPushHandlers) {
        handlers = window.jupwaCapacitorPushHandlers;

        delete window.jupwaCapacitorPushHandlers;
    }

    await menuReady;

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            refreshStatus();
        }
    });

    if (!autoRegister || isDisabled()) {
        return;
    }

    setRequestState({
        loading: true
    });

    try {
        const permission = await FirebaseMessaging.requestPermissions();

        status.permission = permission.receive || 'unknown';

        if (permission.receive !== 'granted') {
            setRequestState();
            renderStatus();

            return;
        }

        const {token} = await FirebaseMessaging.getToken();

        publishToken(token);

        if (token && typeof handlers.onToken === 'function') {
            await handlers.onToken(token);

            setSubscriptionState(true);
        }

        setRequestState();
        renderStatus();

    } catch (error) {
        console.error('[push] register failed:', error);

        status.permission = 'unknown';
        setRequestState({
            error: error?.message || message('permissionCheckUnknown', 'Could not check notification permission.')
        });
    }
}

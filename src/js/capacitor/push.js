"use strict";

import {PushNotifications} from '@capacitor/push-notifications';

export const platform = 'native';

let currentToken = null;
let listenersReady = false;

function exposeTokenApi() {
    window.PushManager = window.PushManager || {};
    window.PushManager.getToken = () => currentToken || null;
}

export default async function init(options = {}) {
    const autoRegister = options.autoRegister !== false;

    exposeTokenApi();

    if (!listenersReady) {
        listenersReady = true;

        PushNotifications.addListener('registration', ({value}) => {
            currentToken = value;
            exposeTokenApi();
            console.log('[push] token:', value);
        });

        PushNotifications.addListener('registrationError', (err) => {
            console.error('[push] registration error:', err);
        });

        PushNotifications.addListener('pushNotificationReceived', (n) => {
            console.log('[push] foreground:', n);
        });

        PushNotifications.addListener('pushNotificationActionPerformed', (action) => {
            const data = action?.notification?.data || {};
            const target = data.url || data.click_action || data.link || data.route;

            if (typeof target === 'string') {
                try {
                    const u = new URL(target, window.location.origin);
                    if (u.origin === window.location.origin) {
                        window.location.href = u.href;
                    }
                } catch (_) {
                }
            }
        });
    }

    if (!autoRegister) {
        return;
    }

    try {
        const permission = await PushNotifications.requestPermissions();

        if (permission.receive === 'granted') {
            await PushNotifications.register();
        }

    } catch (e) {
        console.error('[push] register failed:', e);
    }
}

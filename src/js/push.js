"use strict";

import "../scss/style.scss";
import "../scss/notification.scss";

import {initFirebase} from "./push/initFirebase";
import {registerSW} from "./push/registerSW";
import jupwaNotification from "./push/utils/notification";
import {getConfig, isIOSandNotStandalone, supportsPush, supportsSW} from "./push/utils/env";
import {createButtonController} from "./push/ui/buttonController";
import {createWidgetController} from "./push/ui/widgetController";
import {getToken, onMessage} from "firebase/messaging";
import {sendToken} from "./push/sendToken";
import {deleteFCMToken} from "./push/deleteFCMToken";
import getTokenNativeIOS from "./push/utils/getTokenNativeIOS";
import {FCM_STORAGE_KEY, FCM_UNSUBSCRIBED_KEY, getItemWithExpiry} from "./push/utils/storage";

const nativeTokenRequests = new Map();

function sendNativeToken(params) {
    const existingRequest = nativeTokenRequests.get(params.token);

    if (existingRequest) {
        return existingRequest;
    }

    const request = sendToken(params).finally(() => {
        nativeTokenRequests.delete(params.token);
    });
    nativeTokenRequests.set(params.token, request);

    return request;
}

(() => {
    document.addEventListener("DOMContentLoaded", async () => {
        const cfg = getConfig("jupwa-push-setting");

        if (!cfg) {
            return;
        }

        const {
            firebase: firebaseConfig,
            csrf: csrfToken,
            sw: urlSW,
            localisation: lang,
            api: {subscribe: urlSubscribe, unsubscribe: urlUnSubscribe} = {},
        } = cfg;

        const isPushManager = typeof window.PushManager?.getToken === 'function';
        const isNativePlatform = window.Capacitor?.isNativePlatform?.() === true;
        const isNativeWebView = isNativePlatform || isPushManager;

        if (isNativeWebView) {
            const widget = document.getElementById('jupwa-widget');

            if (widget) {
                widget.remove();
            }

            const nativePushHandlers = {
                onToken: async (token) => {
                    await sendNativeToken({
                        token,
                        csrfToken,
                        urlSubscribe,
                    });

                    window.jupwaCapacitorPush?.setSubscriptionState(true);
                },
                onDisable: async (token) => {
                    await deleteFCMToken({
                        token,
                        csrfToken,
                        urlUnSubscribe,
                    });

                    window.jupwaCapacitorPush?.setSubscriptionState(false);
                },
            };

            if (typeof window.jupwaCapacitorPush?.setHandlers === 'function') {
                window.jupwaCapacitorPush.setHandlers(nativePushHandlers);
            } else {
                window.jupwaCapacitorPushHandlers = nativePushHandlers;
            }

            try {
                const savedToken = getItemWithExpiry(FCM_STORAGE_KEY);

                getTokenNativeIOS()
                    .then(async (token) => {
                        if (token && token !== savedToken) {
                            try {
                                await sendNativeToken({
                                    token,
                                    csrfToken,
                                    urlSubscribe,
                                });

                                window.jupwaCapacitorPush?.setSubscriptionState(true);

                            } catch (err) {
                                window.jupwaCapacitorPush?.setRequestState({
                                    error: err?.message || lang.tokenNotLoad,
                                });
                            }
                        }
                    });

            } catch (err) {
                jupwaNotification(lang.tokenNotLoad, "warning");
            }

        } else {
            const pageSubscribeBtn = document.getElementById("jupwa-subscribe-btn");
            const pageUnsubscribeBtn = document.getElementById("jupwa-unsubscribe-btn");
            const haveBothPageButtons = Boolean(pageSubscribeBtn && pageUnsubscribeBtn);

            let subscribeButton = pageSubscribeBtn || null;
            let unsubscribeButton = pageUnsubscribeBtn || null;
            let widgetButton = document.getElementById("jupwa-button") || null;
            let widgetAlert = document.getElementById("jupwa-alert") || null;

            if (!haveBothPageButtons) {
                const widget = createWidgetController();

                if (widget) {
                    subscribeButton = widget.subscribeButton;
                    unsubscribeButton = widget.unsubscribeButton;
                    widgetButton = widget.widgetButton;
                    widgetAlert = widget.widgetAlert;
                }
            }

            if (!subscribeButton || !unsubscribeButton) {
                return;
            }

            if (isIOSandNotStandalone()) {
                subscribeButton.hidden = true;
                unsubscribeButton.hidden = true;

                if (widgetAlert) {
                    widgetAlert.hidden = false;
                    widgetAlert.textContent = lang.addToMainDisplay;
                }

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }

                return;
            }

            if (!supportsSW()) {
                subscribeButton.hidden = true;
                unsubscribeButton.hidden = true;

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }

                if (widgetAlert) {
                    widgetAlert.hidden = false;
                    widgetAlert.textContent = lang.swNotSupport;
                }

                return;
            }

            let swRegistration = null;

            try {
                swRegistration = await registerSW(urlSW);
            } catch (e) {
                subscribeButton.hidden = true;
                unsubscribeButton.hidden = true;

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }

                if (widgetAlert) {
                    widgetAlert.hidden = false;
                    widgetAlert.textContent = e?.message || lang.swNotSupport;
                }

                return;
            }

            if (!supportsPush()) {
                subscribeButton.hidden = true;

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }

                if (widgetAlert) {
                    widgetAlert.hidden = false;
                    widgetAlert.textContent = lang.notSupport;
                }
            }

            const messaging = initFirebase(firebaseConfig);

            let tokenStorage = getItemWithExpiry(FCM_STORAGE_KEY);

            const wasUnsubscribed = getItemWithExpiry(FCM_UNSUBSCRIBED_KEY);

            if (Notification.permission === "granted" && !tokenStorage && !wasUnsubscribed) {
                try {
                    const currentToken = await getToken(messaging, {
                        serviceWorkerRegistration: swRegistration
                    });

                    if (currentToken) {
                        await sendToken({
                            token: currentToken,
                            csrfToken,
                            urlSubscribe
                        });

                        tokenStorage = currentToken;
                    }

                } catch (err) {
                    console.error("FCM Auto-refresh error:", err);
                }
            }

            if (Notification.permission === "granted" && tokenStorage) {
                unsubscribeButton.hidden = false;
                subscribeButton.hidden = true;

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }
            } else if (Notification.permission === "denied") {
                unsubscribeButton.hidden = true;
                subscribeButton.hidden = false;

                if (widgetButton) {
                    widgetButton.classList.add("jupwa-button-subscrided");
                }

                if (widgetAlert) {
                    widgetAlert.hidden = false;
                    widgetAlert.textContent = lang.notGranted;
                }
            } else {
                unsubscribeButton.hidden = !tokenStorage;
                subscribeButton.hidden = !!tokenStorage;

                if (widgetButton && !tokenStorage) {
                    widgetButton.classList.remove("jupwa-button-subscrided");
                }
            }

            createButtonController({
                subscribeButton,
                unsubscribeButton,
                widgetButton,
                lang,
                csrfToken,
                urlSW,
                urlSubscribe,
                urlUnSubscribe,
                messaging,
                firebaseConfig,
                swRegistration,
            });

            onMessage(messaging, (payload) => {
                const notif = payload?.data;
                if (!notif) {
                    return;
                }

                const {title, body, image, click_action} = notif;

                if (title || body) {
                    jupwaNotification(title || '', body || '');
                }

                if (document.visibilityState === "hidden") {
                    if (Notification.permission === "granted") {
                        const notification = new Notification(
                            title || '',
                            {
                                body: body || '',
                                icon: image || '/favicon.ico',
                                data: {
                                    url: click_action || ''
                                }
                            }
                        );

                        notification.onclick = (event) => {
                            event.preventDefault();

                            if (click_action) {
                                if ('setAppBadge' in navigator) {
                                    navigator.setAppBadge(0);
                                }

                                window.open(click_action, "_blank");
                            }
                        };
                    }
                }
            });
        }
    });
})();

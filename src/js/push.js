"use strict";

import "../scss/style.scss";
import "../scss/notification.scss";

import {initFirebase} from "./push/initFirebase";
import {registerSW} from "./push/registerSW";
import jupwaNotification from "./push/utils/notification";
import {getConfig, isIOSandNotStandalone, supportsPush, supportsSW} from "./push/utils/env";
import {createButtonController} from "./push/ui/buttonController";
import {createWidgetController} from "./push/ui/widgetController";
import {onMessage} from "firebase/messaging";

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

        const pageSubscribeBtn = document.getElementById("jupwa-subscribe-btn");
        const pageUnsubscribeBtn = document.getElementById("jupwa-unsubscribe-btn");
        const haveBothPageButtons = Boolean(pageSubscribeBtn && pageUnsubscribeBtn);

        let subscribeButton = pageSubscribeBtn || null;
        let unsubscribeButton = pageUnsubscribeBtn || null;
        let widgetButton = document.getElementById("jupwa-button") || null;

        if (!haveBothPageButtons) {
            const widget = createWidgetController();

            if (widget) {
                subscribeButton = widget.subscribeButton;
                unsubscribeButton = widget.unsubscribeButton;
                widgetButton = widget.widgetButton;
            }
        }

        if (!subscribeButton || !unsubscribeButton) {
            return;
        }

        if (isIOSandNotStandalone()) {
            subscribeButton.hidden = true;
            unsubscribeButton.hidden = true;

            if (widgetButton) {
                widgetButton.classList.add("jupwa-button-subscrided");
            }

            jupwaNotification(lang.addToMainDisplay);

            return;
        }

        if (!supportsSW()) {
            subscribeButton.hidden = true;
            unsubscribeButton.hidden = true;

            if (widgetButton) {
                widgetButton.classList.add("jupwa-button-subscrided");
            }

            jupwaNotification(lang.swNotSupport);

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

            jupwaNotification(e?.message || lang.swNotSupport, "error");

            return;
        }

        if (!supportsPush()) {
            subscribeButton.hidden = true;

            if (widgetButton) {
                widgetButton.classList.add("jupwa-button-subscrided");
            }

            jupwaNotification(lang.notSupport);
        }

        const messaging = initFirebase(firebaseConfig);
        const tokenStorage = localStorage.getItem("jupwaFCMToken");

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

            jupwaNotification(lang.notGranted);
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
            const notif = payload?.notification;

            if (!notif) {
                return;
            }

            const {title, body, image} = notif;

            if (Notification.permission === "granted") {
                try {
                    new Notification(title || "", {body, icon: image || "/favicon.ico"});
                } catch (_) {
                }
            }

            if (title || body) {
                jupwaNotification(title || "", body || "");
            }
        });
    });
})();

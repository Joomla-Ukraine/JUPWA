"use strict";

import {getToken} from "firebase/messaging";
import {sendToken} from "./sendToken";
import jupwaNotification from "./utils/notification";

export async function subscribe(params = {}) {
    const {
        messaging,
        firebaseConfig,
        swRegistration,
        urlSW,
        csrfToken,
        urlSubscribe,
        lang,
        subscribeButton,
        unsubscribeButton,
        widgetButton,
    } = params;

    try {
        const permission = await Notification.requestPermission();

        if (permission !== "granted") {
            jupwaNotification(lang.permissionDenied, "error");

            return;
        }

        if (!swRegistration) {
            throw new Error("Service Worker registration is missing");
        }

        const token = await getToken(messaging, {
            serviceWorkerRegistration: swRegistration,
            vapidKey: firebaseConfig?.vapidKey || undefined,
        });

        if (!token) {
            jupwaNotification(lang.tokenNotLoad, "warning");

            return;
        }

        if (unsubscribeButton) {
            unsubscribeButton.hidden = false;
        }

        if (subscribeButton) {
            subscribeButton.hidden = true;
        }

        if (widgetButton) {
            widgetButton.classList.add("jupwa-button-subscrided");
        }

        jupwaNotification(lang.subscribe);

        await sendToken({
            token,
            csrfToken,
            urlSubscribe,
        });
    } catch (err) {
        jupwaNotification(err?.message || "Subscribe error", "error");
    }
}

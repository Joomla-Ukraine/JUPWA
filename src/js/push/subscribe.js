"use strict";

import {getToken} from "firebase/messaging";
import jupwaNotification from "./utils/notification";
import {sendToken} from "./sendToken";

export async function subscribe(params = {}) {
    const {
        messaging,
        firebaseConfig,
        swRegistration,
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
            jupwaNotification(lang.permissionDenied);

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

        await sendToken({
            token,
            csrfToken,
            urlSubscribe,
        });

        if (unsubscribeButton) {
            unsubscribeButton.hidden = false;
        }

        if (subscribeButton) {
            subscribeButton.hidden = true;
        }

        if (widgetButton) {
            widgetButton.classList.add("jupwa-button-subscrided");
        }

    } catch (err) {
        jupwaNotification(err?.message || "Subscribe error");
    }
}

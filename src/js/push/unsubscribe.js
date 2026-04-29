"use strict";

import {deleteToken, getToken} from "firebase/messaging";
import {deleteFCMToken} from "./deleteFCMToken";
import jupwaNotification from "./utils/notification";

export async function unsubscribe(params = {}) {
    const {
        messaging,
        firebaseConfig,
        swRegistration,
        urlUnSubscribe,
        csrfToken,
        lang,
        subscribeButton,
        unsubscribeButton,
        widgetButton,
    } = params;

    try {
        if (!swRegistration) {
            throw new Error("Service Worker registration is missing");
        }

        const token = await getToken(messaging, {
            serviceWorkerRegistration: swRegistration,
            vapidKey: firebaseConfig?.vapidKey || undefined,
        });

        if (!token) {
            jupwaNotification(lang.tokenNotFound, "warning");

            return;
        }

        if (unsubscribeButton) {
            unsubscribeButton.hidden = true;
        }

        if (subscribeButton) {
            subscribeButton.hidden = false;
        }

        if (widgetButton) {
            widgetButton.classList.remove("jupwa-button-subscrided");
        }

        jupwaNotification(lang.unsubscribe);

        await deleteToken(messaging);
        await deleteFCMToken({
            token,
            csrfToken,
            urlUnSubscribe,
        });

    } catch (err) {
        jupwaNotification(err?.message || "Unsubscribe error");
    }
}

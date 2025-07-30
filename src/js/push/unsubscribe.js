"use strict";

import {deleteToken, getToken} from "firebase/messaging";
import {deleteFCMToken} from "./deleteFCMToken";
import jupwaNotification from "./utils/notification";

/**
 * Відписка без повторної реєстрації SW:
 * використовує переданий swRegistration та messaging.
 */
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

        // Оптимістичне оновлення UI
        if (unsubscribeButton) unsubscribeButton.disabled = true;
        if (subscribeButton) subscribeButton.disabled = false;
        if (widgetButton) widgetButton.classList.remove("jupwa-button-subscrided");

        jupwaNotification(lang.unsubscribe);

        await deleteToken(messaging);
        await deleteFCMToken({
            token,
            csrfToken,
            urlUnSubscribe,
        });

        localStorage.removeItem("jupwaFCMToken");
    } catch (err) {
        jupwaNotification(err?.message || "Unsubscribe error", "error");
    }
}

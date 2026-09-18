"use strict";

import wretch from 'wretch';
import jupwaNotification from "./utils/notification";
import {
    FCM_STORAGE_KEY,
    FCM_UNSUBSCRIBED_KEY,
    FCM_UNSUBSCRIBED_TTL_MINUTES,
    removeItem,
    setItemWithExpiry
} from "./utils/storage";

export async function deleteFCMToken(params = {}) {
    try {
        const formData = new FormData();
        formData.append("fcm_token", params.token);

        await wretch(params.urlUnSubscribe)
            .headers({
                "X-CSRF-Token": params.csrfToken
            })
            .body(formData)
            .post()
            .res();

        removeItem(FCM_STORAGE_KEY);

        setItemWithExpiry(
            FCM_UNSUBSCRIBED_KEY,
            true,
            FCM_UNSUBSCRIBED_TTL_MINUTES
        );

    } catch (err) {
        jupwaNotification(err.message || "Error when unsubscribing");

        throw err;
    }
}

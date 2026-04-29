"use strict";

import wretch from 'wretch';
import jupwaNotification from "./utils/notification";
import {FCM_STORAGE_KEY, FCM_TTL_MINUTES, getItemWithExpiry, setItemWithExpiry} from "./utils/storage";

export async function sendToken(params = {}) {
    const savedToken = getItemWithExpiry(FCM_STORAGE_KEY);
    if (savedToken === params.token) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append("fcm_token", params.token);

        await wretch(params.urlSubscribe)
            .headers({
                "X-CSRF-Token": params.csrfToken
            })
            .body(formData)
            .post()
            .res();

        setItemWithExpiry(FCM_STORAGE_KEY, params.token, FCM_TTL_MINUTES);

    } catch (err) {
        jupwaNotification(err.message || "Subscription error");

        throw err;
    }
}

"use strict";

import wretch from 'wretch';
import jupwaNotification from "./utils/notification";
import {getItemWithExpiry, setItemWithExpiry} from "./utils/storage";

export async function sendToken(params = {}) {
    const STORAGE_KEY = "jupwaFCMToken";
    const TTL_MINUTES = 7 * 24 * 60;

    const savedToken = getItemWithExpiry(STORAGE_KEY);
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

        setItemWithExpiry(STORAGE_KEY, params.token, TTL_MINUTES);

    } catch (err) {
        jupwaNotification(err.message || "Subscription error", "error");

        throw err;
    }
}

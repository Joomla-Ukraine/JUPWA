"use strict";

import wretch from 'wretch';
import jupwaNotification from "./utils/notification";

export async function sendToken(params = {}) {
    const savedToken = localStorage.getItem("jupwaFCMToken");

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

        localStorage.setItem("jupwaFCMToken", params.token);
    } catch (err) {
        jupwaNotification(err.message, "error");

        throw err;
    }
}

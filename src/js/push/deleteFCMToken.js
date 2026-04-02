"use strict";

import wretch from 'wretch';
import jupwaNotification from "./utils/notification";

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

        localStorage.removeItem("jupwaFCMToken");
    } catch (err) {
        jupwaNotification(err.message, "error");

        throw err;
    }
}

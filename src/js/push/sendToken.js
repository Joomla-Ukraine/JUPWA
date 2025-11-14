"use strict";

import axios from "axios";
import jupwaNotification from "./utils/notification";

export async function sendToken(params = {}) {
    const savedToken = localStorage.getItem("jupwaFCMToken");

    if (savedToken === params.token) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append("fcm_token", params.token);

        await axios.post(params.urlSubscribe, formData, {
            headers: {"X-CSRF-Token": params.csrfToken},
        });

        localStorage.setItem("jupwaFCMToken", params.token);
    } catch (err) {
        jupwaNotification(err.message, "error");

        throw err;
    }
}

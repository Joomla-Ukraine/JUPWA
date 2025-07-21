"use strict";

import axios from 'axios';
import jupwaNotification from "../modules/notification";

export async function sendToken(params = {}) {
    try {
        const formData = new FormData();
        formData.append('fcm_token', params.token);

        await axios.post(params.urlSubscribe, formData, {
            headers: {'X-CSRF-Token': params.csrfToken}
        }).then(response => {
            localStorage.setItem('jupwaFCMToken', params.token);
        });
    } catch (err) {
        jupwaNotification(err.message, 'error');
    }
}
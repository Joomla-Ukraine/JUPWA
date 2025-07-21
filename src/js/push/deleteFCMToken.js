"use strict";

import axios from 'axios';
import jupwaNotification from "../modules/notification";

export async function deleteFCMToken(params = {}) {
    try {
        const formData = new FormData();
        formData.append('fcm_token', params.token);

        await axios.post(params.urlUnSubscribe, formData, {
            headers: {'X-CSRF-Token': params.csrfToken}
        }).then(response => {
            localStorage.removeItem('jupwaFCMToken');
        });
    } catch (err) {
        jupwaNotification(err.message, 'error');
    }
}
"use strict";

export function showMessage(message) {
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML += '<br>' + message;
}

export function showError(error) {
    const errorDiv = document.getElementById('error');

    errorDiv.innerHTML += '<br>' + error;
}

export function showNotification(payload) {
    const {title, body, image} = payload.notification;

    if (Notification.permission === 'granted') {
        new Notification(title, {
            body,
            icon: image || '/favicon.ico'
        });
    }

    UIkit.notification({
        message: `${title} — ${body}`,
        status: 'primary',
        pos: 'top-right',
        timeout: 8000
    });
}

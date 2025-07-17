"use strict";

import '../../scss/notification.scss';

export default function jupwaNotification(message, duration = 3000, position = 'top-right', styleClass = 'info') {

    const notification = document.createElement('div');

    notification.className = `jupwa-notification ${position} ${styleClass}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '1';
    }, 100);

    setTimeout(() => {
        notification.style.opacity = '0';

        setTimeout(() => {
            notification.remove();
        }, 500);
    }, duration);
}
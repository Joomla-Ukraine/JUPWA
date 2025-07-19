"use strict";

export async function registerServiceWorker(urlSW) {
    if (!('serviceWorker' in navigator)) {
        throw new Error('Service Worker не підтримується в цьому браузері.');
    }

    const registration = await navigator.serviceWorker.register(urlSW);
    console.log('Service Worker зареєстровано:', registration.scope);
    
    return registration;
}

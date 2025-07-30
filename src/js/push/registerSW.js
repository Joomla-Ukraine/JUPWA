"use strict";

let _registrationPromise = null;

export function registerSW(urlSW) {
    if (_registrationPromise) {
        return _registrationPromise;
    }

    if (!("serviceWorker" in navigator)) {
        return Promise.reject(new Error("Service Worker is not supported"));
    }

    _registrationPromise = navigator.serviceWorker.register(urlSW);

    return _registrationPromise;
}

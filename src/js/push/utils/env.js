"use strict";

export function getConfig(scriptId) {
    const node = document.getElementById(scriptId);
    if (!node || !node.textContent) {
        return null;
    }

    try {
        return JSON.parse(node.textContent);
    } catch (_) {
        return null;
    }
}

export function supportsSW() {
    return "serviceWorker" in navigator;
}

export function supportsPush() {
    return "PushManager" in window;
}

export function isIOSandNotStandalone() {
    const ua = navigator.userAgent || "";
    const iOS = /iPhone|iPad|iPod/.test(ua);
    const standalone = window.navigator.standalone === true;
    
    return iOS && !standalone;
}

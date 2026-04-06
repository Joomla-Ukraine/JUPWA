"use strict";

export const FCM_STORAGE_KEY = "jupwaFCMToken";
export const FCM_TTL_MINUTES = 24 * 60;

/**
 * @param {string} key
 * @param {any} value
 * @param {number} ttlInMinutes
 */
export function setItemWithExpiry(key, value, ttlInMinutes) {
    const now = new Date();
    const item = {
        value: value,
        expiry: now.getTime() + (ttlInMinutes * 60 * 1000),
    };

    localStorage.setItem(key, JSON.stringify(item));
}

/**
 * @param {string} key
 * @returns {any|null}
 */
export function getItemWithExpiry(key) {
    const itemStr = localStorage.getItem(key);

    if (!itemStr) {
        return null;
    }

    try {
        const item = JSON.parse(itemStr);
        const now = new Date();

        if (!item || typeof item.expiry !== "number" || !Number.isFinite(item.expiry) || now.getTime() > item.expiry) {
            localStorage.removeItem(key);

            return null;
        }

        return item.value;
    } catch (error) {
        return null;
    }
}

/**
 * @param {string} key
 */
export function removeItem(key) {
    localStorage.removeItem(key);
}
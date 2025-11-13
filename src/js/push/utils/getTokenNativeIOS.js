"use strict";

export default function getTokenNativeIOS(maxAttempts = 20, delay = 300) {
    return new Promise((resolve) => {
        let attempts = 0;

        const check = () => {
            const token = window.PushManager.getToken();

            if (token) {
                resolve(token);
            } else if (attempts < maxAttempts) {
                attempts++;
                setTimeout(check, delay);
            } else {
                resolve(null);
            }
        };
        
        check();
    });
}
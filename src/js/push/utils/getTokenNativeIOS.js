"use strict";

export default function getTokenNativeIOS(maxAttempts = 100, delay = 300) {
    return new Promise((resolve) => {
        let attempts = 0;
        let warned = false;

        const check = () => {
            const getToken = window.PushManager?.getToken;
            let token = null;

            if (typeof getToken === 'function') {
                try {
                    token = getToken();
                } catch (e) {
                    if (!warned) {
                        console.warn('[push] token is not available yet:', e);

                        warned = true;
                    }
                }
            }

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

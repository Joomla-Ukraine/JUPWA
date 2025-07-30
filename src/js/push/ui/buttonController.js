"use strict";

import {subscribe} from "../subscribe";
import {unsubscribe} from "../unsubscribe";

export function createButtonController(params) {
    const {
        subscribeButton,
        unsubscribeButton,
        widgetButton,
        lang,
        csrfToken,
        urlSW,
        urlSubscribe,
        urlUnSubscribe,
        messaging,
        firebaseConfig,
        swRegistration,
    } = params;

    if (subscribeButton) {
        subscribeButton.addEventListener(
            "click",
            () =>
                subscribe({
                    csrfToken,
                    urlSW,
                    urlSubscribe,
                    urlUnSubscribe,
                    widgetButton,
                    unsubscribeButton,
                    subscribeButton,
                    messaging,
                    firebaseConfig,
                    swRegistration,
                    lang,
                }),
            {
                once: false
            }
        );
    }

    if (unsubscribeButton) {
        unsubscribeButton.addEventListener(
            "click",
            () =>
                unsubscribe({
                    csrfToken,
                    urlSW,
                    urlSubscribe,
                    urlUnSubscribe,
                    widgetButton,
                    unsubscribeButton,
                    subscribeButton,
                    messaging,
                    firebaseConfig,
                    swRegistration,
                    lang,
                }),
            {
                once: false
            }
        );
    }

    return {subscribeButton, unsubscribeButton, widgetButton};
}

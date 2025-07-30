"use strict";

import {getApps, initializeApp} from "firebase/app";
import {getMessaging} from "firebase/messaging";

export function initFirebase(firebaseConfig) {
    const apps = getApps();
    const app = apps.length ? apps[0] : initializeApp(firebaseConfig);

    return getMessaging(app);
}

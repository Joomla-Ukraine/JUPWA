"use strict";

import Notify from 'simple-notify'

export default function jupwaNotification(message, duration = 6000, position = 'right top', status = 'info') {

    return new Notify({
        status: status,
        title: message,
        position: position,
        autotimeout: duration
    });
}
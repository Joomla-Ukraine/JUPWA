"use strict";

import Notify from 'simple-notify'

export default function jupwaNotification(message, status = 'info', duration = 6000, position = 'right top') {

    return new Notify({
        status: status,
        title: message,
        position: position,
        autotimeout: duration
    });
}
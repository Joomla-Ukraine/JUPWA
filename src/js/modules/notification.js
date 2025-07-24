"use strict";

import Notify from 'simple-notify'

export default function jupwaNotification(message, text = '', status = 'info', duration = 6000, position = 'right top') {

    return new Notify({
        status: status,
        title: message,
        text: text,
        position: position,
        autotimeout: duration
    });
}
"use strict";

export function createWidgetController() {
    const tpl = document.getElementById("jupwa-widget");
    if (!tpl || !(tpl instanceof HTMLTemplateElement)) {
        return null;
    }

    if (document.querySelector(".jupwa-widget")) {
        const widgetRoot = document.querySelector(".jupwa-widget");

        return {
            subscribeButton: widgetRoot.querySelector("#jupwa-subscribe-btn"),
            unsubscribeButton: widgetRoot.querySelector("#jupwa-unsubscribe-btn"),
            widgetButton: widgetRoot.querySelector("#jupwa-button"),
            widgetAlert: widgetRoot.querySelector("#jupwa-alert"),
        };
    }

    const fragment = tpl.content.cloneNode(true);
    document.body.appendChild(fragment);

    const widgetRoot = document.querySelector(".jupwa-widget");
    if (!widgetRoot) {
        return null;
    }

    const widgetButton = widgetRoot.querySelector("#jupwa-button");
    const widgetAlert = widgetRoot.querySelector("#jupwa-alert");
    const subscribeButton = widgetRoot.querySelector("#jupwa-subscribe-btn");
    const unsubscribeButton = widgetRoot.querySelector("#jupwa-unsubscribe-btn");

    if (widgetButton && widgetRoot) {
        const panel = widgetRoot.querySelector(".jupwa-panel");

        widgetButton.addEventListener("click", () => {
            if (!panel) {
                return;
            }

            panel.classList.toggle("jupwa-hidden");
            widgetButton.setAttribute(
                "aria-expanded",
                panel.classList.contains("jupwa-hidden") ? "false" : "true"
            );
        });
    }

    return {widgetButton, widgetAlert, subscribeButton, unsubscribeButton};
}

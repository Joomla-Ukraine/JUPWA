"use strict";

/**
 * @param {string} icon
 * @param {string} color
 */
export default function iOSSplash(icon, color = 'white') {

    if (typeof icon !== 'string' || icon.length === 0) {
        throw new Error('Invalid icon URL provided');
    }

    const deviceWidth = screen.width,
        deviceHeight = screen.height,
        pixelRatio = window.devicePixelRatio || 1,
        canvas = document.createElement('canvas'),
        canvas2 = document.createElement('canvas'),
        ctx = canvas.getContext('2d'),
        ctx2 = canvas2.getContext('2d'),
        iconImage = new Image();

    iconImage.onerror = function () {
        throw new Error('Failed to load icon image');
    };

    iconImage.src = icon;
    iconImage.onload = function () {
        const iconSizew = iconImage.width / (3 / pixelRatio),
            iconSizeh = iconImage.height / (3 / pixelRatio);

        canvas.width = deviceWidth * pixelRatio;
        canvas2.height = canvas.width;
        canvas.height = deviceHeight * pixelRatio;
        canvas2.width = canvas.height;
        ctx.fillStyle = color;
        ctx2.fillStyle = color;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx2.fillRect(0, 0, canvas2.width, canvas2.height);

        const x = (canvas.width - iconSizew) / 2,
            y = (canvas.height - iconSizeh) / 2,
            x2 = (canvas2.width - iconSizew) / 2,
            y2 = (canvas2.height - iconSizeh) / 2;

        ctx.drawImage(iconImage, x, y, iconSizew, iconSizeh);
        ctx2.drawImage(iconImage, x2, y2, iconSizew, iconSizeh);

        const imageDataURL = canvas.toDataURL('image/png'),
            imageDataURL2 = canvas2.toDataURL('image/png'),
            appleTouchStartupImageLink = document.createElement('link');

        appleTouchStartupImageLink.setAttribute('rel', 'apple-touch-startup-image');
        appleTouchStartupImageLink.setAttribute('media', "screen and (orientation: portrait)");
        appleTouchStartupImageLink.setAttribute('href', imageDataURL);

        document.head.appendChild(appleTouchStartupImageLink);

        const appleTouchStartupImageLink2 = document.createElement('link');

        appleTouchStartupImageLink2.setAttribute('rel', 'apple-touch-startup-image');
        appleTouchStartupImageLink2.setAttribute('media', "screen and (orientation: landscape)");
        appleTouchStartupImageLink2.setAttribute('href', imageDataURL2);

        document.head.appendChild(appleTouchStartupImageLink2);
    };
}
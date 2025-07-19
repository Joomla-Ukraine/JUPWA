"use strict";

export function getJupwaConfig() {
    const element = document.getElementById('pwapush');
    
    return JSON.parse(element.textContent);
}

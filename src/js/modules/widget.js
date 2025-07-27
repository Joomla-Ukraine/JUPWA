"use strict";

import template from '../html/template.html';

export class widget {
    constructor(translate) {
        const widget = this.render(translate);

        this.container = widget;
        this.toggleButton = widget.querySelector('.jupwa-widget-button');
        this.menu = widget.querySelector('.jupwa-widget-panel');

        this.isOpen = false;
        this.openedWithKeyboard = false;

        this.init();
    }

    render(translate) {
        const container = document.createElement('div');
        container.className = 'jupwa-widget';
        document.body.appendChild(container);

        container.innerHTML = template.replace(/\{\{\s*(\w+)\s*\}\}/g, (_, key) => {
            return translate[key] ?? `{{${key}}}`;
        });

        return container;
    }

    init() {
        this.toggleButton.addEventListener('click', () => this.toggle());
        this.toggleButton.addEventListener('keydown', (e) => this.onToggleKeydown(e));
        document.addEventListener('click', (e) => this.onClickOutside(e));
        document.addEventListener('keydown', (e) => this.onEscape(e));
    }

    toggle(open = !this.isOpen) {
        this.isOpen = open;

        this.update();
    }

    open() {
        this.toggle(true);
        if (this.openedWithKeyboard) {
            this.focusFirstItem();
        }
    }

    close() {
        this.isOpen = false;
        this.openedWithKeyboard = false;

        this.update();
    }

    update() {
        this.menu.classList.toggle('jupwa-widget-show', !this.isOpen);
        this.toggleButton.setAttribute('aria-expanded', String(this.isOpen));

        this.toggleButton.classList.toggle('text-on-surface-strong', this.isOpen || this.openedWithKeyboard);
        this.toggleButton.classList.toggle('text-on-surface', !(this.isOpen || this.openedWithKeyboard));
    }

    onToggleKeydown(e) {
        if ([' ', 'Enter', 'ArrowDown'].includes(e.key)) {
            e.preventDefault();

            this.openedWithKeyboard = true;
            this.open();
        }
    }

    onClickOutside(e) {
        if (!this.container.contains(e.target)) {
            this.close();
        }
    }

    onEscape(e) {
        if (e.key === 'Escape') {
            this.close();
        }
    }

    focusFirstItem() {
        this.focusItem(0);
    }

    focusItem(index) {
        this.items[index]?.focus();
    }
}
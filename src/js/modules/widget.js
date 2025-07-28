"use strict";

export class widget {
    constructor() {
        const tmpl = document.getElementById('jupwa-widget');

        if (!tmpl || !(tmpl instanceof HTMLTemplateElement)) {
            return;
        }

        const widgetContent = tmpl.content.cloneNode(true);

        this.container = document.createElement('div');
        this.container.classList.add('jupwa-widget-container');
        this.container.appendChild(widgetContent);
        document.body.appendChild(this.container);

        this.toggleButton = this.container.querySelector('.jupwa-button');
        this.menu = this.container.querySelector('.jupwa-panel');

        this.isOpen = false;
        this.openedWithKeyboard = false;

        this.init();
    }

    init() {
        if (!this.toggleButton || !this.menu) {
            return;
        }

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
    }

    close() {
        this.isOpen = false;
        this.openedWithKeyboard = false;
        this.update();
    }

    update() {
        this.menu.classList.toggle('jupwa-hidden', !this.isOpen);
        this.toggleButton.setAttribute('aria-expanded', String(this.isOpen));
        this.toggleButton.classList.toggle('jupwa-button-subscrided', this.isOpen || this.openedWithKeyboard);

        /*
        this.toggleButton.classList.toggle('text-on-surface', !(this.isOpen || this.openedWithKeyboard));*/
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
}
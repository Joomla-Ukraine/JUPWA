[![Stand With Ukraine](https://img.shields.io/badge/made_in-ukraine-ffd700.svg?labelColor=0057b7)](https://stand-with-ukraine.pp.ua)

# JUPWA: Free Progressive Web Application (PWA) for Joomla! with Capacitor Integration

![JUPWA](https://repository-images.githubusercontent.com/604152173/03fc8604-76de-4c5c-81ad-0b3246afad24)

## Donate: Buy Me a Coffee

* [Monobank (Google Pay, Apple Pay or Bank Card)](https://send.monobank.ua/jar/7u4x6vNRZJ)
* [PayPal](https://www.paypal.com/donate/?hosted_button_id=WQJNDPDPDMKP8)

## JUPWA Documentation

[Read the documentation](docs/index.md)

## Download

[JUPWA v 2.8.0](https://github.com/Joomla-Ukraine/JUPWA/releases/download/2.8.0/pkg_jupwa_v2.8.0.zip)

## Supported versions

* Joomla 5, 6
* PHP 8.1-8.5

## Integration

* Joomla! Content
* JoomShopping
* SEBLOD CCK

## Features

* Capacitor integration for native Android and iOS apps
* Firebase push notifications for guests and signed-in users
* Workbox-based Service Worker and offline fallback page
* Automatic app icon generation and PWA splash screens
* Configurable PWA manifest, install dialog, screenshots, and shortcuts
* Light and dark theme colors
* SEO metadata, Open Graph tags, and Schema.org
* Resource preconnect and preload, guest-page prerendering, and HTML compression
* Joomla cache controls with URL exclusions
* Android Digital Asset Links and related app configuration
* Custom Service Worker and offline page templates
* Plugin integrations

### PWA settings and installation

Configure the app name, description, language, start URL, scope, display mode, and orientation.
Add screenshots, menu-based shortcuts, and light/dark theme colors. An optional install dialog supports
remembering user choices and disabling the dialog in Chrome. Availability of manifest features depends on the browser.

### Loading and cache settings

Configure preconnect hints for external services and preload hints for selected resources. Optional Speculation Rules
prerender pages for guests, with a CSS class to exclude links. HTML compression is also available.
Joomla cache settings include URL exclusions, cache lifetime, and optional `Cache-Control` and `Expires` headers.

### Related apps and Android links

Configure related applications and Google Play/App Store identifiers. Generate `/.well-known/assetlinks.json`
using an Android package name and signing certificate SHA-256 fingerprints to associate the website with an Android app.
The Android app must also be configured to handle the website's links.

### Service Worker and offline mode

JUPWA uses [Workbox](https://developer.chrome.com/docs/workbox) for Service Worker routing and caching
of scripts, styles, fonts, and images. When navigation fails without a network connection, a cached offline
page is shown. This fallback requires a previous online visit and Service Worker installation; it does not
make all site features available offline. Both the Service Worker and offline page support template overrides.

### Icons and splash screens

Generate favicons, Apple touch icons, standard and maskable PWA icons, and shortcut icons from the configured
source image. JUPWA also prepares a splash icon and generates iOS PWA splash screens using the icon and
configured background color. Android PWA launch screens use the app manifest's icons and colors.
See [manifest icons](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps/Manifest/Reference/icons).
Native Capacitor splash screens are configured separately through the plugin below.

### Push notifications

Firebase Cloud Messaging (FCM) notifications work with web/PWA and native Capacitor apps:

* **Guests:** subscribe without an account — useful for news, articles, and public announcements.
* **Signed-in users:** subscriptions created while signed in are linked to the Joomla account — useful for
  user dashboards, order status changes, and delivery updates in online stores.

Allow subscriptions for everyone or restrict them to signed-in users in the push settings (`usepush_for_jusers`).
Notification permission and Firebase configuration are required; subscribers can unsubscribe.
Event-based notifications, such as order updates, require integration with the relevant component or application logic.

### Capacitor integration

[Capacitor](https://capacitorjs.com/docs) is a cross-platform native runtime that lets web apps run in native Android
and iOS containers and access device features through plugins.

Enable the integration in the JUPWA system plugin's **Capacitor** settings, then enable and configure the modules you
need. The corresponding native plugins must also be installed and configured in your Capacitor app.

| Plugin                                    | Integration and available JUPWA settings                                                                                                                                                   |
|-------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `@capacitor-firebase/messaging`           | Native push notifications; enable or disable the module and automatic registration.                                                                                                        |
| `@capawesome/capacitor-settings-launcher` | Opens the app's notification settings as part of the push integration; no separate JUPWA setting.                                                                                          |
| `@capacitor/splash-screen`                | Enable or disable the module; configure splash screen visibility and fade-out duration.                                                                                                    |
| `@capacitor/status-bar`                   | Enable or disable the module; configure style (`DEFAULT`, `LIGHT`, `DARK`), background color, WebView overlay, and visibility.                                                             |
| `@capacitor-community/in-app-review`      | Enable or disable native in-app review requests.                                                                                                                                           |
| `@capawesome/capacitor-app-update`        | Enable or disable the module; configure checks on startup, check delay, forced updates, and automatic store opening. Store links use the configured Android package name and App Store ID. |

## Browser support

* Chrome / Chrome for Android
* Edge
* Safari / Safari on iOS / Safari on iPadOS
* Firefox for Mobile
* Opera / Opera Mobile
* Samsung Internet
* Android Browser
* Firefox for Android

## Template overrides

Customize the offline page and Service Worker in your Joomla template:

| Original file | Template override |
| --- | --- |
| `/plugins/system/jupwa/tmpl/offline.php` | `/templates/my_template/html/jupwa/offline.php` |
| `/plugins/system/jupwa/tmpl/sw.php` | `/templates/my_template/html/jupwa/sw.php` |

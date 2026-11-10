# Overview and Requirements

[Documentation index](index.md)

## What JUPWA provides

JUPWA adds PWA configuration and supporting assets to a Joomla website. It can generate a web app manifest and icons, register a Service Worker, provide an offline fallback page, and add installation guidance. Optional features include Firebase push notifications, social metadata, structured data, resource loading hints, and integration with a separate Capacitor native application.

Installing JUPWA does not convert every Joomla feature into an offline feature. Forms, authentication, checkout, and server-side operations still depend on their own implementations and network access. Likewise, enabling Capacitor settings does not build or publish an Android or iOS application.

## Package components

| Plugin | Group | Responsibility |
| --- | --- | --- |
| `jupwa` | `system` | Core configuration, generated PWA files, page metadata, frontend assets, push configuration, and Capacitor settings. |
| `jupwapush` | `ajax` | Subscription storage and push subscription requests through Joomla `com_ajax`. |
| `content` | `jupwa` | Joomla article Open Graph and structured data integration. |
| `seblod` | `jupwa` | SEBLOD content field integration for metadata and structured data. |
| `jshopping` | `jupwa` | JoomShopping product metadata and structured data integration. |
| `jupwa_content` | `console` | Command-line article notifications. |

Enable the integration plugins appropriate to the components actually installed on your site. Their presence in the package does not install the corresponding third-party components.

## Joomla and PHP

The project README declares support for Joomla 5 and 6 and PHP 8.1–8.5. The package installer specifically rejects Joomla versions below **5.2**. Treat 5.2 as the installer minimum, then check the PHP requirements of your exact Joomla release and other extensions. The README's PHP range does not mean every Joomla/PHP pairing is valid.

The bundled library declares PHP 8.1 or later. Image generation uses the project's image-processing dependencies; check that the image driver and required PHP extensions are available in your hosting environment. A release installation uses bundled frontend files and PHP libraries: Node.js and a JavaScript build are not part of normal Joomla administration.

## Hosting prerequisites

Prepare the following before enabling PWA and push features:

- A publicly reachable HTTPS site for production PWA and web push use.
- Write access for the Joomla PHP process to generate files in the site root, `favicons/`, and, when configured, `.well-known/`.
- Public access to the generated manifest, worker, offline page, and icon URLs without a login redirect.
- Browser access to the external scripts referenced by the generated worker, subject to the site's network and Content Security Policy rules.
- Server access to Google's authentication and FCM endpoints when sending notifications.

Check whether another extension already manages `/sw.js`, `/manifest.webmanifest`, `/favicon.ico`, or `/.well-known/assetlinks.json`. JUPWA writes shared website paths, so ownership must be decided before enabling overlapping features.

## Browser and device support

Manifest rendering, installation UI, push permission, screenshots, shortcuts, and native device features have different support requirements. A browser displaying the site correctly may still lack one of these capabilities.

Test the actual browser/device combinations used by your audience. JUPWA's web push frontend specifically asks iOS users to open the installed Home Screen app before subscribing. Native Capacitor push follows a separate path.

## Choose a starting point

- For an installable website, follow [Getting Started](02-getting-started.md).
- For notifications, read [Push Notifications](07-push-notifications.md) after the basic PWA setup.
- For Android/iOS containers, read [Native Apps and Capacitor](08-capacitor.md).
- For custom PHP integration, read [Integrations and Development](09-integrations-and-development.md).

Source references: [package manifest](../pkg_jupwa.xml), [installer](../script.php), [library dependencies](../packages/plg_system_jupwa/libraries/composer.json).

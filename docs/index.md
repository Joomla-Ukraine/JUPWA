# JUPWA Documentation

User and developer documentation for **JUPWA 2.8.0**, a Joomla extension package for Progressive Web Apps, push notifications, social metadata, and Capacitor integration.

Start with **Getting Started** if you are configuring a site. Use **Integrations and Development** when extending JUPWA or working on its source code.

## Chapters

| Chapter | Description |
| --- | --- |
| [Overview and Requirements](01-overview.md) | Understand the features, included plugins, hosting requirements, and compatibility boundaries. |
| [Getting Started](02-getting-started.md) | Install the package, configure your first PWA, verify the result, and prepare for updates or removal. |
| [PWA Configuration](03-pwa-settings.md) | Configure app identity, manifest behavior, icons, colors, installation prompts, shortcuts, and screenshots. |
| [Service Worker and Offline Mode](04-service-worker.md) | Understand resource caching, offline fallback, generated files, and template overrides. |
| [Performance and Joomla Cache](05-performance-and-cache.md) | Configure connection hints, resource preloading, prerendering, HTML compression, and Joomla cache controls. |
| [SEO and Social Sharing](06-seo-and-social.md) | Configure metadata, Open Graph, Facebook properties, image selection, and structured data. |
| [Push Notifications](07-push-notifications.md) | Set up Firebase, manage web subscriptions, send notifications from PHP, and diagnose delivery problems. |
| [Native Apps and Capacitor](08-capacitor.md) | Connect a native application, configure device modules, and associate an Android app with your website. |
| [Integrations and Development](09-integrations-and-development.md) | Work with Joomla Content, SEBLOD, JoomShopping, console commands, extension events, and the build process. |
| [Recipes and Troubleshooting](10-recipes-and-troubleshooting.md) | Follow common setup recipes and investigate installation, cache, metadata, and push issues. |

## Reading conventions

- Setting identifiers such as `usepwa` refer to fields in the plugin configuration. Administrator labels can vary with the selected language.
- Website paths such as `/sw.js` refer to an installed Joomla site. Repository source links point to the code used to prepare this documentation.
- Example domains, user IDs, and tokens are placeholders. Replace them only in your own trusted environment.
- A feature being configured does not prove that every browser or operating system supports it.

## Version and verification

The documentation targets the release identified as 2.8.0 in the project README. It describes the checked-in source and configuration; it is not a report of successful installation or device testing. Differences between intended behavior and the current implementation are called out in the relevant chapters.

Before deploying, verify your own Joomla/PHP combination, browser behavior, Firebase delivery, and native application configuration on a staging site. External service interfaces may change; the relevant chapters link to official documentation.

[Project README](../README.md)

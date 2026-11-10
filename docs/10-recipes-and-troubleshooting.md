# Recipes and Troubleshooting

[Documentation index](index.md)

## Recipe: an installable information website

1. Install and enable the system plugin on staging.
2. Configure a square icon, app identity, theme colors, scope, and start URL.
3. Enable PWA and save.
4. Verify the manifest, icons, and worker registration.
5. Add installation guidance, screenshots, and menu shortcuts as needed.
6. Install on a target device and test startup, navigation, and offline fallback.
7. Enable the relevant content metadata integration and inspect representative pages.

Keep push and native modules disabled until this baseline works. See [Getting Started](02-getting-started.md) and [PWA Configuration](03-pwa-settings.md).

## Recipe: public push subscriptions

Configure Firebase and PWA, then enable push with `usepush_for_jusers` off. Subscribe using a dedicated guest test browser, inspect the server record, and send a single test notification to that installation.

For application code, `Console::tokens(0)` selects guest records only. It does not include authenticated subscribers. Decide which audience your announcement should reach before implementing a batch sender. The Content console command can send article notifications, but review its [current limitations](09-integrations-and-development.md) before using it.

## Recipe: notify a specific Joomla user

In your own trusted component event handler:

1. Confirm that the business event completed successfully.
2. Resolve the recipient's Joomla user ID on the server.
3. Retrieve that user's token records with `JUPWA\Console\Console::tokens($userId)`.
4. Call `JUPWA\Push\Push::send()` for each token with a suitable title, body, site domain, and authorized destination.
5. Handle failed sends and prevent duplicate event processing in your integration.

This is a development recipe, not an automatic order notification feature. Ensure that the destination page still enforces Joomla authorization. Do not send private order details merely because the URL requires login.

## Troubleshooting by symptom

| Symptom | Check first | Next step |
| --- | --- | --- |
| Installation is rejected | Joomla version and installer messages. | The installer requires Joomla 5.2 or later; check the full environment requirements. |
| No install option | HTTPS, manifest link, valid icon URLs, and platform install support. | Inspect browser manifest diagnostics and test the platform's own install flow. |
| Custom install dialog is absent | `usepwainstall`, loader request, browser detection, and remembered state. | The current custom dialog import is iOS-specific; see the PWA chapter. |
| Icons are missing or old | Source image, save errors, generated files, and response cache. | Save again, open individual icon URLs, then identify the stale cache layer. |
| Worker fails to register | Actual `/sw.js` response, scope, JavaScript errors, external imports, and CSP. | Resolve worker console errors before testing push or offline navigation. |
| Offline navigation fails | Successful prior installation, cached fallback, navigation errors, and deployment path. | Review the default template's overlapping navigation handling and root-path assumption. |
| Push widget is absent | Required Firebase fields, plugin status, `usepush_for_jusers`, excluded paths, and native detection. | Inspect the rendered configuration and whether custom buttons are present. |
| Push subscription fails | Permission, HTTPS, worker, VAPID mapping, CSRF/session, and AJAX response body. | Follow the push chapter; do not rely solely on HTTP 200 or a UI label. |
| Subscription exists but no message arrives | PHP send result, matching Firebase project, current token, and device permission. | Test one token in foreground/background and distinguish FCM acceptance from display. |
| Wrong notification URL | Sender argument order and generated absolute link. | The domain is the fourth argument and click link is the fifth. |
| Guest subscription is not tied to a user after login | Existing token record and local token state. | Existing guest records are not automatically reassigned by the subscribe handler. |
| Old HTML or scripts persist | Browser HTTP cache, worker caches, Joomla cache, CDN, and versioned asset URLs. | Inspect each layer independently; do not clear only Joomla cache and assume the worker changed. |
| Metadata is missing or wrong | Integration status, context dispatch, menu route, fallback data, and duplicate SEO output. | Inspect final source and the current Content integration caveats. |
| Native controls do nothing | Native bridge, installed plugins, module settings, template IDs, and native logs. | Rebuild/sync the native project and test on its target platform. |
| Force-update flag has no expected effect | Missing minimum-version value in rendered settings. | Review the documented update-module limitation before enabling force mode. |
| Android links open in the browser | Package name, installed app certificate, association URL, and app intent filters. | Run the platform's App Links verification procedure. |

## Collect useful diagnostics

Record the JUPWA release, Joomla/PHP versions, browser/OS, affected URL pattern, active settings, and minimal reproduction steps. Note whether the problem occurs as a guest, signed-in user, installed PWA, or native app.

Capture relevant response status/body, worker errors, and sanitized PHP errors. Remove service account JSON, private keys, full registration tokens, session cookies, and personal information before sharing a report. Report issues through the [project issue tracker](https://github.com/Joomla-Ukraine/JUPWA/issues).

## Frequently asked questions

### Does the offline page make the whole site available offline?

No. It is a fallback for failed navigation after successful worker installation. Interactive server operations require their own offline design.

### Do I need Node.js on the Joomla server?

Not for a normal release installation. Node.js and pnpm are development build tools; the release contains frontend assets.

### Does Capacitor integration create an APK or an iOS app?

No. Maintain and build a separate native project with its own dependencies, signing, and platform configuration.

### Does enabling push send order updates automatically?

No. Event-specific sending needs integration with the relevant component and server-side recipient selection.

### Does disabling the system plugin remove a worker already installed in browsers?

No. Browser registrations and caches have their own lifecycle. Verify a deliberate retirement procedure if removing PWA support.

### Can I edit the root manifest or worker directly?

Those files are generated and can be overwritten when settings are saved. Use configuration and supported template overrides instead.

### Can I uninstall and reinstall to reset notifications?

That removes the AJAX plugin's subscription and processing tables. Back up first and use a targeted test profile for browser-side troubleshooting.

## Glossary

| Term | Meaning in this documentation |
| --- | --- |
| PWA | A website using web app capabilities such as installation and a manifest. |
| Manifest | JSON describing app identity, launch behavior, icons, and related app information. |
| Service Worker | Browser-managed background script controlling requests and supporting push behavior within its scope. |
| Workbox | Library used by JUPWA's worker template for caching strategies and routing. |
| FCM | Firebase Cloud Messaging, used to route messages to registration tokens. |
| Registration token | Identifier for a particular messaging installation; not a Joomla login credential. |
| VAPID | Web push application-server identification; JUPWA's form provides a public web push key field. |
| Capacitor | Native runtime connecting web code with platform plugins. |
| Digital Asset Links | Website-to-Android-app association data published under `.well-known`. |
| Template override | A site-template copy of a JUPWA layout used instead of the bundled source template. |

## Verification status

These chapters were prepared from repository source and configuration, with official references for external setup. They do not certify end-to-end browser delivery, a native build, a release build, or installation on a live Joomla site. Use the chapter checklists to verify the deployment you actually operate.

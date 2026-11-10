# Getting Started

[Documentation index](index.md)

## Before installation

Use a staging copy first. Back up the website files and database, confirm the [requirements](01-overview.md), and identify any existing PWA extension or Service Worker.

Prepare a clear square app icon and decide on an app name, short name, description, start URL, and theme colors. Push and native integration can remain disabled during the first setup.

## Install the release package

1. Download the JUPWA release package linked from the [project README](../README.md).
2. Sign in to Joomla administration with extension installation privileges.
3. Open **System → Install → Extensions** and upload the package ZIP.
4. Review the installation messages and the list of installed plugins.
5. Open **System → Manage → Plugins**, find the system JUPWA plugin, and enable it.
6. Enable only the content integrations needed for your installed components.

The installer prepares `favicons/` and image directories under `images/jupwa/`. Installation success alone does not confirm that the PWA files have been generated or that the browser has registered a worker.

## Configure a first PWA

1. Open the system plugin and select a small app icon (`source_icon_sm`). Use a sufficiently large square image; a 512 × 512 PNG is a practical starting point.
2. Configure light theme and background colors. Add dark theme colors if needed.
3. Enable PWA (`usepwa`).
4. Set the manifest name, short name, description, and language.
5. For a site hosted at the domain root, use values such as:

   | Setting | Example |
   | --- | --- |
   | Scope | `https://example.com/` |
   | Start URL | `https://example.com/?utm_source=pwa` |
   | Manifest ID | `https://example.com/` |
   | Display | `standalone` |

6. Save the plugin and review all administrator messages.
7. Optionally enable installation guidance (`usepwainstall`) and save again.

Keep the manifest ID stable across later configuration changes so that app identity does not unintentionally change. For subdirectory installations, read the offline path limitation in [Service Worker and Offline Mode](04-service-worker.md) before deployment.

Saving the plugin generates icons and supporting files. The icon generator recreates `favicons/`; do not use that directory for unrelated uploads or hand-maintained files.

## Verify the result

Open the frontend over HTTPS in a fresh browser session:

1. Inspect the page head for a manifest link.
2. Open the linked manifest and check the app identity, start URL, scope, colors, and icon URLs.
3. Confirm that `/sw.js` and `/offline.php` return their expected content rather than an error page or login screen.
4. In browser developer tools, inspect the manifest and Service Worker registration. Resolve registration errors before testing offline behavior.
5. Reload while online, then simulate loss of connectivity and navigate to another page. Expect the offline fallback rather than a fully functional offline Joomla site.
6. Install the PWA using the browser or operating system's available installation flow. Launch it and verify the start URL, icon, and display mode.

The custom installation dialog is not proof of browser installability. Test the platform's actual installation flow.

## Updating

Back up and test the update on staging before installing it through Joomla's extension workflow. After updating, open and save the system plugin to regenerate configuration-dependent files. Recheck icons, manifest, Service Worker, push subscription, and template overrides.

The package installer clears the installed JUPWA JavaScript/CSS directories before replacing assets. The AJAX plugin update also changes its database tables, including removing the legacy `order_desc` column if present. Preserve any data needed by custom integrations before upgrading.

Do not manually rename generated bundles to resolve a missing asset. Check that the installed package contains the assets referenced by its system plugin.

## Disabling and uninstalling

Disabling a Joomla plugin stops its normal server-side hooks; it does not automatically remove an already installed browser Service Worker or its caches. Saving the system plugin with PWA disabled deletes the generated `sw.js` and `offline.php`, but browser-side cleanup must still be verified.

Before removing the package, back up subscription data if needed. The AJAX push plugin's uninstall routine **drops `#__jupwa_push_users` and `#__jupwa_push_orders`**. Do not uninstall it as a harmless way to reset a subscription.

After removal, inspect the site root and generated directories for remaining files. Remove only files owned by JUPWA, and account for existing client registrations during the retirement of the PWA.

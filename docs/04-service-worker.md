# Service Worker and Offline Mode

[Documentation index](index.md)

## Generated files and lifecycle

When the system plugin configuration is saved with PWA enabled, JUPWA generates `sw.js` and `offline.php` in the Joomla site root. The worker includes Workbox and, when push settings are present, Firebase messaging code. The frontend registers the worker using a URL containing a generated version value.

Saving with PWA disabled deletes those two files. This is also why web push should be configured together with PWA in the current implementation: push enabled on its own does not generate a standalone worker.

The worker calls `skipWaiting()` and `clientsClaim()`. New workers are intended to take control promptly rather than waiting for every old tab to close. Always check open tabs and cached resources after a deployment; a version query string does not itself clear every cache.

## Cache behavior

| Resource | Template strategy | Cache |
| --- | --- | --- |
| Scripts, styles, fonts | Stale while revalidate | `jupwa-assets` |
| Images | Cache first, up to 100 entries and 30 days | `jupwa-images` |
| Offline fallback | Added during installation | `jupwa-pages` |
| Navigation | Explicit network/fallback handlers plus a Workbox network-first route | `jupwa-pages` |

The asset strategy can return a cached resource while updating it in the background. Images can remain cached until expiration or eviction. The browser may also evict stored data earlier; cached resources are not permanent storage.

**Current implementation limitation:** the default template contains overlapping navigation handling, including two explicit fetch listeners and a Workbox navigation route. It should not be interpreted as a verified general-purpose offline page cache. Check the worker console for navigation handling errors, and test the fallback on your deployment. The documentation does not change this implementation.

## What offline mode means

The intended fallback is an offline page when a navigation request cannot reach the network. It requires a previous online visit, successful worker installation, and a cached fallback. It does not make article editing, login, checkout, or AJAX operations work without a server.

The default offline page provides a reload button and periodically checks connectivity. Its generated PHP template content is rendered into the output at generation time; changing the source override requires saving the plugin again.

**Subdirectory limitation:** the default worker refers to `/offline.php` at the domain root. If Joomla is installed under `/portal/`, verify the generated paths and use a template override appropriate to that deployment. Do not assume the default is subdirectory-safe.

## Customize through template overrides

Use the default site template's `html/jupwa/` directory:

| Original installed template | Override |
| --- | --- |
| `/plugins/system/jupwa/tmpl/offline.php` | `/templates/my_template/html/jupwa/offline.php` |
| `/plugins/system/jupwa/tmpl/sw.php` | `/templates/my_template/html/jupwa/sw.php` |

1. Copy the original source template into the override location.
2. Adjust the content or worker behavior in that copy.
3. Save the system plugin to regenerate root files.
4. Inspect the generated output and test worker installation online.
5. Test navigation offline and recovery when the connection returns.

The resolver looks up the **default site template**, not necessarily the template assigned to the current menu item. An offline page should keep its critical presentation self-contained so that it does not depend on uncached network resources.

## Diagnose updates and cache problems

In browser developer tools, inspect the active worker URL, scope, installation errors, and Cache Storage contents. Distinguish browser HTTP cache, Service Worker caches, Joomla cache, and any reverse proxy/CDN cache.

For a controlled test profile, unregister the worker and clear that site's storage, then revisit online. This also removes browser-side subscription state and can require subscribing again. Do not instruct all production users to clear site data as a routine update mechanism.

Joomla URL exclusions do not automatically become worker route exclusions. Review the worker separately before using custom caching rules on personalized pages.

Source references: [worker generator](../packages/plg_system_jupwa/libraries/src/Helpers/ServiceWorker.php), [default worker template](../packages/plg_system_jupwa/tmpl/sw.php), [override resolver](../packages/plg_system_jupwa/libraries/src/Utils/Util.php).

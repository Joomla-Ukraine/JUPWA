# Integrations and Development

[Documentation index](index.md)

## Content integrations

### Joomla Content

Enable the `jupwa` group Content plugin for standard articles. Its configuration includes description selection, menu-based Open Graph type selection, and menu assignments for article schemas. The system plugin provides global Open Graph and image defaults; the integration provides content-specific data.

Test both direct article URLs and menu routes. The current dispatcher passes content contexts to integration access handlers; the checked-in handlers return booleans while part of the dispatcher compares context values with those results. If article output is missing, inspect this dispatch path before assuming the form settings alone are incorrect. See the additional description caveat in [SEO and Social Sharing](06-seo-and-social.md).

### SEBLOD

Enable the ordinary SEBLOD integration only on a site using SEBLOD. Configure field mappings using actual field names from your content types:

| Identifier | Mapping |
| --- | --- |
| `seblod_title` | Content title field. |
| `seblod_intro` | Introductory text field. |
| `seblod_images` | Image field names, read as a comma-separated list. |
| `seblod_gallery` | Gallery field used by the integration's fallback extraction. |

The title and intro mappings support `[lang]` substitution with the current language's short code when multilingual mode is active. For example, use `title_[lang]` only if your actual fields follow that convention.

The integration reads SEBLOD content properties through the included adapter and handles specific stored image/gallery representations. Confirm those representations against your installed SEBLOD version. Validate a representative item for every mapped content type and language; field names alone do not guarantee that every storage layout is supported.

### JoomShopping

Enable the JoomShopping integration when the component is present. It supplies product Open Graph data and optional product structured data through `use_schema`. Check a product's title, description, image, price-related output, and review data against the page and your shop configuration.

This integration is for metadata. It does not establish automatic order-status push notifications; those require application-specific event handling and recipient selection.

## Architecture and data flow

The system plugin loads the bundled Composer autoloader for the `JUPWA\` namespace. During administrator configuration saves it generates icons, manifest data, Android associations, version metadata, and worker/offline output.

On frontend requests, Joomla hooks load assets and render metadata/settings. Browser entry points are:

| Source | Role |
| --- | --- |
| `src/js/index.js` | Installation guidance and iOS PWA splash support. |
| `src/js/push.js` | Browser/native subscription bridge and web notification UI. |
| `src/js/capacitor.js` | Native module loading from server-rendered settings. |

Push subscription requests go through Joomla `com_ajax` to the AJAX plugin. Server-side sending reads stored Firebase service account parameters and calls FCM HTTP v1. Content integrations run in Joomla's `jupwa` plugin group.

## Existing extension events

These names describe the current calls; they are not newly introduced APIs:

| Event | Arguments supplied by the system plugin | Purpose |
| --- | --- | --- |
| `onJUPWAAccess` | Component or content context | Integration access/routing selection. |
| `onJUPWASchema` | System plugin parameters | Component-level structured data. |
| `onJUPWAOG` | System plugin parameters | Component-level Open Graph. |
| `onJUPWAArticleSchema` | Article, system parameters, context | Article structured data. |
| `onJUPWAArticleOG` | Article, system parameters, context | Article Open Graph. |

Review the current dispatcher and an existing integration before adding handlers, especially the access-return mismatch noted above. Follow the Joomla event conventions appropriate to the deployed Joomla release; this documentation does not promise a new stable event abstraction.

For PHP notification sending, use the signature and result handling in [Push Notifications](07-push-notifications.md). Do not expose a send endpoint without implementing its own authorization and recipient checks.

## Subscription and notification storage

`#__jupwa_push_users` stores tokens, Joomla user association, and device information. `#__jupwa_push_orders` records notification processing information. `#__` denotes Joomla's configured table prefix.

Use Joomla database APIs and bind external values. A token is not proof of account ownership. Keep recipient selection server-side, account for multiple devices, and avoid including sensitive content in lock-screen notification bodies.

The AJAX installer creates/updates these tables, and its uninstall routine drops them. Read that routine before planning migrations or using uninstall/reinstall for troubleshooting.

## Content console command

The included Content console plugin registers `jupwa_content:run`. Enable that plugin and inspect the command before sending:

```sh
php cli/joomla.php list
php cli/joomla.php jupwa_content:run --help
```

The command accepts an `action` argument (`last` or `random`) and optional `--live_site` (`-s`), `--catid` (`-c`), and `--featured` (`-f`) values. It selects one published article, using the featured setting as a filter, and targets guest subscriptions through the console helper. It records processed content to avoid sending the same matching record again.

Example for a staging site with test subscribers only:

```sh
php cli/joomla.php jupwa_content:run last --live_site=https://example.com --catid=2 --featured=1
```

This command **sends real notifications and writes processing records**; it has no documented dry-run switch. Do not add it to cron before checking the selected article, audience, generated URL, and failure behavior.

Current implementation limitations include incomplete category projection in some option combinations, a fourth-argument call that passes the article URL as the sender's domain rather than its fifth link argument, and processing records that do not prove delivery to every token. The helper can also remove tokens after particular error codes. Test this workflow before scheduling it; a normal process exit is not a delivery report.

## Building from source

Normal administrators should install the release ZIP. For development, use the repository's existing tools and lock files:

```sh
pnpm install --frozen-lockfile
pnpm run build
```

The package scripts also provide `pnpm run dev` and `pnpm run icons`. Webpack builds the three entry points, extracts styles, produces chunks, and writes generated assets under the system plugin's media directory. It also rewrites the system plugin's asset-version property. Inspect the diff after running a build; do not hand-edit the generated bundles.

When PHP library dependencies need installing, use `composer install` from `packages/plg_system_jupwa/libraries/` with its existing lock file. Do not use a blanket dependency update as an installation step.

The release packaging file is `build.xml`, targeting JUPWA 2.8.0. It uses Phing-style tasks, assembles plugin archives, and regenerates `update.xml` with release metadata/checksum. With the project's PHP build tool available, the entry point is `phing -f build.xml`. The repository does not declare that tool in a root Composer manifest; use the maintainer's established toolchain rather than assuming it is installed.

Packaging cleans its build/temp directories and updates tracked release metadata. Run it in a suitable development checkout and review the resulting archive and diff. The frontend package version is not used here to identify the documented JUPWA release.

## Verification for code changes

Lint modified PHP files, run available project checks, and exercise the Joomla lifecycle hooks affected by the change. For frontend changes, build with the existing toolchain and test the affected browser/native flow. For packaging changes, install the resulting archive on staging and inspect generated resources. Documentation-only changes do not require regenerating bundles.

Source references: [system dispatcher](../packages/plg_system_jupwa/jupwa.php), [console command](../packages/plg_console_jupwa_content/src/Console/JUPWAContentCommand.php), [release build](../build.xml).

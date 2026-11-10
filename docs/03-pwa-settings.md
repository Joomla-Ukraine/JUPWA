# PWA Configuration

[Documentation index](index.md)

## App identity and manifest

The system plugin writes `manifest.webmanifest` in the Joomla site root when its configuration is saved. Use explicit values for important fields; a blank saved field is not always equivalent to a missing value and may not receive the helper's fallback.

| Setting | Identifier | Purpose / declared default |
| --- | --- | --- |
| Enable PWA | `usepwa` | Registers the worker when enabled; off by default. |
| App name | `manifest_name` | Full name used by supporting installation and launch surfaces. |
| Short name | `manifest_sname` | Compact app name for limited space. |
| Description | `manifest_desc` | Summary of the app. |
| Language | `manifest_lang` | Manifest language; `en` by default. |
| Text direction | `manifest_dir` | Direction setting; `auto` by default. |
| Scope | `manifest_scope` | Navigation boundary associated with the app. |
| Start URL | `manifest_start_url` | Address opened when the installed app starts. |
| App ID | `manifest_id` | Stable manifest identity. |
| Display | `manifest_display` | `standalone` by default. |
| Orientation | `manifest_orientation` | `any` by default. |
| Categories | `manifest_categories` | Categories describing the app. |

Keep the start URL within the intended scope. Test authentication redirects and multilingual routes from an installed app, rather than only opening the URL in a browser tab.

Advanced fields include `manifest_display_override`, `manifest_handle_links`, `manifest_launch_handler`, `manifest_scope_extensions`, and `manifest_edge_side_panel_width`. They describe browser-specific or evolving manifest behavior. Configuring them does not implement cross-origin permissions, force links into an app, or guarantee support. Leave them at their existing values unless you have a specific platform requirement and can verify the output.

Related application fields are covered in [Native Apps and Capacitor](08-capacitor.md).

## Icons

| Setting | Identifier | Use |
| --- | --- | --- |
| Small app logo | `source_icon_sm` | Main source for favicon, Apple icons, manifest icons, and splash icon generation. |
| Original logo | `source_icon` | Additional logo source used by the image helper. Verify the resulting output before relying on it for metadata. |
| SVG icon | `source_icon_svg` | SVG favicon reference. |
| Apply icon background | `manifest_icon_background_color` | Controls generated manifest icon background handling; off by default. |
| Mask/background color | `maskiconcolor` | Color used by relevant icon generation paths. |

Choose an image with a recognizable central mark and enough padding for platform masks. Inspect both standard and maskable outputs after saving. A transparent logo can look correct on a web page while becoming unreadable against an installation surface.

The generator recreates `favicons/` on Save/Apply. Outputs include `micon_*.png`, `maskable_*.png`, `icon_*.png`, Apple icons, `sicon_512.png`, shortcut icons, and `thumbs.json`. The manifest lists generated icon files that actually exist. Do not hand-edit these generated assets.

## Colors and splash screens

Configure `theme_color` and `background_color` for the manifest. `use_color_scheme` and `theme_color_dark` support light/dark page theme metadata. `ioscolor` supplies the background used for iOS PWA splash generation.

The frontend generates iOS splash resources using the prepared splash icon. In the current implementation the main frontend loader is included through `usepwainstall`; when testing splash generation, verify that this loader and the `pwaicons` configuration are present. Android PWA launch appearance depends on the manifest and the platform.

Native Capacitor splash screens are separate from PWA launch screens and have their own [module settings](08-capacitor.md).

## Installation guidance

| Setting | Identifier | Declared default |
| --- | --- | --- |
| Enable dialog markup and loader | `usepwainstall` | Off |
| Remember choices in local storage | `pwainstall_localstorage` | On |
| Disable Chrome handling in the dialog | `pwainstall_disablechrome` | Off |

The plugin inserts a `pwa-install` element referring to the generated manifest. The current JavaScript entry point imports the dialog library only for detected iOS/iPadOS environments. Do not assume that enabling the switch creates the same custom dialog in every browser. Other browsers may provide their own install UI.

When testing repeated prompts, account for local storage, previous dismissal, and whether the app is already installed.

## Shortcuts

Add rows in `shortcuts`, selecting a Joomla menu item, language, and icon. The generator creates a 96 × 96 shortcut icon, and the manifest helper builds the destination from the menu path and language selection.

Save and inspect each generated shortcut URL. Language prefixes are derived from the language code, so custom language routing, aliases, and subdirectory installations need particular attention. Shortcuts should lead to pages the intended user can access; adding a shortcut does not bypass Joomla access checks.

## Screenshots

Add local image files through the `screenshots` rows. The helper reads their dimensions and MIME types and assigns a wide or narrow form factor. Missing local images are omitted.

Use screenshots that reflect the current interface and verify every generated URL. Their presence in the manifest does not guarantee that a browser will display them in its installation UI.

Source references: [configuration fields](../packages/plg_system_jupwa/jupwa.xml), [manifest generator](../packages/plg_system_jupwa/libraries/src/Helpers/Manifest.php), [icon generator](../packages/plg_system_jupwa/libraries/src/Thumbs/Render.php).

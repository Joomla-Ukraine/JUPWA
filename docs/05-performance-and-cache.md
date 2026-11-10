# Performance and Joomla Cache

[Documentation index](index.md)

## Configure one optimization at a time

Start with a baseline of representative public pages, then enable one setting and compare network activity and rendering. These features change loading or caching behavior; they do not establish a measured performance improvement on every site.

## Preconnect

The plugin offers predefined external service switches and custom `preconnect` rows containing a URL. Add origins that the page actually uses early, such as a font or asset host. Do not add a long list of unused services.

Inspect the resulting `<link rel="preconnect">` elements and the Network panel. A connection hint does not load the resource itself and does not replace consent or application logic for a third-party service.

## Preload

Each `preloads` row supports:

| Field | Purpose |
| --- | --- |
| `url` | Resource URL to preload. |
| `as` | Resource destination, such as font, style, or script. |
| `type` | Optional MIME type. |
| `crossorigin` | Cross-origin request mode when needed by the resource. |
| `media` | Optional media condition. |

For example, preload a critical WOFF2 font using the correct URL, destination, MIME type, and cross-origin behavior matching its later use. Verify that the preload is consumed rather than followed by a duplicate request. Preloading many large resources can compete with more important page content.

## Guest-page prerendering

`use_speculationrules` is off by default. When enabled, the helper emits document-based prerender rules for guests with moderate eagerness. It does not emit these rules for signed-in users.

Set `speculationrules_class` to a class such as `no-prerender` and apply that class to links you want excluded:

```html
<a class="no-prerender" href="/special-action">Open action page</a>
```

The helper strips dots from the configured class before constructing the selector. Use one class name. Test the generated JSON and the behavior of your target browser. Pay particular attention to URLs that trigger side effects or sensitive workflows when visited.

## HTML compression

`htmlcompress` is off by default. It compresses the rendered HTML response and attempts to preserve code, preformatted text, textarea, and script blocks during processing.

After enabling it, test forms, inline JavaScript, formatted content, and template widgets. If a problem appears only after enabling compression, compare the actual response and browser console before changing unrelated settings.

## Joomla cache controls

These controls are separate from [Service Worker caches](04-service-worker.md).

| Setting | Declared default | Behavior |
| --- | --- | --- |
| `joomla_cache` | Off | Enables JUPWA's Joomla cache handling. |
| `definitions` | `option=com_content` | Matching request definitions temporarily disable Joomla caching. |
| `cachecontrol` | Off | Adds `Cache-Control: public, max-age=…`. |
| `expires` | Off | Adds an expiration header. |
| `expirestime` | `900` | Configured HTTP header lifetime in seconds; this is not the worker cache lifetime. |
| `cache_exclusion` | Empty | URL fragments for skipping the final cache/header handling. |
| `reenable_afterdispatch` | Off | Present in the form, but no corresponding use was found in the system plugin; do not rely on it to restore caching. |

### Request definitions

Enter one query-style definition per line. Conditions within a line must all match; any matching line triggers the rule. A `?` value matches a parameter that is present:

```text
option=com_content&view=article
option=com_example&task=?
```

These are request parameter conditions, not URL regular expressions. The `com_example` line illustrates the syntax and does not refer to a bundled component.

### Exclusions and personalized responses

The final cache exclusion check compares URL fragments with the current URL. The current parser splits `cache_exclusion` on CRLF line endings; verify multiple saved entries on your Joomla installation rather than assuming every line-ending format works.

The system plugin also skips many hooks for paths containing `/account` and for path fragments in `push_exclude`. Despite its name, `push_exclude` affects more than the push widget. These skips do not remove an already controlling worker or change CDN rules.

The route hook attempts to disable Joomla caching for signed-in users. However, the later header handler can still emit a public cache header when its switches are enabled. Do not treat that route hook as proof that private pages cannot be cached. Keep public headers disabled until authenticated responses and upstream caches have been checked on staging.

## Verification checklist

Inspect both response HTML and headers for a guest and an authenticated test user. Test excluded URLs, forms, and logout. Compare a first load, a repeated load, and a worker-controlled load. Verify each cache layer independently.

Source references: [resource hint helper](../packages/plg_system_jupwa/libraries/src/Helpers/META.php), [system cache handling](../packages/plg_system_jupwa/jupwa.php).

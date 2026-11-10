# SEO and Social Sharing

[Documentation index](index.md)

## Separate page metadata from app metadata

The manifest describes the installed application. Open Graph and Schema.org describe individual pages and content. Changing the app description does not automatically replace each article's description.

Configure your component integration as well as the system plugin. Templates and other SEO extensions may already emit the same properties; inspect the final page before enabling overlapping generators.

## Basic metadata controls

| Identifier | Declared default | Effect in the rendered HTML |
| --- | --- | --- |
| `jgenerator` | On | Removes matching Joomla generator metadata. |
| `keywords` | On | Removes matching keywords metadata. |
| `jauthor` | On | Replaces a matching author meta value with the site name. |

These controls operate on the rendered response using pattern matching. Check the output with your actual template instead of assuming they normalize every possible tag format.

## Open Graph

Enable `og` in the system plugin. It is off by default. Depending on the active content integration, JUPWA can emit locale, page type, title, description, URL, site name, image, and image dimensions. Article integrations can add publication/modification times, section, and tags.

The generic fallback starts from Joomla's document title and description. An active non-default menu item can replace the fallback title with its menu title. Component integration paths use their own content data, so there is no single global precedence rule that applies to every page type.

For standard Joomla articles, enable the Content integration. Its `og_type_website` and `og_website_menus` settings allow selected menu contexts to use a website type instead of the usual article type. Test article pages reached through different menu items, because the current `Itemid` also affects structured data selection.

### Description caveat in the current Content integration

The Content plugin reads the article metadata and intro text, but its current `core()` implementation replaces the description with the article title when `metadesc` is non-empty. Therefore, do not assume that enabling `usemeta` guarantees the article meta description will appear in Open Graph. Verify the generated `og:description`. This is a documented implementation limitation, not a change made by this documentation.

## Social image selection

The shared article image helper checks these sources in order:

1. A recognized gallery expression in the content.
2. The article's intro image, then full-text image.
3. The first HTML image in the content.
4. A recognized YouTube URL's thumbnail.
5. The configured fallback image strategy.

Recognized but unusable content can still require investigation; this sequence is not a guarantee that every invalid gallery or remote image automatically falls through successfully.

Fallback selection uses `selectimg`, `imagemain`, and `image`. In random mode, a file from `images/jupwa/images/` takes priority when available. Otherwise the helper checks the configured main image, then the other configured image, and finally the bundled JUPWA image.

Use publicly reachable images. Local image dimensions can be detected from disk; a remote image may not receive width and height metadata. Inspect the actual `og:image` URL and open it without signing in.

## Facebook properties

The form contains `fb`, `fbpage`, `fbapp`, and repeatable `fbadmin` fields. The helper emits publisher, app ID, and administrator properties from their configured values. In the current helper, output is not consistently gated by the `fb` switch, so inspect the generated markup rather than relying on the switch alone.

Do not put access tokens into these fields. Page URLs and application/administrator identifiers are different from credentials.

## Structured data

The Content and SEBLOD integrations provide menu-based configuration for Article, NewsArticle, and BlogPosting schemas and their person settings. The relevant field groups are `schema_article`, `schema_news_article`, and `schema_blogposting` with corresponding person fields.

1. Choose the schema appropriate to the content.
2. Select the intended menu items in the integration plugin.
3. Configure the associated person settings.
4. Open a representative article through those menu routes.
5. Inspect the JSON-LD and confirm that titles, descriptions, images, dates, and URLs represent the visible page.

Avoid unintentionally selecting the same menu context for multiple article types. For JoomShopping, the integration has a separate `use_schema` setting for product output. Metadata generation does not guarantee indexing or search result enhancements.

## Verify sharing output

Test a home page, an article with and without an explicit image, a category/menu page, and a product page if applicable. Compare the response source for duplicate tags and incorrect URLs. Check multilingual routes and unauthenticated access. A social network may cache an older preview even after the current HTML has changed.

Source references: [Open Graph helper](../packages/plg_system_jupwa/libraries/src/Helpers/OG.php), [image selection](../packages/plg_system_jupwa/libraries/src/Helpers/Images.php), [Content integration](../packages/plg_jupwa_content/content.php).

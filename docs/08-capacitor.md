# Native Apps and Capacitor

[Documentation index](index.md)

## Responsibilities

JUPWA supplies website-side configuration and JavaScript adapters. You maintain a separate Capacitor application containing Android/iOS projects, native dependencies, signing configuration, platform permissions, Firebase configuration, and store releases.

Follow the [Capacitor installation workflow](https://capacitorjs.com/docs/getting-started) in that native project. Installing packages in the Joomla website repository does not install them into your native container. Select native plugin versions compatible with the Capacitor major version used by your app.

## Enable modules

Enable `usecapacitor` in the system plugin. The page receives a `jupwa-app-setting` JSON block and the application loader. The loader checks the native platform before initializing the device modules.

| Module | Native package | JUPWA settings |
| --- | --- | --- |
| Push | `@capacitor-firebase/messaging` | `plgs_push`, `plgs_push_autoRegister` |
| Notification settings | `@capawesome/capacitor-settings-launcher` | Used by push settings navigation; no separate enable switch. |
| Splash screen | `@capacitor/splash-screen` | `plg_splashScreen`, visibility, fade-out duration. |
| Status bar | `@capacitor/status-bar` | `plg_statusBar`, style, background color, WebView overlay, visibility. |
| In-app review | `@capacitor-community/in-app-review` | `plg_appReview` |

Use each provider's installation instructions in the native app, then synchronize and rebuild it. Verify enabled modules on a real native build; an ordinary browser does not exercise the native bridge.

## Native push

Complete the server-side [push configuration](07-push-notifications.md), enable the native push module, and configure the native app's Firebase messaging integration for each platform. An iOS native app needs its own Apple/Firebase push setup; the web app configuration alone is insufficient. Follow the [messaging plugin documentation](https://capawesome.io/plugins/firebase/cloud-messaging/) for the native configuration matching your installed version.

The web subscription widget is removed in a detected native environment. To provide native controls in your Joomla template, the current module recognizes this minimal menu:

```html
<div id="jupwa-capacitor-push-menu" hidden>
    <label>
        <input type="checkbox" name="push-notifications">
        Enable notifications
    </label>
    <button type="button" data-push-action="settings">Notification settings</button>
</div>
```

The module also exposes `window.jupwaCapacitorPush` after initialization, including `enable()`, `disable()`, `getToken()`, and `refreshStatus()`. The push frontend connects token and disable callbacks to Joomla's subscription endpoints. A native token alone does not establish that it was stored successfully on the server.

If automatic registration is enabled, initialization can request permission and obtain a token. Test permission denial, returning from OS settings, restarting the app, and unsubscribing. Avoid enabling repeated prompts without checking the actual user experience.

## Splash screen and status bar

The splash module's `visible` option selects showing the native splash with automatic hiding disabled or hiding it with the configured fade-out duration. Keeping it visible therefore requires an intentional later hide path in the app.

Choose `DEFAULT`, `LIGHT`, or `DARK` for the status bar style and check text contrast against the page. Test safe areas and WebView overlay behavior on your target OS versions. Background and overlay behavior are platform-dependent; Joomla settings do not override native OS restrictions.

## In-app review

When the module initializes on a supported native platform, it exposes `window.appReview` and unhides elements with `.jupwa-app-appreview`. A template can provide a button:

```html
<button type="button" class="jupwa-app-appreview" hidden
        onclick="window.appReview?.()">
    Rate this app
</button>
```

The module exposes the request function rather than automatically requesting a review on every load. A successful request does not guarantee that the operating system displays a review dialog.

## Related applications and store metadata

Configure `related_apps` with platform, URL, and optional ID values, and review `prefer_related_applications`. These values enter the web manifest. `googleplay` and `appstore` also provide app-related page metadata. They do not publish an application or implement universal links.

## Android Digital Asset Links

1. Enable `use_assetlinks` and ensure the manifest has been generated.
2. Set `assetlinks_package_name` to the actual Android application ID.
3. Set `assetlinks_sha256` to the signing certificate SHA-256 fingerprint. Multiple fingerprints are comma-separated.
4. Save and inspect `https://example.com/.well-known/assetlinks.json`.
5. Configure the native Android application's link intent filters and verify links on a device.

Use the certificate corresponding to the installed app, including the correct distribution signing certificate. JUPWA generates the website association; the Android app must implement the other side. See [Android's App Links verification guide](https://developer.android.com/training/app-links/verify-applinks).

## Native verification checklist

Check a clean installation, permission rejection, offline launch, foreground/background notification delivery, notification links, status bar layout, splash dismissal, and review controls on both target platforms. Review native logs as well as the website console.

Source references: [settings renderer](../packages/plg_system_jupwa/libraries/src/Capacitor/Capacitor.php), [module loader](../src/js/capacitor.js).

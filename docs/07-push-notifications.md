# Push Notifications

[Documentation index](index.md)

## Delivery paths

JUPWA uses Firebase Cloud Messaging (FCM) for web/PWA and native Capacitor notifications. Web subscriptions use browser notification permission, a Service Worker, and an FCM registration token. Native applications obtain their token through a Capacitor Firebase plugin.

The AJAX plugin stores subscriptions in `#__jupwa_push_users`. New subscriptions created while signed in receive the current Joomla user ID; guests use `0`. Tokens represent browser/app installations, so one account can have multiple tokens.

The existing-token check does not relink an already stored guest token to a user after login. Do not assume automatic subscription ownership migration between guest and authenticated sessions.

## Prepare Firebase

Use the [official Firebase web setup guide](https://firebase.google.com/docs/cloud-messaging/web/get-started) for project setup and web credentials:

1. Create or select a Firebase project and register its web application.
2. Obtain the web app configuration values.
3. In Cloud Messaging settings, generate or select the public Web Push certificate key used for VAPID.
4. Ensure the FCM HTTP v1 API is available for the project.
5. Obtain service account JSON credentials for server-side sending and keep them private.

Firebase documents service account authorization in its [HTTP v1 sending guide](https://firebase.google.com/docs/cloud-messaging/send/v1-api). JUPWA's sender specifically reads JSON from the plugin parameter; setting an environment variable alone does not replace that parameter in the current implementation.

## Configure Joomla

Enable PWA first and verify that its worker registers. Then configure the system plugin:

| Field | Identifier | Use |
| --- | --- | --- |
| Enable push | `usepush` | Off by default. |
| Signed-in users only | `usepush_for_jusers` | Off by default; controls frontend availability for guests. |
| Firebase API key | `apiKey` | Web app configuration. |
| Project ID | `projectId` | Firebase project identity. |
| Messaging sender ID | `messagingSenderId` | Web app messaging configuration. |
| App ID | `appId` | Firebase web app identifier. |
| Public web push key | `vapidKey_wp` | Required by the configuration completeness check. |
| Service account JSON | `firebaseServiceAccount` | Private server-side credentials for sending. |
| Widget information | `push_widget_info` | Repeatable explanatory text shown in the widget. |
| Excluded paths | `push_exclude` | Path fragments; also affects other system plugin hooks. |

The form also includes `privateKey_wp`, `measurementId`, `authDomain`, and `storageBucket`. They are not all prerequisites in the current push availability check; do not confuse a VAPID private key with the service account JSON required by the sender.

Save, review the configuration messages, and confirm that the AJAX JUPWA push plugin is enabled. Saving with push enabled attempts to enable that plugin automatically.

**VAPID mapping caveat:** the form and completeness check use `vapidKey_wp`, while the frontend configuration renderer reads `vapidKey`. If subscription token creation fails or uses an unexpected key, inspect the `jupwa-push-setting` JSON. Do not treat a populated form field as proof that the key reached Firebase's client API.

The service account JSON belongs only in trusted server-side configuration. Do not upload it to a public directory or include it in screenshots, documentation, browser code, or issue reports. Database backups containing plugin parameters also contain this credential.

## Subscription UI

The default frontend provides a subscription widget. A template can instead supply both buttons below; the frontend uses them when both IDs are present:

```html
<button type="button" id="jupwa-subscribe-btn">Enable notifications</button>
<button type="button" id="jupwa-unsubscribe-btn" hidden>Disable notifications</button>
```

Keep these IDs unique. The default widget template is `plugins/system/jupwa/tmpl/widget.php`; the common template resolver can use `templates/my_template/html/jupwa/widget.php` from the default site template.

On iOS, test from the installed Home Screen app. If notification permission was denied, the user may need to change browser/OS settings before another subscription attempt can succeed. The Joomla switch cannot override a denied platform permission.

Unsubscribing attempts to delete the web Firebase token and the server-side subscription. The UI changes before all asynchronous steps finish, so verify completion in the Network panel and subscription storage when diagnosing failures.

## Send from trusted PHP code

After JUPWA's classes are loaded inside Joomla, the existing sender accepts one token:

```php
// $token comes from trusted subscription storage, not a public request parameter.
$result = \JUPWA\Push\Push::send(
    $token,
    'A new article is available',
    'Open the website to read it.',
    'https://example.com',
    'https://example.com/news/example'
);

if (!$result['success']) {
    // Handle the failure in your integration without exposing credentials or tokens.
}
```

The parameter order is **token, title, body, domain, link**. The domain is used for generated icon URLs; the link becomes the click destination. The return array includes `success` and `code`, with `data` on success or `error` on failure. A successful FCM response means the service accepted the request, not that a person saw it.

`JUPWA\Console\Console::tokens($userId)` retrieves token records for an exact user ID. Passing `0` selects guests, not all subscribers. User-specific event notifications require your own component logic to identify the authorized recipient and call the sender for that user's tokens.

## AJAX interface and limits

The frontend uses POST requests with `fcm_token` to Joomla `com_ajax` endpoints named `JUPWAPushSubscribe` and `JUPWAPushUnsubscribe`; `JUPWAPushCheck` also exists. The frontend sends the Joomla token in `X-CSRF-Token`, and the backend calls `Session::checkToken()`. Preserve the current session and inspect actual Joomla responses when integrating; these endpoints are not a standalone public messaging API.

The signed-in-only option gates frontend rendering. The AJAX handler does not itself read that option, so it must not be treated as a complete server-side authorization policy. New subscription handling also attempts a welcome message before storing the token, without using the send result as proof of delivery.

## Test delivery

1. Subscribe from a dedicated test browser and inspect the POST response, not just its HTTP status.
2. Confirm the expected subscription record and user association.
3. Send one controlled message to that token.
4. Check delivery in foreground and background, then open its destination.
5. Unsubscribe and verify both the browser result and database state.
6. Repeat for a guest, a signed-in user, a denied permission, and each supported device type.

For failures, check the worker console, Firebase project consistency, service account authorization, VAPID mapping, HTTPS, browser permission, and outbound server connectivity. Never paste full token values or credentials into public diagnostics.

Source reference: [PHP sender and frontend configuration](../packages/plg_system_jupwa/libraries/src/Push/Push.php), [subscription handler](../packages/plg_ajax_jupwapush/src/Extension/Push.php).

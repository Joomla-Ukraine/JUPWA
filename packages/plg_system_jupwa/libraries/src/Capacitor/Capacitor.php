<?php
/**
 * JUPWA plugin
 *
 * @version       2.x
 * @package       JUPWA\Capacitor
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Capacitor;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class Capacitor
{
    /**
     *
     * @param array $option
     *
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    public static function isCapacitor(array $option = []): bool
    {
        $params = $option['params'] ?? null;

        return !(!$params || $params->get('usecapacitor') != 1);
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public static function render(array $option = []): void
    {
        $params = $option['params'] ?? null;

        if (!$params || $params->get('usecapacitor') != 1) {
            return;
        }

        $doc = Factory::getApplication()->getDocument();

        $appSettings = [
            'modules' => [],
        ];
        $appSettings['language'] = [];

        if ((int)$params->get('plgs_push') === 1) {
            $appSettings['modules']['push'] = [
                'autoRegister' => filter_var(
                    $params->get('plgs_push_autoRegister', 'false'),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ];

            $appSettings['language'] += [
                'permissionGranted' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_GRANTED'),
                'permissionDenied' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_DENIED'),
                'permissionPrompt' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_PROMPT'),
                'permissionPromptRationale' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_PROMPT_RATIONALE'),
                'permissionUnknown' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_UNKNOWN'),
                'subscriptionActive' => Text::_('PLG_JUPWA_CAP_PUSH_SUBSCRIPTION_ACTIVE'),
                'subscriptionInactive' => Text::_('PLG_JUPWA_CAP_PUSH_SUBSCRIPTION_INACTIVE'),
                'permissionNotGranted' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_NOT_GRANTED'),
                'tokenTransferFailed' => Text::_('PLG_JUPWA_CAP_PUSH_TOKEN_TRANSFER_FAILED'),
                'enableSuccess' => Text::_('PLG_JUPWA_CAP_PUSH_ENABLE_SUCCESS'),
                'enableFailed' => Text::_('PLG_JUPWA_CAP_PUSH_ENABLE_FAILED'),
                'unsubscribeFailed' => Text::_('PLG_JUPWA_CAP_PUSH_UNSUBSCRIBE_FAILED'),
                'disableSuccess' => Text::_('PLG_JUPWA_CAP_PUSH_DISABLE_SUCCESS'),
                'disableFailed' => Text::_('PLG_JUPWA_CAP_PUSH_DISABLE_FAILED'),
                'subscriptionUpdateFailed' => Text::_('PLG_JUPWA_CAP_PUSH_SUBSCRIPTION_UPDATE_FAILED'),
                'permissionCheckUnknown' => Text::_('PLG_JUPWA_CAP_PUSH_PERMISSION_CHECK_UNKNOWN'),
                'settingsOpenFailed' => Text::_('PLG_JUPWA_CAP_PUSH_SETTINGS_OPEN_FAILED'),
            ];
        }

        if ((int)$params->get('plg_appReview', 1) === 1) {
            $appSettings['modules']['appReview'] = filter_var(
                true,
                FILTER_VALIDATE_BOOLEAN
            );
        }

        if ((int)$params->get('plg_splashScreen') === 1) {
            $appSettings['modules']['splashScreen'] = [
                'visible' => filter_var(
                    $params->get('plg_splashScreen_visible', 'false'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'fadeOutDuration' => (int)$params->get('plg_splashScreen_fadeOutDuration'),
            ];
        }

        if ((int)$params->get('plg_statusBar') === 1) {
            $appSettings['modules']['statusBar'] = [
                'statusBarStyle' => (string)$params->get('plg_statusBar_statusBarStyle', 'DEFAULT'),
                'backgroundColor' => $params->get('plg_statusBar_backgroundColor'),
                'overlaysWebView' => filter_var(
                    $params->get('plg_statusBar_overlaysWebView', 'false'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'visible' => filter_var(
                    $params->get('plg_statusBar_visible', 'true'),
                    FILTER_VALIDATE_BOOLEAN
                ),
            ];
        }

        $json = json_encode($appSettings);

        $doc->addCustomTag(
            '<script id="jupwa-app-setting" type="application/json">'.$json.'</script>'
        );
    }
}

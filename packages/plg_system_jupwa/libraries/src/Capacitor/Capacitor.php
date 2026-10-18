<?php
/**
 * @package     JUPWA\Push
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

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

        if ((int)$params->get('plg_appUpdate') === 1) {
            $appSettings['modules']['appUpdate'] = [
                'checkOnStart' => filter_var(
                    $params->get('plg_appUpdate_checkOnStart', 'true'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'delayMs' => (int)$params->get('plg_appUpdate_delayMs', 2000),
                'force' => filter_var(
                    $params->get('plg_appUpdate_force', 'false'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'autoOpenStore' => filter_var(
                    $params->get('plg_appUpdate_autoOpenStore', 'true'),
                    FILTER_VALIDATE_BOOLEAN
                ),
                'storeUrlAndroid' => 'https://play.google.com/store/apps/details?id='.$params->get(
                        'assetlinks_package_name'
                    ),
                'storeUrlIos' => 'https://apps.apple.com/app/id'.$params->get('appstore'),
            ];

            $appSettings['language'] += [
                'updateAvailableMessage' => Text::_('PLG_JUPWA_CAP_APP_UPDATE_AVAILABLE_MESSAGE'),
                'forceUpdateTitle' => Text::_('PLG_JUPWA_CAP_FORCE_UPDATE_TITLE'),
                'forceUpdateMessage' => Text::_('PLG_JUPWA_CAP_FORCE_UPDATE_MESSAGE'),
                'forceUpdateButton' => Text::_('PLG_JUPWA_CAP_FORCE_UPDATE_BUTTON'),
            ];
        }

        $json = json_encode($appSettings);

        $doc->addCustomTag(
            '<script id="jupwa-app-setting" type="application/json">'.$json.'</script>'
        );
    }
}

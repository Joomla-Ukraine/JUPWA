<?php
/**
 * @package     JUPWA\Push
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Push;

use Exception;
use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use JsonException;
use JUPWA\Helpers\Manifest;
use JUPWA\Utils\Util;
use RuntimeException;
use stdClass;

class Push
{
    /**
     *
     * @param string $token
     * @param string $title
     * @param string $body
     * @param string $domain
     * @param string $link
     *
     * @return array
     *
     * @throws GuzzleException
     * @throws JsonException
     * @since 1.0
     */
    public static function send(
        string $token,
        string $title,
        string $body = '',
        string $domain = '',
        string $link = ''
    ): array {
        $serviceAccountFile = JPATH_ROOT.'/.well-known/jupwa/firebase-service-account.json';
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        if (!file_exists($serviceAccountFile)) {
            return [
                'success' => false,
                'error' => 'Service account file not found',
                'code' => 404,
            ];
        }

        try {
            $serviceAccountData = json_decode(
                file_get_contents($serviceAccountFile),
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            $projectId = $serviceAccountData['project_id'] ?? '';

            if (!$projectId) {
                throw new RuntimeException('Project ID missing in service account file');
            }

            $credentials = new ServiceAccountCredentials(
                $scopes,
                $serviceAccountFile
            );

            $authToken = $credentials->fetchAuthToken();
            $accessToken = $authToken['access_token'] ?? null;

            if (!$accessToken) {
                throw new RuntimeException('Failed to fetch access token');
            }

            $client = new Client([
                'base_uri' => 'https://fcm.googleapis.com/',
                'headers' => [
                    'Authorization' => 'Bearer '.$accessToken,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $domain = $domain ?: Uri::base();
            $domain = str_replace(JPATH_ROOT.'/cli', '', $domain);
            $domain .= '/';

            $data = [
                'json' => [
                    'message' => [
                        'token' => $token,
                        'data' => [
                            'title' => $title,
                            'body' => $body,
                            'image' => $domain.'favicons/icon_192.png',
                            'badge' => $domain.'favicons/icon_72.png',
                            'click_action' => $link ?: $domain,
                        ],
                        'apns' => [
                            'headers' => [
                                'apns-priority' => '10',
                                'apns-push-type' => 'alert',
                            ],
                            'payload' => [
                                'aps' => [
                                    'alert' => [
                                        'title' => $title,
                                        'body' => $body,
                                    ],
                                    'sound' => 'default',
                                    'badge' => 0,
                                    'mutable-content' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $response = $client->post(
                'v1/projects/'.$projectId.'/messages:send',
                $data
            );

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $result = json_decode($body, true, 512, JSON_THROW_ON_ERROR);

            if ($statusCode === 200) {
                return [
                    'success' => true,
                    'data' => $result,
                    'code' => 200,
                ];
            }

            return [
                'success' => false,
                'error' => 'Unexpected HTTP status: '.$statusCode,
                'code' => $statusCode,
            ];

        } catch (RequestException $e) {
            $response = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $errorBody = $response ? json_decode($response->getBody()->getContents(), true) : [];
            $message = $errorBody['error']['message'] ?? $e->getMessage();

            return [
                'success' => false,
                'error' => "FCM Error ($statusCode): $message",
                'code' => $statusCode,
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'System Error: '.$e->getMessage(),
                'code' => 500,
            ];
        }
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
        if (!self::isPush($option)) {
            return;
        }

        $doc = Factory::getApplication()->getDocument();
        $pwa_version = Manifest::getVersion();
        $params = $option['params'];

        $pwapush = [
            'firebase' => [
                'apiKey' => $params['apiKey'],
                'projectId' => $params['projectId'],
                'messagingSenderId' => $params['messagingSenderId'],
                'appId' => $params['appId'],
                'vapidKey' => $params['vapidKey'],
            ],
            'csrf' => Session::getFormToken(),
            'sw' => Uri::base().'sw.js?v='.$pwa_version,
            'api' => [
                'subscribe' => Uri::base().'index.php?option=com_ajax&plugin=JUPWAPushSubscribe&format=json',
                'unsubscribe' => Uri::base().'index.php?option=com_ajax&plugin=JUPWAPushUnsubscribe&format=json',
            ],
            'localisation' => [
                'addToMainDisplay' => Text::_('PLG_JUPWA_ADD_TO_MAIN_DISPLAY'),
                'notSupport' => Text::_('PLG_JUPWA_NOT_SUPPORT'),
                'notGranted' => Text::_('PLG_JUPWA_NOT_GRANTED'),
                'swNotSupport' => Text::_('PLG_JUPWA_SW_NOT_SUPPORT'),
                'subscribe' => Text::_('PLG_JUPWA_SUBSCRIBE'),
                'unsubscribe' => Text::_('PLG_JUPWA_UNSUBSCRIBE'),
                'tokenNotLoad' => Text::_('PLG_JUPWA_TOKEN_NOT_LOAD'),
                'tokenNotFound' => Text::_('PLG_JUPWA_TOKEN_NOT_FOUND'),
                'permissionDenied' => Text::_('PLG_JUPWA_PERMISION_DENIED'),
            ],
        ];

        $pwapush = json_encode($pwapush);

        $doc->addCustomTag('<script id="jupwa-push-setting" type="application/json">'.$pwapush.'</script>');
    }

    /**
     *
     * @param array $option
     *
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    public static function isPush(array $option = []): bool
    {
        $params = $option['params'] ?? null;

        if (!$params || $params->get('usepush') != 1) {
            return false;
        }

        if (
            $params->get('usepush_for_jusers') == 1 &&
            Factory::getApplication()->getIdentity()->guest
        ) {
            return false;
        }

        $data = [
            $params['firebaseServiceAccount'],
            $params['apiKey'],
            $params['projectId'],
            $params['messagingSenderId'],
            $params['appId'],
            $params['vapidKey_wp'],
        ];

        if (!Util::checkFields($data)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0
     */
    public static function checkAjaxPlugin(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(['extension_id', 'enabled']);
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('name').' = '.$db->Quote('plg_ajax_jupwapush'));
        $query->where($db->quoteName('folder').' = '.$db->Quote('ajax'));
        $db->setQuery($query);
        $db->execute();
        $status = $db->loadObject();

        if ($status && $status->enabled == 0) {
            $object = new stdClass();
            $object->extension_id = $status->extension_id;
            $object->enabled = 1;
            $result = $db->updateObject('#__extensions', $object, 'extension_id', true);

            if ($result) {
                Factory::getApplication()->enqueueMessage('Enable Ajax Plugin "JUPWA. Push"');
            }
        }
    }
}   
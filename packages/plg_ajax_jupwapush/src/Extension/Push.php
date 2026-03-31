<?php
/**
 * JUPWAPush plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2025-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JU\Plugin\Ajax\JUPWAPush\Extension;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use stdClass;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

class Push extends CMSPlugin implements SubscriberInterface
{
    /**
     * @since  1.0.0
     * @var    boolean
     */
    protected $autoloadLanguage = true;

    /**
     * @return array
     *
     * @since 1.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAjaxJUPWAPushCheck' => 'onAjaxJUPWAPushCheck',
            'onAjaxJUPWAPushSubscribe' => 'onAjaxJUPWAPushSubscribe',
            'onAjaxJUPWAPushUnsubscribe' => 'onAjaxJUPWAPushUnsubscribe',
        ];
    }

    /**
     * @param \Joomla\Event\Event $event
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function onAjaxJUPWAPushCheck(Event $event): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $app = Factory::getApplication();

            $this->auth($app, $event);

            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $user = $app->getIdentity();
            $post = (object)$app->input->post->getArray();
            $fcm_token = $post->fcm_token;

            $query = $db->getQuery(true);
            $query->select(['*']);
            $query->from($db->quoteName('#__jupwa_push_users'));
            $query->where($db->quoteName('user_id').' = '.$db->Quote($user->id));
            $query->where($db->quoteName('fcm_token').' = '.$db->Quote($fcm_token));
            $db->setQuery($query);
            $db->execute();
            $check = $db->getNumRows();

            $event->setArgument('result', $check);
        } else {
            $this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
        }
    }

    /**
     * @param \Joomla\Event\Event $event
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function onAjaxJUPWAPushSubscribe(Event $event): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $app = Factory::getApplication();

            $this->auth($app, $event);

            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $user = $app->getIdentity();
            $post = (object)$app->input->post->getArray();
            $fcm_token = $post->fcm_token;
            $chek = $this->checkUser($fcm_token);

            if ($chek == 0) {
                \JUPWA\Push\Push::send(
                    $fcm_token,
                    Text::_('PLG_AJAX_JUPWAPUSH_SUBSCRIBE'),
                    'Тепер ви отримуватимете сповіщення!'
                );

                $obj = new stdClass();

                if ($user->guest == 0) {
                    $obj->user_id = $user->id;
                }

                $obj->fcm_token = $fcm_token;
                $db->insertObject('#__jupwa_push_users', $obj);

                $event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_SUBSCRIBE'));
            } else {
                $event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_SUBSCRIBED'));
            }
        } else {
            $this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
        }
    }

    /**
     * @param \Joomla\Event\Event $event
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0.0
     */
    public function onAjaxJUPWAPushUnsubscribe(Event $event): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $app = Factory::getApplication();

            $this->auth($app, $event);

            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $user = $app->getIdentity();
            $post = (object)$app->input->post->getArray();
            $fcm_token = $post->fcm_token;
            $chek = $this->checkUser($fcm_token);

            if ($chek > 0) {
                $query = $db->getQuery(true);
                $query->delete($db->quoteName('#__jupwa_push_users'));

                if ($user->guest == 1) {
                    $query->where($db->quoteName('fcm_token').'='.$db->quote($fcm_token));
                } else {
                    $query->where([
                        $db->quoteName('user_id').'='.$db->quote($user->id),
                        $db->quoteName('fcm_token').'='.$db->quote($fcm_token),
                    ]);
                }

                $db->setQuery($query);
                $db->execute();

                $event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_UNSUBSCRIBED'));
            } else {
                $this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_NOT_UNSUBSCRIBED'), 200);
            }
        } else {
            $this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
        }
    }

    /**
     * @param string $fcm_token
     *
     * @return int
     *
     * @since 1.0
     */
    protected function checkUser(string $fcm_token): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        $query->select(['*']);
        $query->from($db->quoteName('#__jupwa_push_users'));
        $query->where($db->quoteName('fcm_token').' = '.$db->Quote($fcm_token));
        $db->setQuery($query);
        $db->execute();

        return $db->getNumRows();
    }

    /**
     * @param $app
     * @param $event
     *
     * @return void
     *
     * @throws Exception
     * @since 1.0.0
     */
    protected function auth(
        $app,
        $event
    ): void {
        Session::checkToken() or $this->returnError($event, Text::_('JINVALID_TOKEN'), 403);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
        }
    }

    /**
     * @param \Joomla\Event\Event $event
     * @param                        $message
     * @param int $code
     *
     *
     * @throws Exception
     * @since 1.0.0
     */
    protected function returnError(
        Event $event,
        $message,
        int $code = 500
    ): void {
        Factory::getApplication()->enqueueMessage($message, 'error');

        $event->setArgument('result', [
            'success' => false,
            'message' => $message,
            'data' => null,
        ]);

        throw new Exception($message, $code);
    }
}
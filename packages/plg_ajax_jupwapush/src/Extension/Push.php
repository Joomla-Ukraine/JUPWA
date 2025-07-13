<?php
/**
 * JUPWAPush plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JU\Plugin\Ajax\JUPWAPush\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

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
			'onAjaxJUPWAPushSubscribe'   => 'onAjaxJUPWAPushSubscribe',
			'onAjaxJUPWAPushUnsubscribe' => 'onAjaxJUPWAPushUnsubscribe'
		];
	}

	/**
	 * @param \Joomla\Event\Event $event
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function onAjaxJUPWAPushSubscribe(Event $event): void
	{
		if($_SERVER[ 'REQUEST_METHOD' ] === 'POST')
		{
			$app = Factory::getApplication();

			$this->auth($app, $event);

			$db   = Factory::getContainer()->get(DatabaseInterface::class);
			$user = $app->getIdentity();
			$post = (object) $app->input->post->getArray();

			$user_id   = $user->id;
			$fcm_token = $post->fcm_token;

			$chek = $this->checkUser($user_id, $fcm_token);
			if($chek == 0)
			{
				$obj            = new \stdClass();
				$obj->user_id   = $user_id;
				$obj->fcm_token = $fcm_token;
				$db->insertObject('#__jupwa_push_users', $obj);

				$event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_SUBSCRIBE'));
			}
			else
			{
				$event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_SUBSCRIBED'));
			}
		}
		else
		{
			$this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
		}
	}

	/**
	 * @param \Joomla\Event\Event $event
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 */
	public function onAjaxJUPWAPushUnsubscribe(Event $event): void
	{
		if($_SERVER[ 'REQUEST_METHOD' ] === 'POST')
		{
			$app = Factory::getApplication();

			$this->auth($app, $event);

			$db   = Factory::getContainer()->get(DatabaseInterface::class);
			$user = $app->getIdentity();
			$post = (object) $app->input->post->getArray();

			$user_id   = $user->id;
			$fcm_token = $post->fcm_token;

			$chek = $this->checkUser($user_id, $fcm_token);
			if($chek > 0)
			{
				$query = $db->getQuery(true);
				$query->delete($db->quoteName('#__jupwa_push_users'));
				$query->where([
					$db->quoteName('user_id') . '=' . $db->quote($user_id),
					$db->quoteName('fcm_token') . '=' . $db->quote($fcm_token)
				]);
				$db->setQuery($query);
				$db->execute();

				$event->setArgument('result', Text::_('PLG_AJAX_JUPWAPUSH_UNSUBSCRIBED'));
			}
			else
			{
				$this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_NOT_UNSUBSCRIBED'), 200);
			}
		}
		else
		{
			$this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
		}
	}

	/**
	 * @param int    $user_id
	 * @param string $fcm_token
	 *
	 * @return int
	 *
	 * @since 1.0
	 */
	protected function checkUser(int $user_id, string $fcm_token): int
	{
		$db    = Factory::getContainer()->get(DatabaseInterface::class);
		$query = $db->getQuery(true);

		$query->select([ '*' ]);
		$query->from($db->quoteName('#__jupwa_push_users'));
		$query->where($db->quoteName('user_id') . ' = ' . $db->Quote($user_id));
		$query->where($db->quoteName('fcm_token') . ' = ' . $db->Quote($fcm_token));
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
	 * @throws \Exception
	 * @since 1.0.0
	 */
	protected function auth($app, $event): void
	{
		Session::checkToken() or $this->returnError($event, Text::_('JINVALID_TOKEN'), 403);

		$user = $app->getIdentity();
		if($_SERVER[ 'REQUEST_METHOD' ] !== 'POST' || $user->guest == 1)
		{
			$this->returnError($event, Text::_('PLG_AJAX_JUPWAPUSH_ERROR'), 400);
		}
	}

	/**
	 * @param \Joomla\Event\Event    $event
	 * @param                        $message
	 * @param int                    $code
	 *
	 *
	 * @throws \Exception
	 * @since 1.0.0
	 */
	protected function returnError(Event $event, $message, int $code = 500): void
	{
		Factory::getApplication()->enqueueMessage($message, 'error');

		$event->setArgument('result', [
			'success' => false,
			'message' => $message,
			'data'    => null
		]);

		throw new \Exception($message, $code);
	}
}
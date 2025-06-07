<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;

class Facebook
{
	/**
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function bot(): bool
	{
		$fb = false;
		if(!empty($_SERVER[ 'HTTP_USER_AGENT' ]))
		{
			$pattern = strtolower('#facebookscraper|facebookexternalhit|facebook|Facebot#x');
			if(preg_match($pattern, strtolower($_SERVER[ 'HTTP_USER_AGENT' ])))
			{
				$fb = true;
			}
		}

		return $fb;
	}

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function fix(): bool
	{
		$app = Factory::getApplication();

		$unsupported = false;
		if(!empty($_SERVER[ 'HTTP_USER_AGENT' ]))
		{
			$pattern = strtolower('#facebookexternalhit|LinkedInBot#x');

			if(preg_match($pattern, strtolower($_SERVER[ 'HTTP_USER_AGENT' ])))
			{
				$unsupported = true;
			}
		}

		if($app->get('gzip', 0) == 1 && $unsupported === true)
		{
			$app->set('gzip', 0);
		}

		return true;
	}
}
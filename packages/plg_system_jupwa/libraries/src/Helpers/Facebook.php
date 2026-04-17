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

use Exception;
use Joomla\CMS\Factory;

class Facebook
{
    /**
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    public static function bot(): bool
    {
        return self::matchUserAgent('facebookscraper|facebookexternalhit|facebook|facebot');
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    public static function fix(): bool
    {
        if (self::matchUserAgent('facebookexternalhit|linkedinbot')) {
            $app = Factory::getApplication();

            if ($app->get('gzip') === 1) {
                $app->set('gzip', 0);

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pattern
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    private static function matchUserAgent(string $pattern): bool
    {
        $ua = Factory::getApplication()->input->server->getString('HTTP_USER_AGENT', '');

        if (empty($ua)) {
            return false;
        }

        return (bool)preg_match('#'.$pattern.'#i', $ua);
    }
}
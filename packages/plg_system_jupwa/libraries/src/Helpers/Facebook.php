<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
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
        $app = Factory::getApplication();
        $ua = $app->input->server->getString('HTTP_USER_AGENT', '');

        return self::matchUserAgent('facebookscraper|facebookexternalhit|facebook|facebot', $ua);
    }

    /**
     * @return bool
     *
     * @throws Exception
     * @since 1.0
     */
    public static function fix(): bool
    {
        $app = Factory::getApplication();
        $ua = $app->input->server->getString('HTTP_USER_AGENT', '');

        if (self::matchUserAgent('facebookexternalhit|linkedinbot', $ua)) {
            if ($app->get('gzip') === 1) {
                $app->set('gzip', 0);

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pattern
     * @param string $ua
     * @return bool
     *
     * @since 1.0
     */
    private static function matchUserAgent(string $pattern, string $ua): bool
    {
        if (empty($ua)) {
            return false;
        }

        return (bool)preg_match('#'.$pattern.'#i', $ua);
    }
}

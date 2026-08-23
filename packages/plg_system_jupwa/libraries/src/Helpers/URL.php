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

use Joomla\CMS\Uri\Uri;

class URL
{
    /**
     * @param string|null $url
     *
     * @return bool
     *
     * @since 1.0
     */
    public static function is_url(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string|null $html
     *
     * @return string
     *
     * @since 1.0
     */
    public static function absolute(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $root = Uri::root();
        $scheme = Uri::getInstance()->getScheme().'://';

        $html = str_replace(
            ['href="//', 'src="//'],
            ['href="'.$scheme, 'src="'.$scheme],
            $html
        );

        $pattern = '/(href|src)=["\'](?!(?:https?|mailto|tel|javascript|#|data:))([^"\']+)["\']/i';

        return preg_replace_callback(
            $pattern,
            static function ($matches) use ($root) {
                $attr = $matches[1];
                $path = $matches[2];

                $path = ltrim($path, '/');

                return $attr.'="'.$root.$path.'"';
            },
            $html
        ) ?? $html;
    }
}
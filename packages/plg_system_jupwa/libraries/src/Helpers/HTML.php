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

use JUPWA\Classes\Minify;

class HTML
{
    /**
     * @param string|null $text
     *
     * @return string
     *
     * @since 1.0
     */
    public static function text(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $text = trim($text);

        return str_replace(["\n", "\r", "\t", "\n\r"], '', $text);
    }

    /**
     * @param string|null $html
     *
     * @return string
     *
     * @since 1.0
     */
    public static function html(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        $html = self::clean($html);
        $html = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $html) ?? $html;
        $html = preg_replace('/<iframe\b[^>]*>.*?<\/iframe>/is', '', $html) ?? $html;
        $html = preg_replace('/<p\b[^>]*>"/is', '<p>', $html) ?? $html;

        if (preg_match('/<p\b[^>]*>.*?<\/p>/is', $html, $matches)) {
            $html = $matches[0];
        }

        return $html;
    }

    /**
     * @param string $html
     *
     * @return string
     *
     * @since 1.0
     */
    public static function clean(string $html): string
    {
        $patterns = [
            '/<script\b[^>]*>.*?<\/script>/is',
            '/<style\b[^>]*>.*?<\/style>/is',
            '/<noscript\b[^>]*>.*?<\/noscript>/is',
            '/\son[a-z]+\s*=\s*[^\s>]*/i',
            '/<p>\s*{[a-zA-Z0-9\-_]*\s*.*?}\s*<\/p>/i',
            '/{[a-zA-Z0-9\-_]*\s*.*?}/i',
            '/\[(.*?)\s?.*?\].*?\[\/(.*?)\]/i',
            '/::cck::(.*?)::\/cck::/i',
        ];

        $html = preg_replace($patterns, '', $html) ?? $html;

        $html = preg_replace('/::introtext::(.*?)::\/introtext::/i', '$1', $html) ?? $html;

        return preg_replace('/::fulltext::(.*?)::\/fulltext::/i', '$1', $html) ?? $html;
    }

    /**
     * @param array $matches
     *
     * @return string
     *
     * @since 1.0
     */
    public static function tag_html(array $matches = []): string
    {
        $buffer = $matches[1] ?? '';

        $patterns = [
            '#xml:lang=".*?"#is',
            '#xmlns:fb="http://www\.facebook\.com/2008/fbml"#i',
            '#prefix="fb: http://www\.facebook\.com/2008/fbml"#i',
            '#xmlns:og="http://opengraphprotocol\.org/schema/"#i',
            '#prefix="og: http://opengraphprotocol\.org/schema/"#i',
            '#prefix="og: http://ogp\.me/ns\#"#i',
        ];

        $buffer = preg_replace($patterns, '', $buffer) ?? $buffer;
        $buffer = rtrim($buffer);

        $prefix = ' prefix="og: https://ogp.me/ns# fb: https:///www.facebook.com/2008/fbml og: https://opengraphprotocol.org/schema/"';

        return '<html'.rtrim($buffer).$prefix.'>';
    }

    /**
     * @param string $html
     *
     * @return string
     *
     * @since 1.0
     */
    public static function compress(string $html): string
    {
        $preTagsPattern = '!(<(?:code|pre|textarea|script)\b[^>]*>.*?</(?:code|pre|textarea|script)>)!si';
        preg_match_all($preTagsPattern, $html, $matches);

        $html = preg_replace($preTagsPattern, '#pre#', $html) ?? $html;

        $html = Minify::minify($html, [
            'xhtml' => false,
        ]);

        $html = preg_replace('/[\r\n\t]+/', ' ', $html) ?? $html;

        if (!empty($matches[0])) {
            foreach ($matches[0] as $tag) {
                $html = preg_replace('/#pre#/', $tag, $html, 1) ?? $html;
            }
        }

        return preg_replace(
            [
                '#</script>\s*#',
                '#\s*<script#',
            ],
            ['</script>', '<script'],
            $html
        ) ?? $html;
    }
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Utils
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Utils;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use stdClass;

class Util
{
    /**
     *
     * @return void
     *
     * @since 1.0
     */
    public static function addVersion(): void
    {
        $json = [
            'version' => hash('crc32b', (string)time()),
        ];

        $json_string = json_encode(
            $json,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        );

        File::write(JPATH_SITE.'/favicons/assets.json', $json_string);
    }

    /**
     * @param string $name
     * @param array $variables
     *
     * @return string
     *
     * @since 1.0
     */
    public static function tmpl(
        string $name,
        array $variables = []
    ): string {
        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $query = $db->getQuery(true)
            ->select($db->quoteName('template'))
            ->from($db->quoteName('#__template_styles'))
            ->where($db->quoteName('client_id').' = 0')
            ->where($db->quoteName('home').' = 1');

        $db->setQuery($query);
        $template = $db->loadResult();

        $search = null;
        if ($template) {
            $path = JPATH_SITE.'/templates/'.$template.'/html/jupwa/';

            if (is_dir($path) && file_exists($path.$name.'.php')) {
                $search = $path;
            }
        }

        $default_path = JPATH_SITE.'/plugins/system/jupwa/tmpl/';

        $render_path = $search ?: $default_path;

        return (new FileLayout($name, $render_path))->render($variables);
    }


    /**
     * @param array $json
     *
     * @return string
     *
     * @since 1.0
     */
    public static function LD(array $json = []): string
    {
        $data = array_filter($json, static function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($data)) {
            return '';
        }

        return '<script type="application/ld+json">'.json_encode($data, JSON_UNESCAPED_UNICODE).'</script>';
    }


    /**
     * @return stdClass|null
     *
     * @since 1.0
     */
    public static function get_thumbs(): ?stdClass
    {
        $path = JPATH_SITE.'/favicons/thumbs.json';

        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content) {
                $decoded = json_decode($content);

                return $decoded instanceof stdClass ? $decoded : null;
            }
        }

        return null;
    }

    /**
     * @param string|null $url
     *
     * @return bool|string
     *
     * @since 1.0
     */
    public static function HTTP(?string $url): string|bool
    {
        if (empty($url)) {
            return false;
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);

            return false;
        }

        $httpCode = (string)curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

        curl_close($ch);

        return $httpCode;
    }

    /**
     *
     * @param array $fields
     * @param bool $requireAll
     *
     * @return bool
     *
     * @since 1.0
     */
    public static function checkFields(
        array $fields,
        bool $requireAll = true
    ): bool {
        $filtered = array_filter(
            $fields,
            static function ($value) {
                return $value !== null && $value !== '';
            }
        );

        return $requireAll ? count($filtered) === count($fields) : !empty($filtered);
    }
}
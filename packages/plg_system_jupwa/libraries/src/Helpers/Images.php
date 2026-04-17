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

use DOMDocument;
use Exception;
use FastImageSize\FastImageSize;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class Images
{
    /**
     * @param array $option
     *
     * @return string
     *
     * @throws Exception
     * @since 1.0
     */
    public static function image_storage(array $option = []): string
    {
        $text = $option['text'] ?? '';
        $article = $option['article'] ?? null;
        $alltxt = $option['alltxt'] ?? '';
        $params = $option['params'] ?? null;

        if (self::is_gallery($text)) {
            $image = self::gallery($text);
        } elseif ($article && ($_image = self::article($article->images ?? ''))) {
            $image = $_image;
        } elseif (self::is_html($text)) {
            $image = self::html($text);
        } elseif (self::is_YouTube($alltxt)) {
            $image = self::YouTube($alltxt);
        } else {
            $image = self::display_default(
                $params?->get('selectimg'),
                $params?->get('image'),
                $params?->get('imagemain')
            );
        }

        return (string)$image;
    }

    /**
     * @param string|null $image
     *
     * @return object
     *
     * @since 1.0
     */
    public static function display(?string $image): object
    {
        $width = 0;
        $height = 0;
        $local = true;
        $image = $image ?? '';

        if (str_contains($image, 'http')) {
            $domain = parse_url(Uri::base(), PHP_URL_HOST);
            $img_domain = parse_url($image, PHP_URL_HOST);

            if ($domain !== $img_domain) {
                $local = false;
            } else {
                $image = str_replace(Uri::base(), '', $image);
            }
        }

        if ($local && $image !== '') {
            $FastImageSize = new FastImageSize();
            $cleanImage = ltrim($image, '/');
            $fullPath = JPATH_SITE.'/'.$cleanImage;

            if (is_file($fullPath)) {
                $imageSize = $FastImageSize->getImageSize($fullPath);
                if ($imageSize !== false) {
                    $width = $imageSize['width'] ?? 0;
                    $height = $imageSize['height'] ?? 0;
                }
            }
        }

        return (object)[
            'image' => Uri::base().ltrim($image, '/'),
            'width' => (int)$width,
            'height' => (int)$height,
        ];
    }

    /**
     * @param $selectimg
     * @param $img
     * @param $imgmain
     *
     * @return string
     *
     * @throws Exception
     * @since 1.0
     */
    public static function display_default(
        $selectimg,
        $img,
        $imgmain
    ): string {
        $imgURL = (string)($img ? HTMLHelper::cleanImageURL($img)->url : '');
        $imgMainURL = (string)($imgmain ? HTMLHelper::cleanImageURL($imgmain)->url : '');
        $default = Uri::base().'media/jupwa/image/jupwa.png';

        if ((int)$selectimg === 1) {
            $rand_img = self::random();

            if ($rand_img !== '') {
                return Uri::base().ltrim($rand_img, '/');
            }
        }

        if ((int)$selectimg === 0) {
            if ($imgMainURL !== '' && is_file(JPATH_SITE.'/'.$imgMainURL)) {
                return $imgMainURL;
            }

            if ($imgURL !== '' && is_file(JPATH_SITE.'/'.$imgURL)) {
                return $imgURL;
            }
        }

        return $default;
    }

    /**
     * @param string|null $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_gallery(?string $text): bool
    {
        return $text !== null && str_contains($text, '{gallery');
    }

    /**
     * @param string $text
     *
     * @return mixed
     *
     * @since 1.0
     */
    private static function gallery(string $text): string
    {
        if (preg_match('/{gallery\s+(.*?)}/i', $text, $matches)) {
            $parts = explode('|', $matches[1]);
            $folderPath = 'images/'.trim($parts[0], ' /');
            $fullPath = JPATH_SITE.'/'.$folderPath;

            if (is_dir($fullPath)) {
                $files = glob($fullPath.'/*.{jpg,jpeg,png,gif,webp,avif}', GLOB_BRACE);

                if ($files && count($files) > 0) {
                    natcasesort($files);
                    $firstImage = reset($files);

                    return str_replace(JPATH_SITE.'/', '', $firstImage);
                }
            }
        }

        return '';
    }

    /**
     * @param string|null $jsonimages
     *
     * @return string
     *
     * @since 1.0
     */
    private static function article(?string $jsonimages): string
    {
        if (empty($jsonimages)) {
            return '';
        }

        $images = json_decode($jsonimages);
        if (!$images) {
            return '';
        }

        $intro = $images->image_intro ?? '';
        $full = $images->image_fulltext ?? '';

        return $intro !== '' ? $intro : $full;
    }

    /**
     * @param string|null $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_html(?string $text): bool
    {
        return $text !== null && str_contains($text, '<img');
    }

    /**
     * @param string $text
     *
     * @return string
     *
     * @since 1.0
     */
    private static function html(string $text): string
    {
        $dom = new DOMDocument();
        $src = '';

        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$text, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        $images = $dom->getElementsByTagName('img');
        if ($images->length > 0) {
            $src = $images->item(0)->getAttribute('src');
        }

        return (string)$src;
    }

    /**
     * @param string|null $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_YouTube(?string $text): bool
    {
        return $text !== null && (str_contains($text, 'youtube.com') || str_contains($text, 'youtu.be'));
    }

    /**
     * @param string $text
     *
     * @return string
     *
     * @since 1.0
     */

    private static function YouTube(string $text): string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i';

        if (preg_match($pattern, $text, $match)) {
            $videoId = $match[1];

            return "https://img.youtube.com/vi/{$videoId}/maxresdefault.jpg";
        }

        return '';
    }

    /**
     * @return string
     *
     * @since 1.0
     */
    private static function random(): string
    {
        $folder = 'images/jupwa/images';
        $images = Folders::files($folder);

        if (!empty($images) && is_array($images)) {
            return $images[array_rand($images)];
        }

        return '';
    }
}
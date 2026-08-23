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
        $alltxt = $option['alltxt'] ?? '';
        $article = $option['article'] ?? null;
        $params = $option['params'] ?? null;

        if (self::is_gallery($text)) {
            return self::gallery($text);
        }

        if ($article && ($image = self::article($article->images ?? ''))) {
            return $image;
        }

        if (self::is_html($text)) {
            return self::html($text);
        }

        if (self::is_YouTube($alltxt)) {
            return self::YouTube($alltxt);
        }

        if ($params) {
            return self::display_default(
                (int)$params->get('selectimg', 0),
                (string)$params->get('image', ''),
                (string)$params->get('imagemain', '')
            );
        }

        return Uri::base().'media/jupwa/image/jupwa.png';
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
        $image = trim($image);
        $image = rawurldecode($image);
        $image = $image ?? '';

        if (URL::is_url($image)) {
            $baseHost = parse_url(Uri::base(), PHP_URL_HOST);
            $imgHost = parse_url($image, PHP_URL_HOST);

            $local = false;
            if ($baseHost === $imgHost) {
                $local = true;
                $image = str_replace(Uri::base(), '', $image);
            }
        }

        if ($local && !empty($image)) {
            $fastImageSize = new FastImageSize();
            $cleanPath = ltrim($image, '/');
            $fullPath = JPATH_SITE.'/'.$cleanPath;

            if (is_file($fullPath)) {
                $imageSize = $fastImageSize->getImageSize($fullPath);

                if ($imageSize) {
                    $width = $imageSize['width'] ?? 0;
                    $height = $imageSize['height'] ?? 0;
                }
            }
        }

        return (object)[
            'image' => (str_contains($image, 'http') ? $image : Uri::base().ltrim($image, '/')),
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @param int $selectimg
     * @param string $img
     * @param string $imgmain
     *
     * @return string
     *
     * @since 1.0
     */
    public static function display_default(
        int $selectimg,
        string $img,
        string $imgmain
    ): string {
        $defaultImage = Uri::base().'media/jupwa/image/jupwa.png';
        $image = $defaultImage;

        if ($selectimg === 1) {
            $rand_img = self::random();

            if ($rand_img !== '') {
                return Uri::base().ltrim($rand_img, '/');
            }
        }

        if (!empty($imgmain)) {
            $cleanMain = HTMLHelper::cleanImageURL($imgmain)->url;

            if (is_file(JPATH_SITE.'/'.$cleanMain)) {
                return $cleanMain;
            }
        }

        if (!empty($img)) {
            $cleanImg = HTMLHelper::cleanImageURL($img)->url;

            if (is_file(JPATH_SITE.'/'.$cleanImg)) {
                return $cleanImg;
            }
        }

        return $image;
    }

    /**
     * @param string $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_gallery(string $text): bool
    {
        return str_contains($text, '{gallery');
    }

    /**
     * @param string $text
     *
     * @return mixed
     *
     * @since 1.0
     */
    private static function gallery(string $text): mixed
    {
        if (strpos($text, '{gallery') === false) {
            return '';
        }

        if (preg_match('/{gallery\s+(.*?)}/i', $text, $imgsource)) {
            $folder_match = $imgsource[1];
            $imglist = explode('|', $folder_match);
            $imgsource = $imglist[0];
            $root = JPATH_BASE.'/';
            $folder = 'images/'.$imgsource;
            $img_folder = $root.$folder;
            $galleries = glob($img_folder.'/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

            if (count($galleries) > 0 && is_dir($img_folder)) {
                $i = 0;
                $html = [];
                natcasesort($galleries);
                foreach ($galleries as $gallery) {
                    if ($i > 0) {
                        break;
                    }

                    $html[] = str_replace(JPATH_BASE.'/', '', $gallery);
                    $i++;
                }

                return $html[0];
            }
        }

        return '';
    }

    /**
     * @param $jsonimages
     *
     * @return string
     *
     * @since 1.0
     */
    private static function article(mixed $jsonimages): string
    {
        if (empty($jsonimages)) {
            return '';
        }

        $images = is_string($jsonimages) ? json_decode($jsonimages) : $jsonimages;

        if (!$images) {
            return '';
        }

        return $images->image_intro ?: $images->image_fulltext ?: '';
    }

    /**
     * @param string $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_html(string $text): bool
    {
        return str_contains($text, '<img');
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
        $src = '';
        if (empty($text)) {
            return $src;
        }

        $dom = new DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8"><html><body>'.$text.'</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_use_internal_errors($internalErrors);

        $images = $dom->getElementsByTagName('img');
        if ($images->length > 0) {
            $src = $images->item(0)->getAttribute('src');
        }

        return $src;
    }

    /**
     * @param string $text
     *
     * @return bool
     *
     * @since 1.0
     */
    private static function is_YouTube(string $text): bool
    {
        return str_contains($text, 'youtube.com') || str_contains($text, 'youtu.be');
    }

    /**
     * @param $text
     *
     * @return string
     *
     * @since 1.0
     */
    private static function YouTube($text): string
    {
        if (preg_match(
            '%(?:youtube\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i',
            $text,
            $match
        )) {
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
        $folder = '/images/jupwa/images';
        $images = Folders::files($folder);

        if (!empty($images)) {
            return $images[array_rand($images)];
        }

        return '';
    }
}
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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class OG
{
    /**
     *
     * @param array $option
     * @param array $parameters
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function tag(
        array $option = [],
        array $parameters = []
    ): void {
        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if (isset($option['params']) && $option['params']->get('og') == 1) {
            $app = Factory::getApplication();
            $lang = $app->getLanguage();

            $plugin = PluginHelper::getPlugin('system', 'languagecode');
            $mapping = $plugin ? (new Registry($plugin->params))->toArray() : [];
            $currentCode = strtolower($lang->getTag());

            $local = $lang->getTag();
            if (!empty($mapping[$currentCode])) {
                $local = $mapping[$currentCode];
            }
            $local = str_replace('-', '_', $local);

            $doc->setMetaData('og:locale', $local, 'property');
            $doc->setMetaData('og:type', $option['type'], 'property');
            $doc->setMetaData('og:title', $option['title'], 'property');
            $doc->setMetaData(
                'og:description',
                $option['description'],
                'property'
            );
            $doc->setMetaData('og:url', Uri::current(), 'property');
            $doc->setMetaData(
                'og:site_name',
                $app->get('sitename'),
                'property'
            );

            if (isset($option['image'])) {
                $doc->setMetaData(
                    'og:image',
                    HTMLHelper::cleanImageURL($option['image'])->url,
                    'property'
                );

                if ((isset($option['image_width']) && $option['image_width'] > 0) || (isset($option['image_height']) && $option['image_height'] > 0)) {
                    $doc->setMetaData(
                        'og:image:width',
                        $option['image_width'],
                        'property'
                    );
                    $doc->setMetaData(
                        'og:image:height',
                        $option['image_height'],
                        'property'
                    );
                }

                $doc->setMetaData(
                    'og:image:alt',
                    $option['title'],
                    'property'
                );
            }

            foreach ($parameters as $k => $v) {
                $doc->setMetaData('og:'.$k, $v, 'property');
            }
        }
    }

    /**
     *
     * @param array $option
     *
     * @return void
     *
     * @throws \Exception
     * @since 1.0
     */
    public static function tagArticle(array $option = []): void
    {
        $app = Factory::getApplication();
        $doc = $app->getDocument();

        if (isset($option['params']) && $option['params']->get('og') == 1) {
            if (isset($option['article']->modified) && !($option['article']->modified === '' || $option['article']->modified === '0000-00-00 00:00:00')) {
                $doc->setMetaData(
                    'og:updated_time',
                    date('c', strtotime($option['article']->modified)),
                    'property'
                );
                $doc->setMetaData(
                    'article:modified_time',
                    date('c', strtotime($option['article']->modified)),
                    'property'
                );
            }

            if (isset($option['article']->publish_up) && $option['article']->publish_up !== '') {
                $doc->setMetaData(
                    'article:published_time',
                    date('c', strtotime($option['article']->publish_up)),
                    'property'
                );
            }

            if (isset($option['article']->category_title) && $option['article']->category_title !== '') {
                $doc->setMetaData(
                    'article:section',
                    $option['article']->category_title,
                    'property'
                );
            }

            if (isset($option['article']->metakey) && $option['article']->metakey != '') {
                if (Facebook::bot() === false) {
                    $doc->setMetaData(
                        'news_keywords',
                        $option['article']->metakey,
                        'property'
                    );
                }

                $_metakeys = explode(',', $option['article']->metakey);
                $i = 0;
                foreach ($_metakeys as $_metakey) {
                    $doc->setMetaData(
                        'article:tag_'.$i.'_',
                        trim($_metakey),
                        'property'
                    );
                    $i++;
                }
            }
        }
    }
}
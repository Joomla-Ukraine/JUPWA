<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die();

/** @var array $displayData */
$data = (object)$displayData;
$items = (array)$data->params->get('push_widget_info');
$svg = Uri::base().'media/jupwa/icons/icons.svg?v='.$data->version;

?>
<div id="jupwa-widget" class="jupwa-widget">
    <button type="button" class="jupwa-button" id="jupwa-button" aria-haspopup="true" aria-expanded="false">
        <svg width="56" height="56" aria-hidden="true">
            <use xlink:href="<?= $svg; ?>#bell_button"></use>
        </svg>
        <span class="sr-only"><?= Text::_(
                'PLG_JUPWA_WIDGET_SUBSCRIBETOPUSH_BUTTON'
            ) ?></span>
    </button>
    <div class="jupwa-panel jupwa-hidden" role="menu">
        <div class="jupwa-info">
            <div class="jupwa-head">
                <div class="jupwa-header">
                    <?= Text::_(
                        'PLG_JUPWA_WIDGET_HEADER'
                    ) ?>
                </div>
                <div class="jupwa-subheader">
                    <?= Text::_(
                        'PLG_JUPWA_WIDGET_SUBHEADER'
                    ) ?>
                </div>
            </div>

            <?php if (is_countable($items) && count($items) > 0): ?>
                <ul class="jupwa-information">
                    <?php foreach ($items as $item): ?>
                        <?php if ($item->text): ?>
                            <li>
                                <svg width="20" height="20" aria-hidden="true">
                                    <use xlink:href="<?= $svg; ?>#check"></use>
                                </svg>
                                <div><?= $item->text ?></div>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="jupwa-animation">
                    <div class="jupwa-pulse">
                        <div class="jupwa-pulse-icon">
                            <svg width="25" height="25" aria-hidden="true">
                                <use xlink:href="<?= $svg; ?>#bell"></use>
                            </svg>
                            <span class="sr-only"><?= Text::_(
                                    'COM_PLUGINS_PUSH_NOTIFICATIONS_FIELDSET_LABEL'
                                ) ?></span>
                        </div>
                        <div class="jupwa-pulse-placeholder">
                            <div class="jupwa-pulse-visual"></div>
                            <div class="jupwa-pulse-placeholder-space">
                                <div>
                                    <div class="jupwa-pulse-visual jupwa-pulse-visual2"></div>
                                    <div class="jupwa-pulse-visual jupwa-pulse-visual1"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="jupwa-alert" id="jupwa-alert" hidden></div>

            <div class="jupwa-action">
                <button id="jupwa-subscribe-btn" class="jupwa-subscribe">
                    <?= Text::_(
                        'PLG_JUPWA_WIDGET_SUBSCRIBE_BUTTON'
                    ) ?>
                </button>
                <button id="jupwa-unsubscribe-btn" class="jupwa-unsubscribe">
                    <?= Text::_(
                        'PLG_JUPWA_WIDGET_UNSUBSCRIBE_BUTTON'
                    ) ?>
                </button>
            </div>
        </div>
    </div>
</div>
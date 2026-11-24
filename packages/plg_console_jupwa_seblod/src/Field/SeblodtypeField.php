<?php
/**
 * @package     JU.Plugin
 * @subpackage  Console.JUPWASeblod
 *
 * @license     GNU General Public License version 3 or later.
 */

namespace JU\Plugin\Console\JUPWASeblod\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

defined('_JEXEC') or die;

class SeblodtypeField extends ListField
{
    protected $type = 'Seblodtype';

    /**
     *
     * @return array|object[]
     *
     * @since 2.0
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();

        if (!ComponentHelper::isEnabled('com_cck')) {
            return $options;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select([$db->quoteName('name', 'value'), $db->quoteName('title', 'text')])
            ->from($db->quoteName('#__cck_core_types'))
            ->where($db->quoteName('published').' = 1')
            ->where($db->quoteName('storage_location').' = '.$db->quote('joomla_article'))
            ->order($db->quoteName('title'));

        return array_merge($options, $db->setQuery($query)->loadObjectList());
    }
}

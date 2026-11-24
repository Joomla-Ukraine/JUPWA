<?php

namespace JU\Plugin\Jupwa\Seblod\Field;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or die;

class SeblodfieldsField extends ListField
{
    protected $type = 'Seblodfields';

    protected $layout = 'joomla.form.field.list-fancy-select';

    protected function getInput(): string
    {
        if (is_string($this->value)) {
            $this->value = array_values(array_filter(
                array_map('trim', explode(',', $this->value)),
                'strlen'
            ));
        }

        // Submit values in the order shown by Choices, independently of the option order.
        Factory::getApplication()->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
            document.addEventListener('formdata', ({ target: form, formData }) => {
                for (const field of form.querySelectorAll('[data-jupwa-seblod-fields]')) {
                    const select = field.querySelector('select');
                    if (!select.name || select.disabled || !field.choicesInstance) {
                        continue;
                    }

                    formData.delete(select.name);
                    for (const value of field.value) {
                        formData.append(select.name, value);
                    }
                }
            }, true);
            JS, ['name' => 'plg_jupwa_seblod.field-order']);

        return str_replace(
            '<joomla-field-fancy-select ',
            '<joomla-field-fancy-select allow-custom data-jupwa-seblod-fields ',
            parent::getInput()
        );
    }

    protected function getOptions(): array
    {
        static $available = null;

        if ($available === null) {
            $available = [];

            if (ComponentHelper::isEnabled('com_cck')) {
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $query = $db->getQuery(true)
                    ->select($db->quoteName(['name', 'title', 'storage_field']))
                    ->from($db->quoteName('#__cck_core_fields'))
                    ->where($db->quoteName('published').' = 1')
                    ->order($db->quoteName(['title', 'name']));

                foreach ($db->setQuery($query)->loadObjectList() as $field) {
                    $value = $field->storage_field ?: $field->name;
                    $available[$value] ??= HTMLHelper::_('select.option', $value, "$field->title ($value)");
                }
            }
        }

        $selected = [];
        $remaining = $available;

        // Keep saved templates and storage keys even when the current catalogue does not contain them.
        foreach ((array) $this->value as $value) {
            $selected[] = $available[$value] ?? HTMLHelper::_('select.option', $value, $value);
            unset($remaining[$value]);
        }

        return array_merge($selected, array_values($remaining));
    }
}

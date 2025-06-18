<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

defined('_JEXEC') or die;

#[AllowDynamicProperties]
class JFormFieldIntegration extends FormField
{
	protected $type = 'Integration';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getInput(): string
	{
		$db   = Factory::getContainer()->get(DatabaseInterface::class);
		$app  = Factory::getApplication();
		$lang = $app->getLanguage();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('jupwa'));
		$db->setQuery($query);
		$jupwa = $db->loadObjectList();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('ajax'));
		$query->where($db->quoteName('name') . ' LIKE ' . $db->quote('%jupwa%'));
		$db->setQuery($query);
		$ajax = $db->loadObjectList();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('console'));
		$query->where($db->quoteName('name') . ' LIKE ' . $db->quote('%jupwa%'));
		$db->setQuery($query);
		$console = $db->loadObjectList();

		$html = '';
		$html .= $this->getPluginsTable(Text::_('PLG_JUPWA_TITLE_GROUP_JUPWA'), $jupwa, $lang);
		$html .= $this->getPluginsTable(Text::_('PLG_JUPWA_TITLE_GROUP_CONSOLE'), $console, $lang);
		$html .= $this->getPluginsTable(Text::_('PLG_JUPWA_TITLE_GROUP_AJAX'), $ajax, $lang);

		return $html;
	}

	protected function getPluginsTable($name, $results, $lang)
	{
		$html = '
		<h3>' . $name . '</h3>
		<table class="table">
		  <thead>
		    <tr>
		      <th scope="col">' . Text::_('PLG_JUPWA_TITLE_EXTENSIONS') . '</th>
		      <th scope="col">' . Text::_('PLG_JUPWA_TITLE_STATUS') . '</th>
		    </tr>
		  </thead>
		<tbody>';

		foreach($results as $result)
		{
			$lang->load($result->name, JPATH_ADMINISTRATOR);
			$description = Text::_(json_decode($result->manifest_cache)->description);

			$html .= '<tr><td width="90%">';
			$html .= '<strong><a href="index.php?option=com_plugins&task=plugin.edit&extension_id=' . $result->extension_id . '" target="_blank">' . Text::_($result->name) . '</a></strong>';
			$html .= '<p><small class="form-text">' . $description . '</small></p>';
			$html .= '</td><td width="10%">';

			if($result->enabled == 1)
			{
				$html .= '<span class="tbody-icon active"><i class="icon-publish" aria-hidden="true"></i></span>';
			}
			else
			{
				$html .= '<span class="tbody-icon"><i class="icon-unpublish" aria-hidden="true"></i></span>';
			}

			$html .= '</td></tr>';
		}

		$html .= '</tbody></table>';

		return $html;
	}
}
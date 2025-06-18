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

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;

class Pkg_JUPWAInstallerScript
{
	/**
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function preflight()
	{
		$app = Factory::getApplication();

		if(version_compare(JVERSION, '4.0', 'lt'))
		{
			$app->enqueueMessage('Update for Joomla! 4.0 +', 'error');

			return false;
		}

		Folder::create(JPATH_SITE . '/favicons');
		Folder::create(JPATH_SITE . '/images/jupwa');
		Folder::create(JPATH_SITE . '/images/jupwa/icon');
		Folder::create(JPATH_SITE . '/images/jupwa/logos');
		Folder::create(JPATH_SITE . '/images/jupwa/icons');
		Folder::create(JPATH_SITE . '/images/jupwa/images');
		Folder::create(JPATH_SITE . '/images/jupwa/screenshots');
		Folder::create(JPATH_SITE . '/images/jupwa/watermark');

		return true;
	}

	public function postflight($type, $parent)
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
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('jupwa'));
		$db->setQuery($query);
		$system = $db->loadObjectList();

		$results = (object) array_merge((array) $system, (array) $jupwa, (array) $ajax);

		$html = '<div class="main-card p-4">
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

			$html .= '<tr><td><strong><a href="index.php?option=com_plugins&task=plugin.edit&extension_id=' . $result->extension_id . '" target="_blank">' . Text::_($result->name) . '</a></strong><br><small class="form-text">' . $description . '</small></td><td>';

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

		$html .= '</tbody></table></div>';

		echo $html;

		return true;
	}
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;

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
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where($db->quoteName('folder') . ' = ' . $db->quote('jupwa'));
		$db->setQuery($query);
		$results = $db->loadObjectList();

		$html = '<div class="main-card p-4">
		<table class="table">
		  <thead class="table-light">
		    <tr>
		      <th scope="col">Extensions</th>
		      <th scope="col">Status</th>
		    </tr>
		  </thead>
		<tbody>';

		foreach($results as $result)
		{
			$html .= '<tr><td><strong>' . $result->name . '</strong></td><td>';

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
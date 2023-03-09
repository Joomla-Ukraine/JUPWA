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

		if(version_compare(JVERSION, '3.10', 'lt'))
		{
			$app->enqueueMessage('Update for Joomla! 3.10 +', 'error');

			return false;
		}

		Folder::create(JPATH_SITE . '/img');

		if(!Folder::exists(JPATH_SITE . '/img'))
		{
			$app->enqueueMessage("Error creating folder '/img'. Please manually create the folder 'img' in the root of the site where you installed Joomla!", 'error');
		}

		return true;
	}
}
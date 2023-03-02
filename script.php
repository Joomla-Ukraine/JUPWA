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

class Pkg_JUPWAInstallerScript
{
	protected $dbSupport = ['mysql', 'mysqli', 'postgresql', 'sqlsrv', 'sqlazure'];
	protected $message;
	protected $status;

	/**
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 7.0
	 */
	public function preflight()
	{
		if(version_compare(JVERSION, '4.0', 'lt'))
		{
			Factory::getApplication()->enqueueMessage('Update for Joomla! 4+', 'error');

			return false;
		}

		if(!in_array(Factory::getDbo()->name, $this->dbSupport, true))
		{
			Factory::getApplication()->enqueueMessage(JText::_('PLG_JUPWA_ERROR_DB_SUPPORT'), 'error');

			return false;
		}

		$this->makeDir(JPATH_SITE . '/img');

		if(!is_dir(JPATH_SITE . '/img/'))
		{
			Factory::getApplication()->enqueueMessage("Error creating folder 'img'. Please manually create the folder 'img' in the root of the site where you installed Joomla!", 'error');
		}

		$cache = Factory::getCache('plg_jupwa');
		$cache->clean();

		return true;
	}

	/**
	 * @param $dir
	 *
	 * @return bool
	 *
	 * @since 7.0
	 */
	private function makeDir($dir)
	{
		if(@mkdir($dir, 0777, true) || is_dir($dir))
		{
			return true;
		}

		if(!$this->makeDir(dirname($dir)))
		{
			return false;
		}

		return mkdir($dir, 0777, true);
	}
}
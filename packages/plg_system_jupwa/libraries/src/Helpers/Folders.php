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

namespace JUPWA\Helpers;

use Joomla\Filesystem\Folder;

class Folders
{
	/**
	 * @param $path
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	public static function files($path): array
	{
		$folder = JPATH_BASE . $path;

		if(!(file_exists($folder) && is_dir($folder)))
		{
			Folder::create($folder);
		}

		$files = [];
		$dir   = opendir($folder);
		while(false !== ($currentFile = readdir($dir)))
		{
			if($currentFile === '.' || $currentFile === '..')
			{
				continue;
			}

			if(preg_match('/\.(jpg|jpeg|png|gif)/', strtolower($currentFile)))
			{
				$file    = $path . '/' . $currentFile;
				$files[] = trim($file, '/');
			}
		}

		closedir($dir);

		return $files;
	}
}
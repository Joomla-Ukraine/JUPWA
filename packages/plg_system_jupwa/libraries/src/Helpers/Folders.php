<?php
/**
 * @package     JUPWA\Helpers
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Helpers;

use Joomla\CMS\Filesystem\Folder;

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

		if(!Folder::exists($folder))
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
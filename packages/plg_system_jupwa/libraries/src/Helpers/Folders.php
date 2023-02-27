<?php
/**
 * @package     JUPWA\Helpers
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Helpers;

class Folders
{
	public static function files($path)
	{
		$files = [];
		$dir   = opendir(JPATH_BASE . $path);
		while(($currentFile = readdir($dir)) !== false)
		{
			if($currentFile === '.' || $currentFile === '..')
			{
				continue;
			}

			if(preg_match('/\.(jpg|jpeg|png|gif)/', mb_strtolower($currentFile)))
			{
				$file    = $path . '/' . $currentFile;
				$files[] = trim($file, '/');
			}
		}

		closedir($dir);

		return $files;
	}
}
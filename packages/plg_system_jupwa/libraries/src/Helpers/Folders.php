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
        $cleanPath = trim($path, DIRECTORY_SEPARATOR.' ');
        $fullPath = Path::clean(JPATH_SITE.DIRECTORY_SEPARATOR.$cleanPath);

        if (!Folder::exists($fullPath) && !Folder::create($fullPath)) {
            return [];
        }

        $filter = '\.(?:jpg|jpeg|png|gif|webp)$';
        $files = Folder::files($fullPath, $filter);

        if (empty($files)) {
            return [];
        }

        return array_map(
            static function ($file) use ($cleanPath) {
                return str_replace(DIRECTORY_SEPARATOR, '/', $cleanPath.'/'.$file);
            },
            $files
        );
    }
}
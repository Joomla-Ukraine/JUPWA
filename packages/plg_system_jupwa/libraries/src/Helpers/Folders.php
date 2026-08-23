<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2026 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

class Folders
{
    /**
     * @param string|null $path
     *
     * @return array
     *
     * @since 1.0
     */
    public static function files(?string $path): array
    {
        if (empty($path)) {
            return [];
        }

        $cleanPath = trim((string)$path, DIRECTORY_SEPARATOR.' /');
        $fullPath = Path::clean(JPATH_SITE.DIRECTORY_SEPARATOR.$cleanPath);

        if (!Folder::exists($fullPath) && !Folder::create($fullPath)) {
            return [];
        }

        $filter = '\.(?i:jpg|jpeg|png|gif|webp|avif)$';
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
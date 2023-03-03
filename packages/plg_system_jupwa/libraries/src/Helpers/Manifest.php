<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use JUPWA\Data\Data;

class Manifest
{
	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function create(array $option = [])
	{
		$app = Factory::getApplication();

		$manifest      = '/manifest.webmanifest';
		$file_manifest = JPATH_SITE . $manifest;

		$data                       = Data::$manifest;
		$data[ 'name' ]             = ($option[ 'param' ][ 'manifest_name' ] ? : $option[ 'site' ]);
		$data[ 'short_name' ]       = ($option[ 'param' ][ 'manifest_sname' ] ? : $option[ 'site' ]);
		$data[ 'description' ]      = $option[ 'param' ][ 'manifest_desc' ];
		$data[ 'scope' ]            = $option[ 'param' ][ 'manifest_scope' ];
		$data[ 'display' ]          = $option[ 'param' ][ 'manifest_display' ];
		$data[ 'orientation' ]      = $option[ 'param' ][ 'manifest_orientation' ];
		$data[ 'start_url' ]        = $option[ 'param' ][ 'manifest_start_url' ];
		$data[ 'background_color' ] = $option[ 'param' ][ 'meta_background_color' ];
		$data[ 'theme_color' ]      = $option[ 'param' ][ 'theme_color' ];

		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		if(File::write($file_manifest, $data))
		{
			$app->enqueueMessage(Text::sprintf('PLG_JUPWA_OS_NOT_UPDATE', '<code>' . $manifest . '</code>'), 'notice');
		}
		else
		{
			$app->enqueueMessage(JText::sprintf('PLG_JUPWA_OS_NOT_SAVE', '<code>' . $manifest . '</code>'), 'error');
		}
	}
}
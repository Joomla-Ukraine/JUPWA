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

use Joomla\CMS\Uri\Uri;

class Manifest
{
	public static function create(array $option = [])
	{
		$option[ 'json' ]->{'name'}       = ($option[ 'param' ][ 'manifest_name' ] ? : $option[ 'site' ]);
		$option[ 'json' ]->{'short_name'} = ($option[ 'param' ][ 'manifest_sname' ] ? : str_replace(' ', '', $option[ 'site' ]));

		if($option[ 'param' ][ 'manifest_desc' ])
		{
			$option[ 'json' ]->{'description'} = $option[ 'param' ][ 'manifest_desc' ];
		}

		if($option[ 'param' ][ 'manifest_scope' ])
		{
			$option[ 'json' ]->{'scope'} = $option[ 'param' ][ 'manifest_scope' ];
		}

		if($option[ 'param' ][ 'manifest_display' ])
		{
			$option[ 'json' ]->{'display'} = $option[ 'param' ][ 'manifest_display' ];
		}

		$option[ 'json' ]->{'start_url'} = $option[ 'param' ][ 'manifest_start_url' ] ? : Uri::root(true);

		//if($option['param'][ 'usepush' ] == 1)
		//{
		$option[ 'json' ]->{'gcm_sender_id'} = '482941778795';
		//}

		if($option[ 'param' ][ 'meta_background_color' ] !== '')
		{
			$option[ 'json' ]->{'background_color'} = $option[ 'param' ][ 'meta_background_color' ];
		}

		if($option[ 'param' ][ 'theme_color' ] !== '')
		{
			$option[ 'json' ]->{'theme_color'} = $option[ 'param' ][ 'theme_color' ];
		}

		if($option[ 'pwa_icons' ] === true)
		{
			$option[ 'json' ]->{'icons'} = $option[ 'json_icons' ]->icons;
		}

		return json_encode($option[ 'json' ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}
}
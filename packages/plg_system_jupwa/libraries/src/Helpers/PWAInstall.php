<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Helpers
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2025 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Helpers;

use Joomla\CMS\Uri\Uri;

class PWAInstall
{
	public static function panel($params): string
	{
		$disable_chrome = '';
		if($params->get('pwainstall_disablechrome') == 1)
		{
			$disable_chrome = ' disable-chrome="true"';
		}

		$local_storage = '';
		if($params->get('pwainstall_localstorage') == 1)
		{
			$local_storage = ' use-local-storage="true"';
		}

		return '<pwa-install id="pwa-install"' . $disable_chrome . $local_storage . ' manifest-url="' . Uri::root() . 'manifest.webmanifest"></pwa-install>';
	}
}
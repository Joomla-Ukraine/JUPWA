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
use Joomla\CMS\Uri\Uri;

class META
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
	public static function facebook(array $option = []): void
	{
		$doc = Factory::getDocument();

		if($option[ 'params' ]->get('fbpage') !== '')
		{
			$doc->setMetaData('article:publisher', $option[ 'params' ]->get('fbpage'), 'property');
		}

		if($option[ 'params' ]->get('fbapp') !== '')
		{
			$doc->setMetaData('fb:app_id', $option[ 'params' ]->get('fbapp'), 'property');
		}

		$fbadmins = (array) $option[ 'params' ]->get('fbadmin');
		$i        = 0;
		foreach($fbadmins as $fbadmin)
		{
			if($fbadmin->id)
			{
				$doc->setMetaData('fb:admins_' . ($i + 1), $fbadmin->id, 'property');
			}
			$i++;
		}
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function preconnect(array $option = []): void
	{
		$doc = Factory::getDocument();

		if($option[ 'params' ]->get('precnct-google') == 1 || $option[ 'params' ]->get('precnct-google-ads') == 1 || $option[ 'params' ]->get('precnct-google-cse') == 1)
		{
			$doc->addHeadLink('https://www.google.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-google-analytics') == 1)
		{
			$doc->addHeadLink('https://www.google-analytics.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://www.googletagmanager.com', 'dns-prefetch preconnect', 'rel');

		}

		if($option[ 'params' ]->get('precnct-google-fonts') == 1)
		{
			$doc->addHeadLink('https://fonts.googleapis.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-google-ads') == 1)
		{
			$doc->addHeadLink('https://pagead2.googlesyndication.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://googleads.g.doubleclick.net', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://tpc.googlesyndication.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://adservice.google.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://partner.googleadservices.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://fonts.googleapis.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-google-cse') == 1)
		{
			$doc->addHeadLink('https://cse.google.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://ssl.gstatic.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://clients1.google.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://www.googleapis.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-google-maps') == 1)
		{
			$doc->addHeadLink('https://maps.gstatic.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://maps.googleapis.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://fonts.gstatic.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://fonts.googleapis.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-google-cloudflare') == 1)
		{
			$doc->addHeadLink('https://cdnjs.cloudflare.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-youtube') == 1)
		{
			$doc->addHeadLink('https://www.youtube.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://i.ytimg.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://s.ytimg.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://yt3.ggpht.com', 'dns-prefetch preconnect', 'rel');
			$doc->addHeadLink('https://fonts.gstatic.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-facebook') == 1)
		{
			$doc->addHeadLink('https://graph.facebook.com', 'dns-prefetch preconnect', 'rel');
		}

		if($option[ 'params' ]->get('precnct-twitter') == 1)
		{
			$doc->addHeadLink('https://dn.api.twitter.com', 'dns-prefetch preconnect', 'rel');
		}

		$preconnects = (array) $option[ 'params' ]->get('preconnect');
		foreach($preconnects as $preconnect)
		{
			if($preconnect->url)
			{
				$doc->addHeadLink($preconnect->url, 'dns-prefetch preconnect', 'rel');
			}
		}

		$preloads = (array) $option[ 'params' ]->get('preloads');
		foreach($preloads as $preload)
		{
			if($preload->url)
			{
				$preload_as   = [ 'as' => $preload->as ];
				$preload_type = [];
				if($preload->type)
				{
					$preload_type = [ 'type' => $preload->type ];
				}

				$preload_co = [];
				if($preload->crossorigin)
				{
					$preload_co = [ 'crossorigin' => $preload->crossorigin ];
				}

				$preload_media = [];
				if($preload->media)
				{
					$preload_media = [ 'media' => $preload->media ];
				}

				$_preload = array_merge($preload_as, $preload_type, $preload_co, $preload_media);

				$doc->addHeadLink($preload->url, 'preload', 'rel', [ $_preload ]);
			}
		}
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function favicons(array $option = []): void
	{
		$doc = Factory::getDocument();

		if(is_file($favfolder . 'apple-touch-icon.png'))
		{
			$href    = $favsite . 'apple-touch-icon.png';
			$attribs = [ 'sizes' => '180x180' ];
			$doc->addHeadLink($href, 'apple-touch-icon', 'rel', $attribs);
		}

		if(is_file($favfolder . 'apple-touch-icon-precomposed.png.png'))
		{
			$href    = $favsite . 'apple-touch-icon-precomposed.png.png';
			$attribs = [ 'sizes' => '180x180' ];
			$doc->addHeadLink($href, 'apple-touch-icon', 'rel', $attribs);
		}

		if(is_file($favfolder . 'favicon-32x32.png'))
		{
			$href    = $favsite . 'favicon-32x32.png';
			$attribs = [
				'sizes' => '32x32',
				'type'  => 'image/png'
			];
			$doc->addHeadLink($href, 'icon', 'rel', $attribs);
		}

		if(is_file($favfolder . 'android-chrome-192x192.png'))
		{
			$href    = $favsite . 'android-chrome-192x192.png';
			$attribs = [
				'sizes' => '192x192',
				'type'  => 'image/png'
			];
			$doc->addHeadLink($href, 'icon', 'rel', $attribs);
		}

		if(is_file($favfolder . 'favicon-16x16.png'))
		{
			$href    = $favsite . 'favicon-16x16.png';
			$attribs = [
				'sizes' => '16x16',
				'type'  => 'image/png'
			];
			$doc->addHeadLink($href, 'icon', 'rel', $attribs);
		}

		if(is_file(JPATH_SITE . '/manifest.webmanifest'))
		{
			$doc->addHeadLink(Uri::base() . 'manifest.webmanifest', 'manifest', 'rel');
		}

		if(is_file($favfolder . 'safari-pinned-tab.svg'))
		{
			$href    = $favsite . 'safari-pinned-tab.svg';
			$attribs = [ 'color' => $option[ 'params' ]->get('maskiconcolor') ];
			$doc->addHeadLink($href, 'mask-icon', 'rel', $attribs);
		}

		if(is_file($favfolder . 'favicon.ico'))
		{
			$href = $favsite . 'favicon.ico';
			$doc->addHeadLink($href, 'shortcut icon', 'rel');
		}

		$doc->setMetaData('msapplication-TileColor', $option[ 'params' ]->get('msapplication_tilecolor'));

		if(is_file($favfolder . 'mstile-144x144.png'))
		{
			$href = $favsite . 'mstile-144x144.png';
			$doc->setMetaData('msapplication-TileImage', $href);
		}

		if(is_file($favfolder . 'browserconfig.xml'))
		{
			$href = $favsite . 'browserconfig.xml';
			$doc->setMetaData('msapplication-config', $href);
		}

		$doc->setMetaData('mobile-web-app-capable', 'yes');
		$doc->setMetaData('apple-mobile-web-app-capable', 'yes');
		$doc->setMetaData('application-name', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));
		$doc->setMetaData('apple-mobile-web-app-title', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));
		$doc->setMetaData('apple-mobile-web-app-status-bar-style', 'black-translucent');
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function tags(array $option = []): void
	{
		$doc = Factory::getDocument();

		if($option[ 'params' ]->get('theme_color') != '')
		{
			$doc->setMetaData('theme-color', $option[ 'params' ]->get('theme_color'));
		}
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function pwa(array $option = []): void
	{
		$doc = Factory::getDocument();

		if($option[ 'params' ]->get('usepwa', 0) == 1)
		{
			if($option[ 'params' ]->get('usepush') === '1' && $option[ 'params' ]->get('onesignal_app_id') !== '')
			{
				$pwajs = "if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
	     navigator.serviceWorker.register('" . Uri::base() . "OneSignalSDKWorker.js?v=" . hash('crc32b', $option[ 'params' ]->get('pwa_version')) . "');
	});
}";
			}
			else
			{
				$pwajs = "if ('serviceWorker' in navigator) {
	window.addEventListener('load', () => {
	     navigator.serviceWorker.register('" . Uri::base() . "sw.js?v=" . hash('crc32b', $option[ 'params' ]->get('pwa_version')) . "');
	});
}";
			}

			$doc->addScriptDeclaration($pwajs);
		}
	}
}
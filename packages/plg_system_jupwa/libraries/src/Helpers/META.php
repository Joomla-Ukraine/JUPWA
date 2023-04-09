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
use Joomla\CMS\Uri\Uri;
use JUPWA\Data\Data;

class META
{
	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function render(array $option = []): void
	{
		self::manifest();

		self::preconnect([ 'params' => $option[ 'params' ] ]);
		self::preloads([ 'params' => $option[ 'params' ] ]);

		self::icons_apple([ 'params' => $option[ 'params' ] ]);
		self::icons();

		self::splash([ 'params' => $option[ 'params' ] ]);

		self::tags([ 'params' => $option[ 'params' ] ]);

		self::pwa([ 'params' => $option[ 'params' ] ]);
	}

	/**
	 *
	 * @param array $option
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
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function preloads(array $option = []): void
	{
		$doc = Factory::getDocument();

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
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function preconnect(array $option = []): void
	{
		$doc = Factory::getDocument();

		$preconnect = Data::$preconnect;
		foreach($preconnect as $key => $val)
		{
			if($option[ 'params' ]->get('precnct-' . $key) == 1)
			{
				foreach($val as $link)
				{
					$doc->addHeadLink($link, 'dns-prefetch preconnect');
				}
			}
		}

		$preconnects = (array) $option[ 'params' ]->get('preconnect');
		foreach($preconnects as $preconnect)
		{
			if($preconnect->url)
			{
				$doc->addHeadLink($preconnect->url, 'dns-prefetch preconnect');
			}
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function splash(array $option = []): void
	{
		$doc = Factory::getDocument();

		$icons = Data::$splash;
		foreach($icons as $icon)
		{
			$file = 'favicons/splash_' . $icon[ 'width' ] . 'x' . $icon[ 'height' ] . '.png';
			if(File::exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addHeadLink($href, 'apple-touch-startup-image', 'rel', [
					'media' => 'screen and (device-width: ' . $icon[ 'd_width' ] . 'px) and (device-height: ' . $icon[ 'd_height' ] . 'px) and (-webkit-device-pixel-ratio: 2) and (orientation: ' . $icon[ 'orientation' ] . ')'
				]);
			}
		}
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function icons_apple(array $option = []): void
	{
		$doc   = Factory::getDocument();
		$icons = Data::$favicons;

		foreach($icons[ 'apple-touch-icon' ] as $icon)
		{
			$file = 'favicons/icon_' . $icon . '.png';
			if(File::exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addHeadLink($href, 'apple-touch-icon', 'rel', [ 'sizes' => $icon . 'x' . $icon ]);
			}
		}

		$doc->setMetaData('mobile-web-app-capable', 'yes');
		$doc->setMetaData('apple-mobile-web-app-capable', 'yes');
		$doc->setMetaData('application-name', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));
		$doc->setMetaData('apple-mobile-web-app-title', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));
		$doc->setMetaData('apple-mobile-web-app-status-bar-style', 'black-translucent');

		/*
				if(is_file($favfolder . 'safari-pinned-tab.svg'))
				{
					$href    = $favsite . 'safari-pinned-tab.svg';
					$attribs = [ 'color' => $option[ 'params' ]->get('maskiconcolor') ];
					$doc->addHeadLink($href, 'mask-icon', 'rel', $attribs);
				}

		*/

	}

	/**
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function icons(): void
	{
		$doc   = Factory::getDocument();
		$icons = Data::$favicons;

		foreach($icons[ 'icon' ] as $icon)
		{
			$file = 'favicons/icon_' . $icon . '.png';
			if(File::exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addHeadLink($href, 'icon', 'rel', [
					'sizes' => $icon . 'x' . $icon,
					'type'  => 'image/png'
				]);
			}
		}

		$file = 'favicons/favicon.ico';
		if(File::exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->addHeadLink($href, 'shortcut icon');
		}

		$file = 'favicons/browserconfig.xml';
		if(File::exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->setMetaData('msapplication-config', $href);
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function manifest(): void
	{
		$doc = Factory::getDocument();

		$file = 'manifest.webmanifest';
		if(File::exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->addHeadLink($href, 'manifest');
		}
	}

	/**
	 *
	 * @param array $option
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
	 * @param array $option
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
			$pwa_version = Manifest::getVersion();
			$pwajs       = "if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('" . Uri::base() . "sw.js?v=" . $pwa_version . "'); }); }";

			$doc->addScriptDeclaration($pwajs);
		}
	}
}
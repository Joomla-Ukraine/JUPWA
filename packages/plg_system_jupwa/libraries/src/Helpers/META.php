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

use Joomla\CMS\Factory;
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

		self::appstore([ 'params' => $option[ 'params' ] ]);
		self::googleplay([ 'params' => $option[ 'params' ] ]);

		self::preconnect([ 'params' => $option[ 'params' ] ]);
		self::preloads([ 'params' => $option[ 'params' ] ]);

		self::meta_apple([ 'params' => $option[ 'params' ] ]);
		self::meta_ms([ 'params' => $option[ 'params' ] ]);

		self::icons();

		self::splash([ 'params' => $option[ 'params' ] ]);

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
	public static function appstore(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(trim($option[ 'params' ]->get('appstore')) !== '')
		{
			$doc->setMetaData('apple-itunes-app', 'app-id=' . trim($option[ 'params' ]->get('appstore')));
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
	public static function googleplay(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if(trim($option[ 'params' ]->get('googleplay')) !== '')
		{
			$doc->setMetaData('google-play-app', 'app-id=' . trim($option[ 'params' ]->get('googleplay')));
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
	public static function facebook(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

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
		$app = Factory::getApplication();
		$doc = $app->getDocument();

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

				$doc->addHeadLink($preload->url, 'preload prefetch', 'rel', [ $_preload ]);
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
		$app = Factory::getApplication();
		$doc = $app->getDocument();

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
		$app   = Factory::getApplication();
		$doc   = $app->getDocument();
		$icons = Data::$splash;

		foreach($icons as $icon)
		{
			$file = 'favicons/splash_' . $icon[ 'width' ] . 'x' . $icon[ 'height' ] . '.png';
			if(file_exists(JPATH_SITE . '/' . $file))
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
	public static function meta_apple(array $option = []): void
	{
		$app   = Factory::getApplication();
		$doc   = $app->getDocument();
		$icons = Data::$favicons;

		foreach($icons[ 'apple-touch-icon' ] as $icon)
		{
			$file = 'favicons/appleicon_' . $icon . '.png';
			if(file_exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addCustomTag('<link href="' . $href . '" rel="apple-touch-icon" sizes="' . $icon . 'x' . $icon . '">');
			}
		}

		$doc->setMetaData('mobile-web-app-capable', 'yes');
		$doc->setMetaData('apple-mobile-web-app-capable', 'yes');
		$doc->setMetaData('application-name', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));
		$doc->setMetaData('apple-mobile-web-app-title', ($option[ 'params' ]->get('manifest_sname') ? : $option[ 'params' ]->get('manifest_name')));

		if($option[ 'params' ]->get('source_icon_svg_pin') && $option[ 'params' ]->get('maskiconcolor'))
		{
			$file = $option[ 'params' ]->get('source_icon_svg_pin');
			if(file_exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addHeadLink($href, 'mask-icon', 'rel', [ 'color' => $option[ 'params' ]->get('maskiconcolor') ]);
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
	public static function meta_ms(array $option = []): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if($option[ 'params' ]->get('msapplication_tilecolor'))
		{
			$doc->setMetaData('msapplication-TileColor', $option[ 'params' ]->get('msapplication_tilecolor'));
		}

		if($option[ 'params' ]->get('use_color_scheme') == 1)
		{
			$doc->addCustomTag('<meta name="color-scheme" content="light dark">');
		}

		if($option[ 'params' ]->get('theme_color'))
		{
			$doc->addCustomTag('<meta name="theme-color" content="' . $option[ 'params' ]->get('theme_color') . '" media="(prefers-color-scheme: light)">');
		}

		if($option[ 'params' ]->get('theme_color_dark'))
		{
			$doc->addCustomTag('<meta name="theme-color" content="' . $option[ 'params' ]->get('theme_color_dark') . '" media="(prefers-color-scheme: dark)">');
		}

		$file = 'favicons/browserconfig.xml';
		if(file_exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->setMetaData('msapplication-config', $href);
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function icons(): void
	{
		$app   = Factory::getApplication();
		$doc   = $app->getDocument();
		$icons = Data::$favicons;

		foreach($icons[ 'icon' ] as $icon)
		{
			$file = 'favicons/icon_' . $icon . '.png';
			if(file_exists(JPATH_SITE . '/' . $file))
			{
				$href = Uri::root() . $file;
				$doc->addHeadLink($href, 'icon', 'rel', [
					'sizes' => $icon . 'x' . $icon,
					'type'  => 'image/png'
				]);
			}
		}

		$file = 'favicons/favicon.ico';
		if(file_exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->addHeadLink($href, 'shortcut icon');
		}
	}

	/**
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function manifest(): void
	{
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		$file = 'manifest.webmanifest';
		if(file_exists(JPATH_SITE . '/' . $file))
		{
			$href = Uri::root() . $file;
			$doc->addHeadLink($href, 'manifest', 'rel', [
				'crossorigin' => 'use-credentials'
			]);
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
		$app = Factory::getApplication();
		$doc = $app->getDocument();

		if($option[ 'params' ]->get('usepwa', 0) == 1)
		{
			$pwa_version = Manifest::getVersion();
			$pwajs       = "if ('serviceWorker' in navigator) { window.addEventListener('load', () => { navigator.serviceWorker.register('" . Uri::base() . "sw.js?v=" . $pwa_version . "'); }); }";

			$doc->addScriptDeclaration($pwajs);
		}
	}
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Thumbs
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Thumbs;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use JUPWA\Classes\PHP_ICO;
use JUPWA\Data\Data;
use JUPWA\Utils\Image;

class Render
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
	public static function create(array $option = []): void
	{
		$path = JPATH_SITE . '/favicons';
		if(Folder::exists($path))
		{
			Folder::delete($path);
		}

		Folder::create($path);

		$favicon = self::ico([ 'source_icon_sm' => $option[ 'source_icon_sm' ] ]);

		$icons_s = self::icons([
			'size' => Data::$icons_sm,
			'icon' => $option[ 'source_icon_sm' ]
		]);

		$icons_b = self::icons([
			'size' => Data::$icons,
			'icon' => ($option[ 'source_icon' ] !== '' ? $option[ 'source_icon' ] : $option[ 'source_icon_sm' ])
		]);

		$icons_m = self::icons([
			'size' => Data::$manifest_icons,
			'name' => 'micon',
			'icon' => ($option[ 'source_icon_sm' ] !== '' ? $option[ 'source_icon_sm' ] : $option[ 'source_icon' ])
		]);

		$json = [
			'favicon_root'     => $favicon->root,
			'favicon_favicons' => $favicon->favicons,
			'icons'            => array_merge($icons_s, $icons_b),
			'manifest_icons'   => $icons_m,
			'shortcuts'        => self::shortcuts($option),
			'splash'           => self::splash($option),
			'article_logo'     => self::article_logo($option),
		];

		$json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		File::write(JPATH_SITE . '/favicons/thumbs.json', $json);
	}

	/**
	 *
	 * @param string $image
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function image(string $image): string
	{
		if(strpos($image, '#joomlaImage') === false)
		{
			return $image;
		}

		$image = explode('#joomlaImage', $image);

		return $image[ 0 ];
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function article_logo(array $option = [])
	{
		$source = self::image($option[ 'source_icon' ]);
		$width  = 600;
		$height = 60;
		$out    = 'favicons/logo_' . $width . 'x' . $height . '.png';

		return Image::render_image($source, $out, [
			'width'    => $width,
			'height'   => $height,
			'position' => 'left',
			'color'    => '#ffffff',
			'ratio'    => 0.6,
			'r'        => 15
		]);
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function splash(array $option = []): array
	{
		if(!$option[ 'source_icon' ])
		{
			return [];
		}

		$icons  = Data::$splash;
		$source = self::image($option[ 'source_icon' ]);

		$image = [];
		foreach($icons as $icon)
		{
			$width  = $icon[ 'width' ];
			$height = $icon[ 'height' ];
			$out    = 'favicons/splash_' . $width . 'x' . $height . '.png';

			$image[] = Image::render_image($source, $out, [
				'width'  => $width,
				'height' => $height,
				'ratio'  => 1.15,
				'color'  => $option[ 'ioscolor' ]
			]);
		}

		return $image;
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function icons(array $option = []): array
	{
		$icons  = $option[ 'size' ];
		$source = self::image($option[ 'icon' ]);
		$name   = ($option[ 'name' ] ? : 'icon');

		$image = [];
		foreach($icons as $icon)
		{
			$out = 'favicons/' . $name . '_' . $icon . '.png';

			$image[] = Image::render($source, $out, [
				'width'  => $icon,
				'height' => $icon
			]);
		}

		return $image;
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function shortcuts(array $option = []): array
	{
		$image     = [];
		$shortcuts = $option[ 'shortcuts' ] ?? [];

		if($shortcuts)
		{
			foreach($shortcuts as $key => $val)
			{
				$source = self::image($val[ 'icons' ]);
				$out    = 'favicons/shortcut_' . $val[ 'item' ] . '.png';

				$image[] = Image::render($source, $out, [
					'width'  => 192,
					'height' => 192
				]);
			}
		}

		return $image;
	}

	/**
	 *
	 * @param array $option
	 *
	 * @return object
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function ico(array $option = []): object
	{
		if($option[ 'source_icon_sm' ] !== '')
		{
			$source      = JPATH_SITE . '/' . self::image($option[ 'source_icon_sm' ]);
			$destination = JPATH_SITE . '/favicon.ico';
			$favicons    = JPATH_SITE . '/favicons/favicon.ico';
			$ico_lib     = new PHP_ICO($source, [ [ 32, 32 ], [ 64, 64 ] ]);

			$is_favicon = [ 'root' => '' ];
			if($ico_lib->save_ico($destination))
			{
				File::copy($destination, $favicons);

				$is_favicon = [ 'root' => 'favicon.ico' ];
			}

			$is_favicons = [ 'favicons' => '' ];
			if(File::exists($favicons))
			{
				$is_favicons = [ 'favicons' => 'favicons/favicon.ico' ];
			}

			return (object) array_merge($is_favicon, $is_favicons);
		}

		return (object) [ 'root' => '', 'favicons' => '' ];
	}
}
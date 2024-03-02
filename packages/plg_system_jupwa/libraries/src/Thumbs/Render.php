<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Thumbs
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Thumbs;

use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use JUPWA\Classes\PHP_ICO;
use JUPWA\Data\Data;
use JUPWA\Utils\Image;

class Render
{
	/**
	 *
	 * @param array $option
	 * @param       $app
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public static function create(array $option = [], $app = ''): void
	{
		$path = JPATH_SITE . '/favicons';
		if(file_exists($path) && is_dir($path))
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

		$json = [
			'favicon_root'     => $favicon->root,
			'favicon_favicons' => $favicon->favicons,
			'icons'            => array_merge($icons_s, $icons_b),
			'manifest_icons'   => self::manifest_icons($option),
			'shortcuts'        => self::shortcuts($option),
			'splash'           => self::splash($option),
			'article_logo'     => self::article_logo($option),
		];

		$json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		File::write(JPATH_SITE . '/favicons/thumbs.json', $json);

		if($app && !file_exists(JPATH_SITE . '/favicons/thumbs.json'))
		{
			$app->enqueueMessage(Text::_('PLG_JUPWA_THUMB_NOT_CREATED'), 'danger');
		}
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
	public static function article_logo(array $option = []): string
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
	public static function manifest_icons(array $option = []): array
	{
		if(!$option[ 'source_icon' ])
		{
			return [];
		}

		$icons = Data::$manifest_icons;

		$source_icon = ($option[ 'source_icon_sm' ] !== '' ? $option[ 'source_icon_sm' ] : $option[ 'source_icon' ]);
		$source      = self::image($source_icon);

		$image = [];
		foreach($icons as $icon)
		{
			$out     = 'favicons/micon_' . $icon . '.png';
			$image[] = Image::render_image($source, $out, [
				'width'  => $icon,
				'height' => $icon,
				'ratio'  => 1.14,
				'color'  => $option[ 'manifest_icon_background_color' ] == 1 ? $option[ 'background_color' ] : null
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
		$name   = (isset($option[ 'name' ]) && $option[ 'name' ] ? $option[ 'name' ] : 'icon');

		$image = [];
		foreach($icons as $icon)
		{
			$out     = 'favicons/' . $name . '_' . $icon . '.png';
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
					'width'  => 96,
					'height' => 96
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
			if(file_exists($favicons))
			{
				$is_favicons = [ 'favicons' => 'favicons/favicon.ico' ];
			}

			return (object) array_merge($is_favicon, $is_favicons);
		}

		return (object) [ 'root' => '', 'favicons' => '' ];
	}
}
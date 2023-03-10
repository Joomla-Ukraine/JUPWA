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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use JUPWA\Data\Data;
use JUPWA\Thumbs\Render;

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
	public static function create(array $option = []): void
	{
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
		$data[ 'screenshots' ]      = self::screenshots($option[ 'param' ]);
		$data[ 'icons' ]            = self::icons();
		$data[ 'shortcuts' ]        = self::shortcuts($option[ 'param' ]);
		$data[ 'categories' ]       = $option[ 'param' ][ 'manifest_categories' ];

		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		File::write($file_manifest, $data);
	}

	/**
	 *
	 * @return array
	 *
	 * @since 1.0
	 */
	private static function icons(): array
	{
		$sizes = Data::$manifest_icons;

		$icons = [];
		foreach($sizes as $size)
		{
			$file = 'favicons/icon_' . $size . '.png';
			if(File::exists(JPATH_SITE . '/' . $file))
			{
				$icons[] = [
					'src'     => Uri::root() . $file,
					'sizes'   => $size . 'x' . $size,
					'type'    => 'image/png',
					'purpose' => 'any'
				];
				$icons[] = [
					'src'     => Uri::root() . $file,
					'sizes'   => $size . 'x' . $size,
					'type'    => 'image/png',
					'purpose' => 'maskable'
				];
			}
		}

		return $icons;
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function shortcuts(array $option = []): array
	{
		$db = Factory::getDbo();

		$shortcuts = $option[ 'shortcuts' ] ?? [];
		$item      = [];
		if($shortcuts)
		{
			foreach($shortcuts as $key => $val)
			{
				$query = $db->getQuery(true);
				$query->select([
					'title',
					'link'
				]);
				$query->from('#__menu');
				$query->where($db->quoteName('id') . '=' . $db->Quote($val[ 'item' ]));
				$db->setQuery($query);
				$row = $db->loadObject();

				$icon = 'favicons/shortcut_' . $val[ 'item' ] . '.png';
				$file = '';
				if(File::exists(JPATH_SITE . '/' . $icon))
				{
					$file = Uri::root() . $icon;
				}

				$item[] = [
					'name'  => $row->title,
					'url'   => Route::link('site', $row->link, true, null, true),
					'icons' => [
						'src'   => $file,
						'type'  => 'image/png',
						'sizes' => '192x192'
					]

				];
			}
		}

		return $item;
	}

	/**
	 *
	 * @param   array  $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function screenshots(array $option = []): array
	{
		$screenshots = $option[ 'screenshots' ] ?? [];
		$screen      = [];

		if($screenshots)
		{
			foreach($screenshots as $key => $val)
			{
				$screen[] = Uri::root() . Render::image($val[ 'screen' ]);
			}
		}

		return $screen;
	}
}
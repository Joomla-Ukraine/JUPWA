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

use FastImageSize\FastImageSize;
use GuzzleHttp\Psr7\MimeType;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use JUPWA\Data\Data;
use JUPWA\Thumbs\Render;

class Manifest
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
		$manifest      = '/manifest.webmanifest';
		$file_manifest = JPATH_SITE . $manifest;

		$utc = Uri::root() . '?utm_source=pwa';

		$data                                           = Data::$manifest;
		$data[ 'name' ]                                 = ($option[ 'param' ][ 'manifest_name' ] ? : $option[ 'site' ]);
		$data[ 'short_name' ]                           = ($option[ 'param' ][ 'manifest_sname' ] ? : $option[ 'site' ]);
		$data[ 'description' ]                          = ($option[ 'param' ][ 'manifest_desc' ] ? : $option[ 'description' ]);
		$data[ 'lang' ]                                 = ($option[ 'param' ][ 'manifest_lang' ] ? : 'en');
		$data[ 'dir' ]                                  = $option[ 'param' ][ 'manifest_dir' ];
		$data[ 'scope' ]                                = ($option[ 'param' ][ 'manifest_scope' ] ? : Uri::root());
		$data[ 'display' ]                              = $option[ 'param' ][ 'manifest_display' ];
		$data[ 'display_override' ]                     = $option[ 'param' ][ 'manifest_display_override' ];
		$data[ 'orientation' ]                          = $option[ 'param' ][ 'manifest_orientation' ];
		$data[ 'start_url' ]                            = ($option[ 'param' ][ 'manifest_start_url' ] ? : $utc);
		$data[ 'id' ]                                   = ($option[ 'param' ][ 'manifest_id' ] ? : $utc);
		$data[ 'launch_handler' ]                       = self::launch_handler($option[ 'param' ]);
		$data[ 'background_color' ]                     = ($option[ 'param' ][ 'background_color' ] ? : '#fafafa');
		$data[ 'theme_color' ]                          = ($option[ 'param' ][ 'theme_color' ] ? : '#fafafa');
		$data[ 'prefer_related_applications' ]          = (bool) $option[ 'param' ][ 'prefer_related_applications' ];
		$data[ 'related_applications' ]                 = self::related_applications($option[ 'param' ]);
		$data[ 'scope_extensions' ]                     = self::scope_extensions($option[ 'param' ]);
		$data[ 'screenshots' ]                          = self::screenshots($option[ 'param' ]);
		$data[ 'icons' ]                                = self::icons();
		$data[ 'shortcuts' ]                            = self::shortcuts($option[ 'param' ]);
		$data[ 'handle_links' ]                         = ($option[ 'param' ][ 'manifest_handle_links' ] ?? []);
		$data[ 'categories' ]                           = ($option[ 'param' ][ 'manifest_categories' ] ?? []);
		$data[ 'edge_side_panel' ][ 'preferred_width' ] = (int) $option[ 'param' ][ 'manifest_edge_side_panel_width' ];

		if(is_countable($data[ 'screenshots' ]) && count($data[ 'screenshots' ]) == 0)
		{
			unset($data[ 'screenshots' ]);
		}

		if(is_countable($data[ 'icons' ]) && count($data[ 'icons' ]) == 0)
		{
			unset($data[ 'icons' ]);
		}

		if(is_countable($data[ 'shortcuts' ]) && count($data[ 'shortcuts' ]) == 0)
		{
			unset($data[ 'shortcuts' ]);
		}

		if(is_countable($data[ 'categories' ]) && count($data[ 'categories' ]) == 0)
		{
			unset($data[ 'categories' ]);
		}

		if(is_countable($data[ 'scope_extensions' ]) && count($data[ 'scope_extensions' ]) == 0)
		{
			unset($data[ 'scope_extensions' ]);
		}

		$data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		File::write($file_manifest, $data);
	}

	/**
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function addVersion(): void
	{
		$path = JPATH_SITE . '/favicons';
		Folder::create($path);

		$json = [
			'version' => hash('crc32b', time()),
		];

		$json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

		File::write($path . '/version.json', $json);
	}

	/**
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function getVersion(): string
	{
		$json = JPATH_SITE . '/favicons/version.json';
		if(file_exists($json))
		{
			$json = file_get_contents($json);

			return json_decode($json)->{'version'};
		}

		return '';
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
			$file = 'favicons/micon_' . $size . '.png';
			if(file_exists(JPATH_SITE . '/' . $file))
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
	 * @param array $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	private static function shortcuts(array $option = []): array
	{
		$db = Factory::getContainer()->get(DatabaseInterface::class);

		$shortcuts = $option[ 'shortcuts' ] ?? [];
		$item      = [];
		if($shortcuts)
		{
			foreach($shortcuts as $key => $val)
			{
				$language = '';
				if(!($val[ 'language' ] === '' || $val[ 'language' ] === '*'))
				{
					$lang_code = explode('-', $val[ 'language' ])[ 0 ];
					$language  = '/' . $lang_code . '/';
				}

				$query = $db->getQuery(true);
				$query->select([
					'title',
					'path'
				]);
				$query->from('#__menu');
				$query->where($db->quoteName('id') . '=' . $db->Quote($val[ 'item' ]));
				$db->setQuery($query);
				$row = $db->loadObject();

				$icon = 'favicons/shortcut_' . $val[ 'item' ] . '.png';
				$file = '';
				if(file_exists(JPATH_SITE . '/' . $icon))
				{
					$file = Uri::root() . $icon;
				}

				$item[] = [
					'name'  => $row->title,
					'url'   => $language . $row->path,
					'icons' => [
						[
							'src'   => $file,
							'type'  => 'image/png',
							'sizes' => '96x96'
						]
					]
				];
			}
		}

		return $item;
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
	private static function screenshots(array $option = []): array
	{
		$screenshots = $option[ 'screenshots' ] ?? [];
		$screen      = [];

		if($screenshots)
		{
			foreach($screenshots as $screenshot)
			{
				$file          = Render::image($screenshot[ 'screen' ]);
				$FastImageSize = new FastImageSize();
				$imageSize     = $FastImageSize->getImageSize(JPATH_SITE . '/' . $file);

				$sizes = '';
				if($imageSize !== false)
				{
					$sizes = $imageSize[ 'width' ] . 'x' . $imageSize[ 'height' ];
				}

				$screen[] = [
					'src'   => Uri::root() . $file,
					'sizes' => $sizes,
					'type'  => MimeType::fromFilename(JPATH_ROOT . '/' . $file)
				];
			}
		}

		return $screen;
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
	private static function related_applications(array $option = []): array
	{
		$my_webapp_pwa = $option[ 'my_webapp_pwa' ] ?? 0;
		$related_apps  = $option[ 'related_apps' ] ?? [];
		$item          = [];

		if($my_webapp_pwa && file_exists(JPATH_ROOT . '/manifest.webmanifest'))
		{
			$item[] = [
				'platform' => 'webapp',
				'url'      => Uri::root() . 'manifest.webmanifest'
			];
		}

		if($related_apps)
		{
			foreach($related_apps as $related_app)
			{
				$item[] = [
					'platform' => $related_app->related_apps_platforms ?? '',
					'url'      => $related_app->related_apps_url ?? '',
					'id'       => $related_app->related_apps_id ?? ''
				];
			}
		}

		return $item;
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
	private static function scope_extensions(array $option = []): array
	{
		$scope_extensions = $option[ 'manifest_scope_extensions' ] ?? [];
		$item             = [];

		if($scope_extensions)
		{
			foreach($scope_extensions as $scope)
			{
				$item[] = [
					'origin' => $scope[ 'domains' ]
				];
			}
		}

		return $item;
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
	private static function launch_handler(array $option = []): array
	{
		$launch_handler = $option[ 'manifest_launch_handler' ] ?? [];

		return [ 'client_mode' => [ implode(',', $launch_handler) ] ];
	}
}
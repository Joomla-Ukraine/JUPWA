<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Utils
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Utils;

use Intervention\Image\ImageManagerStatic as IImage;

class Image
{
	/**
	 *
	 * @param          $image
	 * @param          $image_out
	 * @param   array  $option
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function render($image, $image_out, array $option = []): string
	{
		$width  = $option[ 'width' ];
		$height = $option[ 'height' ];

		IImage::configure([ 'driver' => $option[ 'imagick' ] ?? 'imagick' ]);

		$img = IImage::make(JPATH_SITE . '/' . $image);

		if($img->width() > $width)
		{
			$img->resize($width, null, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		if($img->height() > $height)
		{
			$img->resize(null, $height, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		$img->resizeCanvas($width, $height, 'center', false, $option[ 'color' ] ?? null);
		$img->save(JPATH_SITE . '/' . $image_out);

		return $image_out;
	}

	/**
	 *
	 * @param          $image
	 * @param          $image_out
	 * @param   array  $option
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function render2($image, $image_out, array $option = []): string
	{
		$width  = $option[ 'width' ];
		$height = $option[ 'height' ];

		IImage::configure([ 'driver' => $option[ 'imagick' ] ?? 'imagick' ]);

		$img  = IImage::canvas($width, $height, $option[ 'color' ] ?? null);
		$logo = IImage::make(JPATH_SITE . '/' . $image);

		if($logo->width() > $width)
		{
			$logo->resize($width / 1.2, null, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		if($logo->height() > $height)
		{
			$logo->resize(null, $height / 1.2, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		$logo->resizeCanvas($width, $height, 'center', false, $option[ 'color' ] ?? null);
		$img->insert($logo, 'center');
		$img->save(JPATH_SITE . '/' . $image_out);

		return $image_out;
	}
}
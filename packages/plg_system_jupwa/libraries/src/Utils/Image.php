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
	 * @param array    $option
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function render($image, $image_out, array $option = []): string
	{
		$width    = $option[ 'width' ];
		$height   = $option[ 'height' ];
		$position = ($option[ 'position' ] ?? 'center');
		$color    = ($option[ 'color' ] ?? null);

		if(extension_loaded('imagick') && class_exists('Imagick'))
		{
			IImage::configure([ 'driver' => 'imagick' ]);
		}

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

		$img->resizeCanvas($width, $height, $position, false, $color);
		$img->save(JPATH_SITE . '/' . $image_out);

		return $image_out;
	}

	/**
	 *
	 * @param          $image
	 * @param          $image_out
	 * @param array    $option
	 *
	 * @return string
	 *
	 * @since 1.0
	 */
	public static function render_image($image, $image_out, array $option = []): string
	{
		$width    = $option[ 'width' ];
		$height   = $option[ 'height' ];
		$position = ($option[ 'position' ] ? : 'center');
		$color    = ($option[ 'color' ] ? : null);
		$ratio    = ($option[ 'ratio' ] ? : 1.2);
		$r        = ($option[ 'r' ] ? : 0);

		if(extension_loaded('imagick') && class_exists('Imagick'))
		{
			IImage::configure([ 'driver' => 'imagick' ]);
		}

		$img  = IImage::canvas($width, $height, $option[ 'color' ] ? : null);
		$logo = IImage::make(JPATH_SITE . '/' . $image);

		if($logo->width() > $width)
		{
			$logo->resize($width / $ratio, null, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		if($logo->height() > $height)
		{
			$logo->resize(null, $height / $ratio, static function ($constraint)
			{
				$constraint->aspectRatio();
			});
		}

		$logo->resizeCanvas($width, $height, $position, false, $color);
		$img->insert($logo, 'center', $r);
		$img->save(JPATH_SITE . '/' . $image_out);

		return $image_out;
	}
}
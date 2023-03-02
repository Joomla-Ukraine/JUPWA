<?php
/**
 * @package     JUPWA\Utils
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JUPWA\Utils;

use Intervention\Image\ImageManagerStatic as IImage;

class Image
{
	/**
	 *
	 * @param   array  $option
	 *
	 * @return void
	 *
	 * @since 1.0
	 */
	public static function render($image, array $option = []): void
	{
		$width  = $option[ 'width' ];
		$height = $option[ 'height' ];

		$icon_width = 512;
		if($width > $icon_width)
		{
			$icon_width = $width / 1.4;
		}

		IImage::configure([ 'driver' => $option[ 'imagick' ] ?? 'imagick' ]);

		$img = IImage::make(JPATH_SITE . '/images/jupwa/logo.png');
		/*
		 ->resize($icon_width, null, static function ($constraint)
		{
			$constraint->aspectRatio();
		})
		 */

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

		$img->resizeCanvas($width, $height, 'center', false, '#FAFAFA');
		$img->save(JPATH_SITE . '/out2.png');
	}
}
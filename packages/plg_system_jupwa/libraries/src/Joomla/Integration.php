<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Joomla
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Joomla;

class Integration
{
	/**
	 * @param $name
	 *
	 * @param $attr
	 *
	 * @return bool
	 *
	 * @since 1.0
	 */
	public static function is_com($name, $attr): bool
	{
		$file    = __DIR__ . '/' . $name . '.php';
		$options = ($attr[ 'option' ] === ($attr[ 'component' ] ?? $name));

		return $name && file_exists($file) && $options;
	}
}
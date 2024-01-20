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

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

class com_k2
{
	/**
	 *
	 * @param array $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function run(array $option = []): array
	{
		$id = Factory::getApplication()->input->getInt('id');
		if($id > 0)
		{
			$data  = [];
			$image = 'media/k2/items/src/' . md5('Image' . $id) . '.jpg';
			if(file_exists(JPATH_SITE . '/' . $image))
			{
				$data[ 'image' ] = $image;
			}

			return $data;
		}

		return [];
	}
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Joomla
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 **/

namespace JUPWA\Joomla;

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use JSFactory;

class com_jshopping
{
	/**
	 *
	 * @param   array  $option
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 1.0
	 */
	public function run(array $option = []): array
	{
		$app        = Factory::getApplication();
		$db         = Factory::getContainer()->get(DatabaseInterface::class);
		$id         = $app->input->getInt('product_id');
		$controller = $app->input->get('controller');

		if($controller === 'product' && $id > 0)
		{
			require_once JPATH_SITE . '/components/com_jshopping/lib/factory.php';

			$jshopConfig = JSFactory::getConfig();

			$query = $db->getQuery(true);
			$query->select([ 'image' ]);
			$query->from('#__jshopping_products');
			$query->where($db->quoteName('product_id') . ' = ' . $db->quote($id));
			$db->setQuery($query);
			$img = $db->loadResult();

			$data = [];
			if($img !== '')
			{
				$data[ 'image' ] = str_replace(JPATH_ROOT . '/', '', $jshopConfig->image_product_path) . '/full_' . $img;
			}

			return $data;
		}

		return [];
	}
}
<?php
/**
 * JUPWA plugin
 *
 * @version       1.x
 * @package       JUPWA\Classes
 * @author        Denys D. Nosov (denys@joomla-ua.org)
 * @copyright (C) 2023-2024 by Denys D. Nosov (https://joomla-ua.org)
 * @license       GNU General Public License version 2 or later; see LICENSE.md
 *
 * Copyright 2011-2016 Chris Jean & iThemes
 * Version 1.0.4
 **/

namespace JUPWA\Classes;

class SeblodAPI extends JCckContent
{
	/**
	 * @param $id
	 *
	 * @return object|bool
	 *
	 * @since 1.0
	 */
	public function loadContent($id): object|bool
	{
		if($id === null)
		{
			return true;
		}

		$base = JCckDatabase::loadObject('SELECT id, cck, pk, storage_location FROM #__cck_core WHERE storage_location = "joomla_article" AND pk = ' . (int) $id);

		$this->_type = $base->cck;
		$this->_pk   = $base->pk;
		$this->_id   = $base->id;

		if($this->_type || $this->_pk || $this->_id)
		{
			$this->_properties = JCckDatabase::loadObject('SELECT * FROM #__cck_store_form_' . $this->_type . ' WHERE id = ' . (int) $this->_pk);

			return (object) [
				'cck'        => $base->cck,
				'pk'         => $base->pk,
				'id'         => $base->id,
				'properties' => $this->_properties
			];
		}

		return true;
	}
}
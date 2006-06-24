<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Fully implemented DAO for NamedObject child.
	 * 
	 * @ingroup DAOs
	**/
	abstract class FinalNamedObjectDAO extends NamedObjectDAO
	{
		final public function import(NamedObject $no)
		{
			return parent::importNamed($no);
		}
		
		final public function makeObject(&$array, $prefix = null)
		{
			$class = $this->getObjectName();
			
			return parent::makeNamedObject($array, new $class, $prefix);
		}
		
		final protected function setQueryFields(
			InsertOrUpdateQuery $query, NamedObject $no
		)
		{
			return parent::setNamedQueryFields($query, $no);
		}
	}
?>
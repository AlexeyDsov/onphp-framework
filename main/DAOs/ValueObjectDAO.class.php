<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class ValueObjectDAO extends Singleton
	{
		abstract protected function makeSelf(&$array, $prefix = null);
		
		public function makeObject(&$array, $prefix = null)
		{
			return $this->makeSelf($array, $prefix);
		}
		
		public function makeCascade(
			/* Identifiable */ $object,
			&$array,
			$prefix = null
		)
		{
			return $object;
		}
		
		public function makeJoiners(
			/* Identifiable */ $object,
			&$array,
			$prefix = null
		)
		{
			return $object;
		}
	}
?>
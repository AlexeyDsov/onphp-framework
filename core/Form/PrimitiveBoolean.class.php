<?php
/****************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *   voxus@onphp.org, noiselist@pochta.ru                                   *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	class PrimitiveBoolean extends BasePrimitive
	{
		public function import(&$scope) // to be compatible with BasePrimitive
		{
			if (isset($scope[$this->name]))
				$this->value = true;
			else
				$this->value = false;

			return true;
		}
	}
?>
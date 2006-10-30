<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Sveta Smirnova                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class ExplodedPrimitive extends PrimitiveString
	{
		protected $separator = ' ';
		
		public function setSeparator($separator)
		{
			$this->separator = $separator;
			
			return $this;
		}
		
		public function getSeparator()
		{
			return $this->separator;
		}
		
		public function import($scope)
		{
			if (!$temp = parent::import($scope))
				return $temp;
	
			if ($this->value = explode($this->separator, $this->value)) {
				return true;
			} else {
				return false;
			}
			
			/* NOTREACHED */
		}
	}
?>
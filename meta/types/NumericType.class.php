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
	 * @ingroup Types
	**/
	final class NumericType extends FloatType
	{
		private $precision = 0;
		
		/**
		 * @return NumericType
		**/
		public function setPrecision($precision)
		{
			$this->precision = $precision;
			
			return $this;
		}
		
		public function getPrecision()
		{
			return $this->precision;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::NUMERIC)';
		}
	}
?>
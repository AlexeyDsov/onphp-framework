<?php
/***************************************************************************
 *   Copyright (C) 2006 by Nickolay G. Korolyov                            *
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
	class FloatType extends IntegerType
	{
		/**
		 * @throws WrongArgumentException
		 * @return FloatType
		**/
		public function setDefault($default)
		{
			Assert::isFloat(
				$default,
				"strange default value given - '{$default}'"
			);

			$this->default = $default;

			return $this;
		}

		public function toColumnType()
		{
			return 'DataType::create(DataType::REAL)';
		}

		public function toPrimitive()
		{
			return 'Primitive::float';
		}
		
		public function toXsdType()
		{
			return 'xsd:float';
		}
	}
?>
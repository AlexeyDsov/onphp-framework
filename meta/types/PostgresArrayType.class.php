<?php
/***************************************************************************
 *   Copyright (C) 2013 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	 * @see http://www.postgresql.org/docs/9.2/static/arrays.html
	**/
	class PostgresArrayType extends ObjectType
	{
		public function getPrimitiveName()
		{
			return 'pgarray';
		}
		
		public function isGeneric()
		{
			return true;
		}
		
		public function isMeasurable()
		{
			return true;
		}
		
		public function getDeclaration()
		{
			if ($this->hasDefault())
				return "'{$this->default}'";
		
			return 'null';
		}
		
		public function toColumnType()
		{
			return 'DataType::create(DataType::TEXT)';
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Connected to concrete table DBField.
	 * 
	 * @ingroup OSQL
	**/
	class SelectField extends FieldTable
	{
		private $alias = null;
		
		/**
		 * @return SelectField
		**/
		public static function create(DialectString $field, $alias)
		{
			return new self($field, $alias);
		}

		public function __construct(DialectString $field, $alias)
		{
			parent::__construct($field);
			$this->alias = $alias;
		}
		
		public function getAlias()
		{
			return $this->alias;
		}
		
		public function getName()
		{
			if ($this->field instanceof DBField)
				return $this->field->getField();
			
			return $this->alias;
		}

		public function toDialectString(Dialect $dialect)
		{
			return
				parent::toDialectString($dialect)
				.(
					$this->alias
						? ' AS '.$dialect->quoteField($this->alias)
						: null
				);
		}
	}
?>
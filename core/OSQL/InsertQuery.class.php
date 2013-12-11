<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup OSQL
	**/
	namespace Onphp;

	class InsertQuery extends InsertOrUpdateQuery
	{
		/**
		 * @var \Onphp\SelectQuery
		**/
		protected $select = null;
		
		/**
		 * @return \Onphp\InsertQuery
		**/
		public function into($table)
		{
			$this->table = $table;
			
			return $this;
		}
		
		/**
		 * Just an alias to behave like UpdateQuery.
		 *
		 * @return \Onphp\InsertQuery
		**/
		public function setTable($table)
		{
			return $this->into($table);
		}
		
		/**
		 * @return \Onphp\InsertQuery
		**/
		public function setSelect(SelectQuery $select)
		{
			$this->select = $select;
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$query = 'INSERT INTO '.$dialect->quoteTable($this->table).' ';
			
			if ($this->select === null) {
				$query = $this->toDialectStringValues($query, $dialect);
			} else {
				$query = $this->toDialectStringSelect($query, $dialect);
			}
			
			$query .= parent::toDialectString($dialect);
			
			return $query;
		}
		
		public function where(LogicalObject $exp, $logic = null)
		{
			throw new UnsupportedMethodException();
		}
		
		public function andWhere(LogicalObject $exp)
		{
			throw new UnsupportedMethodException();
		}
		
		public function orWhere(LogicalObject $exp)
		{
			throw new UnsupportedMethodException();
		}

		protected function toDialectStringValue($value, Dialect $dialect)
		{
			if ($value === null)
				return $dialect->literalToString(Dialect::LITERAL_NULL);
			elseif (true === $value)
				return $dialect->literalToString(Dialect::LITERAL_TRUE);
			elseif (false === $value)
				return  $dialect->literalToString(Dialect::LITERAL_FALSE);
			elseif ($value instanceof DialectString)
				return $value->toDialectString($dialect);
			else
				return $dialect->quoteValue($value);
		}
		
		protected function toDialectStringValues($query, Dialect $dialect)
		{
			$fields = array();
			$values = array();

			foreach ($this->fields as $var => $val) {
				$fields[] = $dialect->quoteField($var);
				$values[] = $this->toDialectStringValue($val, $dialect);
			}
			
			if (!$fields || !$values)
				throw new WrongStateException('what should i insert?');
			
			$fields = implode(', ', $fields);
			$values = implode(', ', $values);
			
			return $query . "({$fields}) VALUES ({$values})";
		}
		
		protected function toDialectStringSelect($query, Dialect $dialect)
		{
			$fields = array();
			
			foreach ($this->fields as $var => $val) {
				$fields[] = $dialect->quoteField($var);
			}
			
			if (!$fields)
				throw new WrongStateException('what should i insert?');
			if ($this->select->getFieldsCount() != count($fields))
				throw new WrongStateException('count of select fields must be equal with count of insert fields');
			
			$fields = implode(', ', $fields);
			
			return $query . "({$fields}) ("
				.$this->select->toDialectString($dialect).")";
		}
	}
?>
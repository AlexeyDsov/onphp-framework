<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexander A. Zaytsev                            *
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
	final class SQLCase extends Castable implements MappableObject
	{
		private $whenList	= array();
		private $thenList	= array();
		private $elseValue	= null;
		
		/**
		 * @return SQLCase
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return SQLCase
		**/
		public function add(LogicalObject $when, $then)
		{
			$this->whenList[] = $when;
			$this->thenList[] = $then;
			
			return $this;
		}
		
		/**
		 * @return SQLCase
		**/
		public function setElse($value)
		{
			$this->elseValue = $value;
			
			return $this;
		}
		
		/**
		 * @return SQLCase
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			$sqlCase = new self;
			
			foreach ($this->whenList as $index => $whenExpression) {
				$sqlCase->
					add(
						$dao->guessAtom($whenExpression, $query),
						$dao->guessAtom($this->thenList[$index], $query)
					);
			}
			
			if ($this->elseValue) {
				$sqlCase->setElse(
					$dao->guessAtom($this->elseValue, $query)
				);
			}
			
			return $sqlCase;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			$out = 'CASE';
			
			foreach ($this->whenList as $index => $whenExpression) {
				$thenValue = $this->thenList[$index];
				
				$out .=
					' WHEN '
					.$whenExpression->toDialectString($dialect)
					.' THEN '
					.(
						$thenValue instanceof DialectString
							? $thenValue->toDialectString($dialect)
							: $dialect->valueToString($thenValue)
					);

			}
			
			if ($this->elseValue) {
				$out .=
					' ELSE '
					.(
						$this->elseValue instanceof DialectString
							? $this->elseValue->toDialectString($dialect)
							: $dialect->valueToString($this->elseValue)
					);
			}
			
			$out .= ' END';
			
			return
				$this->cast
					? $dialect->toCasted("($out)", $this->cast)
					: $out;
		}
	}
?>
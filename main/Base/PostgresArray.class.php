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
	 * Mapping array of non-quoted postgres data types like integer, float, etc
	 *
	 * @ingroup Helpers
	**/
	namespace Onphp;
	
	class PostgresArray extends \ArrayObject implements Stringable, DialectString
	{
		protected $delim = ',';
		
		/**
		 * Create PostgresArray object by array or raw string
		 *
		 * @param mixed $mixed may be array or string
		 * @param string $delim one character as postgres array delimeter
		 * @return PostgresArray
		**/
		public static function create($mixed = null, $delim = ',')
		{
			return new self($mixed, $delim);
		}
		
		public function __construct($mixed = null, $delim = ',')
		{
			parent::__construct();
			
			$this->delim = $delim;
			
			if ($mixed) {
				$this->exchangeArray(
					is_array($mixed)
						? $mixed
						: $this->parseString($mixed)
				);
			}
		}
		
		public function toString()
		{
			return $this->convertToString($this->getArrayCopy());
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteValue($this->toString());
		}
		
		protected function convertRawToJson($rawData)
		{
			return strtr($rawData, '{}'.$this->delim, '[],');
		}
		
		protected function convertToString($val)
		{
			if (is_array($val)) {
				$stringList = array();
				
				foreach ($val as $subVal)
					$stringList[] = $this->convertToString($subVal);
				
				return '{'.implode($this->delim, $stringList).'}';
			}
			
			return $val;
		}
		
		private function parseString($rawData)
		{
			$resultArray =
				json_decode(
					$this->convertRawToJson($rawData)
				);
			
			if (json_last_error() != JSON_ERROR_NONE) {
				throw new WrongArgumentException(
					'json_decode() failed with code: '.json_last_error().'; '
					."Raw data value was '$rawData'"
				);
			}
			
			return $resultArray;
		}
	}
?>
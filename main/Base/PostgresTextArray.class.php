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
	 * Mapping array of quoted postgres data types like text
	 *
	 * @ingroup Helpers
	**/
	namespace Onphp;
	
	final class PostgresTextArray extends PostgresArray
	{
		/**
		 * Create PostgresTextArray object by array or raw string
		 *
		 * @param mixed $mixed may be array or string
		 * @param string $delim one character as postgres array delimeter
		 * @return PostgresTextArray
		**/
		public static function create($mixed = null, $delim = ',')
		{
			return new self($mixed, $delim);
		}
		
		protected function convertRawToJson($rawData)
		{
			if ($rawData == '{}')
				return '[]';
			
			return
				preg_replace_callback(
					'/({*)(?:("(?:[^"\\\\]|\\\\.)*")|([^}'.$this->delim
							.'{]+))(}*)('.$this->delim.')?/',
					function($matches) {
						return
							str_repeat('[', strlen($matches[1]))
							.($matches[2] ?: '"'.$matches[3].'"')
							.str_repeat(']', strlen($matches[4]))
							.(
								empty($matches[5])
									? ''
									: ','
							);
					},
					$rawData
				);
		}
		
		protected function convertToString($val)
		{
			if (is_array($val)) {
				$stringList = array();
				
				foreach ($val as $subVal)
					$stringList[] = $this->convertToString($subVal);
				
				return '{'.implode($this->delim, $stringList).'}';
			}
			
			return '"'.addslashes($val).'"';
		}
	}
?>
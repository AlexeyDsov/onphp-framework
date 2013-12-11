<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexander A. Klestov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Filters
	**/
	final class SplitFilter implements Filtrator
	{
		private $separator		= ' ';
		private $splitByRegExp	= false;
		
		/**
		 * @return SplitFilter
		 */
		public static function create() 
		{
			return new self();
		}
		
		public function getSeparator() 
		{
			return $this->separator;
		}

		/**
		 * @return SplitFilter 
		 */
		public function setSeparator($separator) 
		{
			$this->separator = $separator;
			
			return $this;
		}
		
		public function isSplitByRegExp() 
		{
			return $this->splitByRegExp;
		}

		public function setSplitByRegExp($splitByRegExp = true) 
		{
			$this->splitByRegExp = ($splitByRegExp === true);
		}

		public function apply($value) 
		{
			return 
				$this->isSplitByRegExp()
					? 
						preg_split(
							$this->separator,
							$this->value,
							-1,
							PREG_SPLIT_NO_EMPTY
						)
					: explode($this->separator, $value);
		}

	}
?>

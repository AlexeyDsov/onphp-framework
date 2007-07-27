<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Integer's interval implementation and accompanying utility methods.
	 * 
	 * @ingroup Helpers
	**/
	class Range implements Stringable
	{
		private $min = null;
		private $max = null;
		
		public function __construct($min = null, $max = null)
		{
			if ($min !== null)
				Assert::isInteger($min);
			
			if ($max !== null)
				Assert::isInteger($max);
			
			$this->min = $min;
			$this->max = $max;
		}
		
		public static function create($min = null, $max = null)
		{
			return new self($min, $max);
		}
		
		public static function lazyCreate($min = null, $max = null)
		{
			if ($min > $max)
				self::swap($min, $max);
			
			return new self($min, $max);
		}
		
		public function getMin()
		{
			return $this->min;
		}
		
		public function setMin($min = null)
		{
			if ($min !== null)
				Assert::isInteger($min);
			else
				return $this;
			
			iF (($this->max !== null) && $min > $this->max)
				throw new WrongArgumentException(
					'can not set minimal value, which is greater than maximum one'
				);
			else
				$this->min = $min;
			
			return $this;
		}
		
		public function getMax()
		{
			return $this->max;
		}
		
		public function setMax($max = null)
		{
			if ($max !== null)
				Assert::isInteger($max);
			else
				return $this;
			
			if (($this->min !== null) && $max < $this->min)
				throw new WrongArgumentException(
					'can not set maximal value, which is lower than minimum one'
				);
			else
				$this->max = $max;
			
			return $max;
		}

		/// atavism wrt BC
		public function toString($from = 'от', $to = 'до')
		{
			$out = null;
			
			if ($this->min)
				$out .= "{$from} ".$this->min;

			if ($this->max)
				$out .= " {$to} ".$this->max;
				
			return trim($out);
		}
		
		public function divide($factor, $precision = null)
		{
			if ($this->min)
				$this->min = round($this->min / $factor, $precision);

			if ($this->max)
				$this->max = round($this->max / $factor, $precision);
			
			return $this;
		}
		
		public function multiply($multiplier)
		{
			if ($this->min)
				$this->min = $this->min * $multiplier;
			
			if ($this->max)
				$this->max = $this->max * $multiplier;
			
			return $this;
		}

		public function equals(Range $range)
		{
			return ($this->min === $range->getMin() &&
					$this->max === $range->getMax());
		}
		
		public function isEmpty()
		{
			return
				($this->min === null)
				&& ($this->max === null);
		}
		
		public static function swap(&$a, &$b)
		{
			$c = $a;
			$a = $b;
			$b = $c;
		}
	}
?>
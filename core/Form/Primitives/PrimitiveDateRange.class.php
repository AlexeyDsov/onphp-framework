<?php
/***************************************************************************
 *   Copyright (C) 2007 by Igor V. Gulyaev                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveDateRange extends FiltrablePrimitive
	{
		private $className = null;
		
		/**
		 * @return PrimitiveDateRange
		**/
		public static function create($name)
		{
			return new self($name);
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDateRange
		**/
		public function of($class)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($this->getObjectName(), $class)
			);
			
			$this->className = $class;
			
			return $this;
		}
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveDateRange
		**/
		public function setDefault(/* DateRange */ $object)
		{
			$this->checkType($object);
			
			$this->default = $object;
			
			return $this;
		}
		
		public function importValue($value)
		{
			try {
				if ($value) {
					$this->checkType($value);
					
					if ($this->checkRanges($value)) {
						$this->value = $value;
						return true;
					} else {
						return false;
					}
				} else {
					return parent::importValue(null);
				}
			} catch (WrongArgumentException $e) {
				return false;
			}
		}
		
		public function import($scope)
		{
			if (parent::import($scope)) {
				try {
					$range = DateRangeList::makeRange($scope[$this->name]);
				} catch (WrongArgumentException $e) {
					return false;
				}
				
				if ($this->checkRanges($range)) {
					if (
						$this->className
						&& ($this->className != $this->getObjectName())
					) {
						$newRange = new $this->className;
						
						if ($start = $range->getStart())
							$newRange->setStart($start);
						
						if ($end = $range->getEnd())
							$newRange->setEnd($end);
						
						$this->value = $newRange;
						return true;
					}
					
					$this->value = $range;
					return true;
				}
			}
			
			return false;
		}
		
		protected function getObjectName()
		{
			return 'DateRange';
		}
		
		protected function checkRanges(DateRange $range)
		{
			return
				!($this->min && ($this->min->toStamp() < $range->getStartStamp()))
				&& !($this->max && ($this->max->toStamp() > $range->getEndStamp()));
		}
		
		/* void */ private function checkType($object)
		{
			Assert::isTrue(
				ClassUtils::isInstanceOf($objectList, $this->className)
			);
		}
	}
?>
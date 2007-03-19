<?php
/***************************************************************************
 *   Copyright (C) 2006 by Ivan Y. Khvostishkov, Konstantin V. Arkhipov    *
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
	final class PrimitiveEnumeration
		extends IdentifiablePrimitive
		implements ListedPrimitive
	{
		public function getList()
		{
			if ($this->value)
				return $this->value->getObjectList();
			elseif ($this->default)
				return $this->default->getObjectList();
			else {
				return new $this->className(
					call_user_func(array($this->className, 'getAnyId'))
				);
			}
			
			/* NOTREACHED */
		}
		
		public function of($className)
		{
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			Assert::isTrue(
				is_subclass_of($className, 'Enumeration'),
				"non-enumeration child given"
			);
			
			$this->className = $className;
			
			return $this;
		}
		
		public function importValue(/* Identifiable */ $value)
		{
			if ($value)
				Assert::isTrue(get_class($value) == $this->className);
			else
				return parent::importValue(null);
			
			return $this->import(array($this->getName() => $value->getId()));
		}
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveEnumeration '{$this->name}'"
				);
				
			$result = parent::import($scope);
				
			if ($result === true) {
				try {
					$this->value = new $this->className($this->value);
				} catch (MissingElementException $e) {
					$this->value = null;
					$result = false;
				}
			}
			
			return $result;
		}
	}
?>
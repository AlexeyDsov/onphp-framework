<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
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
	class PrimitiveIdentifier extends IdentifiablePrimitive
	{
		private $info = null;
		
		/**
		 * @throws WrongArgumentException
		 * @return PrimitiveIdentifier
		**/
		public function of($class)
		{
			$className = $this->guessClassName($class);
			
			Assert::isTrue(
				class_exists($className, true),
				"knows nothing about '{$className}' class"
			);
			
			$this->info = new ReflectionClass($className);
			
			Assert::isTrue(
				$this->info->implementsInterface('DAOConnected'),
				"class '{$className}' must implement DAOConnected interface"
			);
			
			$this->className = $className;
			
			return $this;
		}
		
		/**
		 * @return GenericDAO
		**/
		public function dao()
		{
			return call_user_func(array($this->className, 'dao'));
		}
		
		public function importValue($value)
		{
			if ($value instanceof Identifiable) {
				Assert::isTrue(
					ClassUtils::isInstanceOf($value, $this->className)
				);
				
				return
					$this->import(
						array($this->getName() => $value->getId())
					);
			} elseif ($value) {
				Assert::isInteger($value);
				
				return $this->import(array($this->getName() => $value));
			}
			
			return parent::importValue(null);
		}
		
		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for PrimitiveIdentifier '{$this->name}'"
				);
			
			$className = $this->className;

			if (
				isset($scope[$this->name])
				&& $scope[$this->name] instanceof $className
			) {
				$value = $scope[$this->name];

				$this->raw = $value->getId();
				$this->setValue($value);

				return $this->imported = true;
			}

			$result = parent::import($scope);
			
			if ($result === true) {
				try {
					$this->value = $this->dao()->getById($this->value);
				} catch (ObjectNotFoundException $e) {
					$this->value = null;
					return false;
				}
				
				return $this->imported = true;
			}
			
			return $result;
		}
	}
?>
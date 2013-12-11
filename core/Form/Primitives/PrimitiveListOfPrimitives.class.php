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
	 * @ingroup Primitives
	**/
	final class PrimitiveListOfPrimitives extends BasePrimitive
	{
		private $failOnFirst = true;
		
		/**
		 * @var BasePrimitive
		 */
		private $primitive = null;
		
		/**
		 * @return PrimitiveListOfPrimitives
		 */
		public static function create($name) 
		{
			return new self($name);
		}
		
		public function getPrimitive()
		{
			return $this->primitive;
		}

		public function setFailOnFirst($failOnFirst)
		{
			$this->failOnFirst = ($failOnFirst === true);
			
			return $this;
		}

		public function isFailOnFirst()
		{
			return $this->failOnFirst;
		}
		
		/**
		 * @return PrimitiveListOfPrimitives 
		 */
		public function setPrimitive(BasePrimitive $primitive)
		{
			$this->primitive = $primitive;
			
			return $this;
		}
		
		public function getPrimitiveList()
		{
			return $this->value;
		}
		
		public function getValue() 
		{
			return $this->getAdoptedValue('getValue');
		}
		
		public function getActualValue() 
		{
			return $this->getAdoptedValue('getActualValue');
		}
		
		public function getSafeValue() 
		{
			return $this->getAdoptedValue('getSafeValue');
		}
		
		public function import($scope)
		{
			Assert::isNotNull($this->primitive, 'Primitive must be set');
			
			if (!parent::import($scope))
				return null;
			
			if (is_array($this->raw)) {
				if ($this->primitive instanceof PrimitiveFile) {
					$result = array();
					
					for ($i = 0; $i < count($this->raw['name']); $i++) {
						$row = array();
						
						foreach (array_keys($this->raw) as $column) {
							$row[$column] = $this->raw[$column][$i];
						}
						
						$result[] = $row;
					}
					
					$this->raw = $result;
				}
				
				$result = true;
				
				$this->value = array();
				
				foreach ($this->raw as $rawValue) {
					$primitive = clone $this->primitive;
					
					$result = 
						($result || !$this->isFailOnFirst()) 
						&& $primitive->importValue($rawValue);
					
					$this->value[] = $primitive;
				}
				
				return $result;
			}
			
			return false;
		}
		
		private function getAdoptedValue($method)
		{
			if (!$this->value)
				return null;
			
			$result = array();
			
			foreach ($this->value as $primitive)
				$result[] = $primitive->$method();
			
			return $result;
		}
	}
?>

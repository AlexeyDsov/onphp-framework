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
	 * @ingroup DAOs
	**/
	abstract class ComplexBuilderDAO extends StorableDAO
	{
		abstract protected function makeSelf(&$array, $prefix = null);
		
		public function getMapping()
		{
			$proto = call_user_func(array($this->getObjectName(), 'proto'));
			
			return $proto->getMapping();
		}
		
		public function getFields()
		{
			static $fields = array();
			
			$className = $this->getObjectName();
			
			if (!isset($fields[$className])) {
				foreach (array_values($this->getMapping()) as $field) {
					if (is_array($field))
						$fields[$className] =
							array_merge(
								$fields[$className],
								$field
							);
					elseif ($field)
						$fields[$className][] = $field;
				}
			}
			
			return $fields[$className];
		}
		
		public function getJoinPrefix($field, $prefix = null)
		{
			return $this->getJoinName($field, $prefix).'__';
		}
		
		public function getJoinName($field, $prefix = null)
		{
			return dechex(crc32($prefix.$this->getTable())).'_'.$field;
		}
		
		public function makeObject(&$array, $prefix = null)
		{
			return
				$this->makeCascade(
					$this->selfSpawn($array, $prefix),
					$array,
					$prefix
				);
		}
		
		public function makeJoinedObject(&$array, $prefix = null)
		{
			return
				$this->makeJoiners(
					$this->selfSpawn($array, $prefix),
					$array,
					$prefix
				);
		}
		
		/// do not override this methods, unless you're MetaConfiguration builder
		//@{
		protected function makeJoiners(
			/* Identifiable */ $object, &$array, $prefix = null
		)
		{
			return $object;
		}
		
		protected function makeCascade(
			/* Identifiable */ $object, &$array, $prefix = null
		)
		{
			return $object;
		}
		//@}
		
		private function selfSpawn(&$array, $prefix = null)
		{
			if (isset($this->identityMap[$array[$prefix.'id']]))
				$object = $this->identityMap[$array[$prefix.'id']];
			else {
				$object = $this->makeSelf($array, $prefix);
				$this->identityMap[$object->getId()] = $object;
			}
			
			return $object;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	abstract class StorableDAO extends ProtoDAO
	{
		public function take(Identifiable $object)
		{
			return
				$object->getId()
					? $this->save($object)
					: $this->add($object);
		}
		
		public function add(Identifiable $object)
		{
			$object->setId(
				DBPool::getByDao($this)->obtainSequence(
					$this->getSequence()
				)
			);
			
			return
				$this->inject(
					$this->setQueryFields(
						OSQL::insert()->setTable($this->getTable()),
						$object
					),
					$object
				);
		}
		
		public function save(Identifiable $object)
		{
			return
				$this->inject(
					$this->setQueryFields(
						OSQL::update()->setTable($this->getTable())->where(
							Expression::eqId($this->getIdName(), $object)
						),
						$object
					)->
					// can't be changed anyway
					drop($this->getIdName()),
					$object
				);
		}
		
		public function import(Identifiable $object)
		{
			return
				$this->inject(
					$this->setQueryFields(
						OSQL::insert()->setTable($this->getTable()),
						$object
					),
					$object
				);
		}
		
		protected function inject(
			InsertOrUpdateQuery $query, Identifiable $object
		)
		{
			$this->checkObjectType($object);
			
			$db = DBPool::getByDao($this);
			
			if (!$db->isQueueActive()) {
				$count = $db->queryCount($query);
				
				$this->uncacheById($object->getId());
				
				if ($count !== 1)
					throw new WrongStateException(
						$count.' rows affected: racy or insane inject happened'
					);
			} else {
				$db->queryNull($query);
				
				$this->uncacheById($object->getId());
			}
			
			// clean out Identifier, if any
			return
				$this->identityMap[$object->getId()]
					= $object->setId($object->getId());
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

/*
	UnifiedContainer:

		child's and parent's field names:
			abstract public function getChildIdField()
			abstract public function getParentIdField()

		all we need from outer world:
			public function __construct(
				Identifiable $parent, UnifiedContainer $dao, $lazy = true
			)

		if you want to apply ObjectQuery's "filter":
			public function setObjectQuery(ObjectQuery $oq)

		first you should fetch whatever you want:
			public function fetch()

		then you can get it:
			public function getList()
		
		set you modified list:
			public function setList($list)

		finally, sync fetched data and stored one:
			public function save()

	OneToManyLinked <- UnifiedContainer:

		indicates whether child can be free (parent_id nullable):
			protected function isUnlinkable()

	ManyToManyLinked <- UnifiedContainer:

		helper's table name:
			abstract public function getHelperTable()

		id field name at parent's primary table:
			protected function getParentTableIdField()
*/

	/**
	 * IdentifiableObject childs collection handling.
	 * 
	 * @see StorableContainer for alternative
	 * 
	 * @ingroup Containers
	**/
	abstract class UnifiedContainer
	{
		protected $worker	= null;
		protected $parent	= null;

		protected $dao		= null;
		
		protected $lazy		= true;
		protected $fetched	= false;

		protected $list		= array();
		protected $clones	= array();
		
		abstract protected function getChildIdField();
		abstract protected function getParentIdField();

		public function __construct(
			DAOConnected $parent, GenericDAO $dao, $lazy = true
		)
		{
			Assert::isBoolean($lazy);
			
			$this->parent 	= $parent;
			$this->lazy		= $lazy;
			$this->dao		= $dao;

			$childClass = $dao->getObjectName();
			
			Assert::isTrue(
				new $childClass instanceof Identifiable,
				"child object should be at least Identifiable"
			);
		}
		
		public function getParentObject()
		{
			return $this->parent;
		}
		
		public function getDao()
		{
			return $this->dao;
		}
		
		public function isLazy()
		{
			return $this->lazy;
		}
		
		public function isFetched()
		{
			return $this->fetched;
		}

		public function setObjectQuery(ObjectQuery $oq)
		{
			Assert::isTrue(
				$this->dao instanceof MappedDAO,
				'you should implement MappedDAO to be able to use ObjectQueries'
			);
			
			$this->worker->setObjectQuery($oq);

			return $this;
		}
		
		public function setList($list)
		{
			Assert::isArray($list);
			
			$this->list = $list;
			
			return $this;
		}
		
		public function setListWithClones($list)
		{
			Assert::isArray($list);
			
			$this->list = $list;
			
			foreach ($this->list as $id => &$object)
				$this->clones[$id] = clone $object;
			
			return $this;
		}

		public function getList()
		{
			return $this->list;
		}
		
		public function fetch()
		{
			if (!$this->parent->getId())
				throw new WrongStateException(
					'save parent object first'
				);
			
			try {
				$this->fetchList();
			} catch (ObjectNotFoundException $e) {
				// yummy
			}
			
			$this->fetched = true;
			
			return $this;
		}
		
		public function save()
		{
			Assert::isArray(
				$this->list,
				"that's not an array :-/"
			);
			
			$list	= $this->list;
			$clones	= $this->clones;
			
			$insert = $delete = $update = array();
			
			if ($clones)
				$ids =
					array_merge(
						array_keys($list),
						array_keys($clones)
					);
			else
				$ids = array_keys($list);
			
			foreach ($ids as $id) {
				if (
					!$this->lazy
					&& isset($list[$id], $clones[$id])
					&& $list[$id] != $clones[$id]
				)
					$update[] = $list[$id];
				elseif (isset($list[$id]) && !isset($clones[$id]))
					$insert[] = $list[$id];
				elseif (isset($clones[$id]) && !isset($list[$id]))
					$delete[] = $clones[$id];
			}
			
			$db = DBFactory::getDefaultInstance();

			$db->queueStart()->begin();

			try {
				$this->worker->sync($insert, $update, $delete);
				
				$db->commit()->queueFlush();
			} catch (DatabaseException $e) {
				$db->queueDrop()->rollback();
				throw $e;
			}
			
			$this->dao->uncacheLists();

			return $this;
		}

		protected function fetchList()
		{
			$query = $this->worker->makeFetchQuery();
			
			if ($this->lazy) {
				$ids = $this->dao->getCustomRowList($query);
		
				foreach ($ids as $id) {
					$this->list[$id] = $id;
					$this->clones[$id] = $id;
				}
			} else {
				$this->list = ArrayUtils::convertObjectList(
					$this->dao->getListByQuery($query)
				);
	
				foreach ($this->list as $id => &$object)
					$this->clones[$id] = clone $object;
			}

			return $this;
		}
	}
?>
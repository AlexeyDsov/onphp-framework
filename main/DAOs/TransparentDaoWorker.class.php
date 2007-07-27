<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Basis for transparent DAO workers.
	 * 
	 * @see VoodooDaoWorker for obscure and greedy worker.
	 * @see SmartDaoWorker for less obscure locking-based worker.
	 * @see FileSystemDaoWorker for Voodoo's filesystem-based child.
	 * 
	 * @ingroup DAOs
	**/
	abstract class TransparentDaoWorker extends BaseDaoWorker
	{
		abstract protected function gentlyGetByKey($key);
		
		//@{
		// single object getters
		public function get(ObjectQuery $oq)
		{
			return $this->getByQuery($oq->toSelectQuery($this->dao));
		}
		
		public function getById($id)
		{
			$object = $this->getCachedById($id);
			
			if ($object) {
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $object;
			} else {
				$db = DBFactory::getDefaultInstance();

				$query = 
					$this->dao->makeSelectHead()->
					andWhere(
						Expression::eq(
							DBField::create('id', $this->dao->getTable()),
							$id
						)
					);

				if ($object = $db->queryObjectRow($query, $this->dao)) {
					return $this->cacheById($object);
				} else {
					$this->cacheNullById($id);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getByLogic(LogicalObject $logic)
		{
			return
				$this->getByQuery(
					$this->dao->makeSelectHead()->andWhere($logic)
				);
		}
		
		public function getByQuery(SelectQuery $query)
		{
			$object = $this->getCachedByQuery($query);
			
			if ($object) {
				
				if ($object === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $object;
				
			} else {
				$object = DBFactory::getDefaultInstance()->queryObjectRow(
					$query, $this->dao
				);
				
				if ($object)
					return $this->cacheByQuery($query, $object);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		
		public function getCustom(SelectQuery $query)
		{
			if ($query->getLimit() > 1)
				throw new WrongArgumentException(
					'can not handle non-single row queries'
				);

			$custom = $this->getCachedByQuery($query);
			
			if ($custom) {
				if ($custom === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $custom;
			} else {
				$custom = DBFactory::getDefaultInstance()->queryRow($query);
				
				if ($custom)
					return $this->cacheByQuery($query, $custom);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
		}
		//@}
		
		//@{
		// object's list getters
		public function getList(ObjectQuery $oq)
		{
			return $this->getListByQuery($oq->toSelectQuery($this->dao));
		}
		
		public function getListByIds($ids)
		{
			$list = array();
			$toFetch = array();
			
			foreach ($ids as $id) {
				if (
					!($cached = $this->getCachedById($id))
					|| ($cached === Cache::NOT_FOUND)
				) {
					$toFetch[] = $id;
				} else {
					$list[] = $cached;
				}
			}
			
			if (!$toFetch)
				return $list;
			
			try {
				return
					array_merge(
						$list,
						$this->getListByLogic(
							Expression::in('id', $toFetch)
						)
					);
			} catch (ObjectNotFoundException $e) {
				foreach ($toFetch as $id) {
					try {
						$list[] = $this->getById($id);
					} catch (ObjectNotFoundException $e) {
						// ignore
					}
				}

				return $list;
			}
			
			/* NOTREACHED */
		}
		
		public function getListByQuery(SelectQuery $query)
		{
			$list = $this->getCachedList($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBFactory::getDefaultInstance()->queryObjectSet(
					$query, $this->dao
				);
				
				if ($list)
					return $this->cacheListByQuery($query, $list);
				else {
					$this->cacheListByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			/* NOTREACHED */
		}
		
		public function getListByLogic(LogicalObject $logic)
		{
			return $this->getListByQuery(
				$this->dao->makeSelectHead()->andWhere($logic)
			);
		}
		
		public function getPlainList()
		{
			return $this->getListByQuery(
				$this->dao->makeSelectHead()
			);
		}
		//@}

		//@{
		// custom list getters
		public function getCustomList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			$list = $this->getCachedByQuery($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBFactory::getDefaultInstance()->querySet($query);
				
				if ($list)
					return $this->cacheByQuery($query, $list);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			/* NOTREACHED */
		}
		
		// TODO: rename to getCustomColumn
		public function getCustomRowList(
			SelectQuery $query, $expires = Cache::DO_NOT_CACHE
		)
		{
			if ($query->getFieldsCount() !== 1)
				throw new WrongArgumentException(
					'you should select only one row when using this method'
				);
			
			$list = $this->getCachedByQuery($query);
			
			if ($list) {
				if ($list === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $list;
			} else {
				$list = DBFactory::getDefaultInstance()->queryColumn($query);
				
				if ($list)
					return $this->cacheByQuery($query, $list);
				else {
					$this->cacheByQuery($query, Cache::NOT_FOUND);
					throw new ObjectNotFoundException();
				}
			}
			
			/* NOTREACHED */
		}
		//@}
		
		//@{
		// query result getters
		public function getCountedList(ObjectQuery $oq)
		{
			return $this->getQueryResult($oq->toSelectQuery($this->dao));
		}
		
		public function getQueryResult(SelectQuery $query)
		{
			$db = DBFactory::getDefaultInstance();

			$cache = Cache::me();
			
			$res = new QueryResult();
			
			$result = $this->getCachedByQuery($query);
			
			if ($result) {
				
				if ($result === Cache::NOT_FOUND)
					throw new ObjectNotFoundException();
				else
					return $result;

			} else {
				
				$list = $db->queryObjectSet($query, $this->dao);
				
				$count = clone $query;
			
				$count =
					$db->queryRow(
						$count->dropFields()->dropOrder()->limit(null, null)->
						get(SQLFunction::create('COUNT', '*')->setAlias('count'))
					);

				if (!$list) {
					$list = Cache::NOT_FOUND;
					
					$this->cacheByQuery($query, $list);
					
					throw new ObjectNotFoundException();
				} else {
					return
						$this->cacheByQuery(
							$query,
							$res->
								setList($list)->
								setCount($count['count'])->
								setQuery($query)
						);
				}
			}
		}
		//@}

		//@{
		// erasers
		public function dropByIds(/* array */ $ids)
		{
			$cache = Cache::me();
			
			$result =
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($this->dao->getTable())->
					where(Expression::in('id', $ids))
				);

			foreach ($ids as $id)
				$cache->mark($this->className)->delete($this->className.'_'.$id);
			
			$this->uncacheLists();

			return $result;
		}
		//@}
		
		//@{
		// cachers
		public function cacheById(Identifiable $object)
		{
			Cache::me()->mark($this->className)->
				add(
					$this->className.'_'.$object->getId(),
					$object,
					Cache::EXPIRES_FOREVER
				);
			
			return $object;
		}
		//@}
		
		//@{
		// uncachers
		public function uncacheById($id)
		{
			$this->uncacheLists();

			return parent::uncacheById($id);
		}
		
		public function uncacheByIds($ids)
		{
			$cache = Cache::me();
			
			foreach ($ids as $id)
				$cache->mark($this->className)->delete(
					$this->className.'_'.$id
				);
			
			return $this->uncacheLists();
		}
		//@}
		
		//@{
		// internal helpers
		public function getCachedByQuery(SelectQuery $query)
		{
			return
				$this->gentlyGetByKey(
					$this->className.self::SUFFIX_QUERY.$query->getId()
				);
		}
		
		protected function getCachedList(SelectQuery $query)
		{
			return
				$this->gentlyGetByKey(
					$this->className.self::SUFFIX_LIST.$query->getId()
				);
		}
		
		protected function cacheNullById($id)
		{
			static $null = Cache::NOT_FOUND;
			
			return 
				Cache::me()->mark($this->className)->
					add(
						$this->className.'_'.$id,
						$null,
						Cache::EXPIRES_FOREVER
					);
		}
		
		protected function keyToInt($key, $precision = 8)
		{
			return hexdec(substr(md5($key), 0, $precision)) + 1;
		}
		//@}
	}
?>
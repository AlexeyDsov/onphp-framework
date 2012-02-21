<?php
/***************************************************************************
 *   Copyright (C) 2009 by Solomatin Alexandr                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Когда-то в году 2009 Solomatin A. и Denisov A. задумали сделать к onPHP
	 *   кэширование не просто по ключу а еще и по тегам
	 *   (т.е. когда у одних и тех же данных несколько ключей и данные считаются протухшими если протух хотя бы один ключ)
	 * 
	 * В данном случае воркер кладет в мемкеш не просто объект а массив содержащий объект + набор тегов с номерами их версий.
	 * Когда из кеша такой массив достается, то смотрятся версии тегов в массиве и текущие версии тегов.
	 *   Если они отличаются - данные в кеше считаются протухшими
	 * 
	 * + у этого воркера должен сетиться TaggableHandler (это либо TaggableLayerHandleer либо TaggableSmartHandler)
	 * Воркер у хендлера спрашивает какие теги нужно использовать в каком случае. Например:
	 *  - какие теги нужно раскешить про раскешивание объекта
	 *  - какие теги нужно использовать при кешировании запроса
	 *  и т.д.
	 * 
	 * TaggableLayerHandler вместе с TaggableDaoWorker'ом по поведению вместе должны вести как CacheDaoWorker
	 * 
	 * TaggableSmartHandler же должен вести себя более хитро и лучше показать на примере:
	 *   Допустим у нас есть база в которой есть объекты Сообщения и Темы.
	 *   И у нас закешены: объекты по своим id-шникам (темы, сообщения) + список тем + список сообщений для конкретной темы.
	 *   Таким образом при изменении:
	 *     - темы должна раскешиться тема + список тем;
	 *	   - сообщения должно раскешиться сообщение + список сообщений для конкретной темы;
	 * 
	 **************
	 * 
	 * Это было описано как оно должно работать в идеале. Сейчас я перенес из старого старого проекта это as is.
	 * На текущий взгляд мне не равится текущий механизм кастомных тегов (через статические методы) + плохо вообще помню что из реализованного работает.
	 * 
	 * + Была бага со связями то ли OneToMany то ли ManyToMany - они нормально то ли не кешились то ли не раскешивались, т.к. у них запросы шли в обход этого воркера.
	 * + Механизм хитрого (Smart) кеширования реализован далеко не полностью, т.к. не дружит с join'ами и его желательно реализовать, если возможно.
	 * + Нет комментариев по коду и спустя три года очень смутно все это вспоминается.
	 * + На реальном но не большом проекте где мы использовали это кеширование время от времени вылетал DeadlockException в котором так и не разобрались.
	 * + В TaggableSmartHandler'е сейчас вообще какой-то мрак на первый взгляд :) Надо долго разбираться и понавесить комментариев.
	 * + Воркер не сильно совместим с другими воркерами. Т.е. если одно дао работает под одним воркером а другое под этим - будут происходить чудеса нераскешивания.
	 * + По предыдущей позиции возможно здесь стоит сделать маппинг для какого DAO какой Handler использовать.
	 * 
	 * Соотвественно этот код сейчас не стоит использовать на продакшене, но это пример или основа того что можно сделать для более умного кеширования.
	 * Если у кого-то есть желание доработать, обсудить - отлично. У самого руки не быстро до кода доходят в свободное от работы время :)
	 * 
	 * @ingroup DAOs
	**/
	final class TaggableDaoWorker extends TransparentDaoWorker
	{
		const MAX_RANDOM_ID	= 134217728;
		const TAG_VERSIONS	= 'tag_versions';
		const KEY_POSTFIX	= 'taggable';
		const LOCK_TIMEOUT	= 1600;		// msec
		const LOCK_PREFIX	= 'lock_';

		private static $handler = null;

		private static $customTags = null;

		public static function setHandler($handler)
		{
			Assert::classExists($handler);

			self::$handler = new $handler();
		}

		public static function setCustomTags($tags)
		{
			Assert::isArray($tags);

			self::$customTags = $tags;
		}

		public static function dropCustomTags()
		{
			self::$customTags = null;
		}

		public function expireTags($tags)
		{
			Assert::isArray($tags);

			$this->updateTags($tags);

			return $this;
		}

		/// cachers
		//@{
		protected function cacheByQuery(
			SelectQuery $query,
			/* Identifiable */ $object,
			$expires = Cache::EXPIRES_FOREVER
		)
		{
			$key = $this->makeQueryKey($query, self::SUFFIX_QUERY);

			Cache::me()->
				mark($this->className)->
				set(
					$key,
					array(
						'tags' => $this->getTagsForQuery($query),
						'data' => $object,
					),
					$expires
				);

			SemaphorePool::me()->free(self::LOCK_PREFIX.$key);

			return $object;
		}

		protected function cacheById(
			Identifiable $object, $expires = Cache::EXPIRES_FOREVER
		)
		{
			if ($expires !== Cache::DO_NOT_CACHE) {

				Cache::me()->
					mark($this->className)->
					set(
						$this->makeIdKey($object->getId()),
						array(
							'tags' => $this->getTagsForObject($object),
							'data' => $object,
						),
						$expires
					);
			}

			return $object;
		}

		protected function cacheListByQuery(
			SelectQuery $query,
			/* array || Cache::NOT_FOUND */ $array,
			$expires = Cache::EXPIRES_FOREVER
		)
		{
			if ($array !== Cache::NOT_FOUND) {
				Assert::isArray($array);
				Assert::isTrue(current($array) instanceof Identifiable);
			}

			$key = $this->makeQueryKey($query, self::SUFFIX_LIST);

			Cache::me()->
				mark($this->className)->
				set(
					$key,
					array(
						'tags' => $this->getTagsForQuery($query),
						'data' => $array,
					),
					$expires
				);

			SemaphorePool::me()->free(self::LOCK_PREFIX.$key);

			return $array;
		}

		protected function cacheNullById($id, $expires = Cache::EXPIRES_FOREVER)
		{
			return
				Cache::me()->
					mark($this->className)->
					add(
						$this->makeIdKey($id),
						array(
							'tags' => $this->getTagsForNullObject($id),
							'data' => Cache::NOT_FOUND,
						),
						$expires
					);
		}
		//@}

		/// getters
		//@{
		public function getCachedById($id)
		{
			$result =
				Cache::me()->
					mark($this->className)->
					get($this->makeIdKey($id));

			if ($this->checkValid($result))
				return $result['data'];

			return null;
		}

		public function getListByIds(array $ids, $expires = Cache::EXPIRES_FOREVER)
		{
			$list = array();
			$toFetch = array();
			$prefixed = array();

			$proto = $this->dao->getProtoClass();

			$proto->beginPrefetch();

			// dupes, if any, will be resolved later @ ArrayUtils::regularizeList
			$ids = array_unique($ids);

			foreach ($ids as $id)
				$prefixed[$id] = $this->makeIdKey($id);

			$cachedList =
				Cache::me()->
					mark($this->className)->
					getList($prefixed);

			if (
				$cachedList
			) {
				foreach ($cachedList as $cached) {
					if ($this->checkValid($cached)) {
						$cached = $cached['data'];
						if ($cached && ($cached !== Cache::NOT_FOUND)) {
							$list[] = $this->dao->completeObject($cached);

							unset($prefixed[$cached->getId()]);
						}
					}
				}
			}

			$toFetch += array_keys($prefixed);

			if ($toFetch) {
				try {
					$list =
						array_merge(
							$list,
							$this->getListByLogic(
								Expression::in(
									new DBField(
										$this->dao->getIdName(),
										$this->dao->getTable()
									),
									$toFetch
								),
								$expires
							)
						);
				} catch (ObjectNotFoundException $e) {
					// nothing to fetch
				}
			}

			$proto->endPrefetch($list);

			return $list;
		}
		//@}

		/// uncachers
		//@{
		public function uncacheById($id)
		{
			$this->baseUncacheById($id);

			return	$this->dao->uncacheLists();
		}

		public function uncacheByIds($ids)
		{
			foreach ($ids as $id)
				$this->baseUncacheById($id);

			return $this->dao->uncacheLists();
		}

		public function uncacheLists()
		{
			$tags = self::$handler->getDefaultTags($this->className);
			$this->updateTags($tags);

			return true;
		}

		//@}

		/// internal helpers
		//@{
		protected function gentlyGetByKey($key)
		{
			$result =
				Cache::me()->mark($this->className)->get($key);

			if ($this->checkValid($result)) {
				return $result['data'];
			}

			$pool = SemaphorePool::me();

			if (!$pool->get(self::LOCK_PREFIX.$key)) {
				if ($result && isset($result['data'])) {
					return $result['data'];
				} else {
					for ($msec = 0; $msec <= self::LOCK_TIMEOUT; $msec += 200) {
						usleep(200*1000);
						if ($pool->get(self::LOCK_PREFIX.$key)) {
							$result =
								Cache::me()->mark($this->className)->get($key);

							$pool->free(self::LOCK_PREFIX.$key);

							if ($this->checkValid($result)) {
								return $result['data'];
							} else {
								// лока уже нет, а кэш не перестроился
								break;
							}
						}
					}
					// не дождались снятия лока
					throw new DeadLockException(
						"Cache deadlock. {$this->className} QueryKey={$key}"
					);
				}
			}

			return null;
		}

		protected function baseUncacheById($id)
		{
			try {
				$object = $this->dao->getById($id);
				$this->updateTagVersions($object);
			} catch (ObjectNotFoundException $e) {
				$object = null;
			}

			Cache::me()->
				mark($this->className)->
				delete($this->makeIdKey($id));
		}

		protected function checkValid($item)
		{
			return
				$item
				&& isset($item['data'])
				&& isset($item['tags'])
				&& $this->checkTagVersions($item['tags']);
		}

		/**
		 * узнает список тегов которые используются в запросе,
		 */
		protected function getTagsForQuery(SelectQuery $query)
		{
			if (self::$customTags) {
				$tags = self::$customTags;
			} else {
				$tags = self::$handler->getQueryTags($query, $this->className);
			}

			$tagList = array();
			foreach ($tags as $tag) {
				$tagList[$tag] = 0;
			}

			return $this->getTagVersions($tagList);
		}

		protected function getTagsForNullObject($id)
		{
			$tags = self::$handler->getNullObjectTags($id, $this->className);
			$tagList = array();
			foreach ($tags as $tag) {
				$tagList[$tag] = 0;
			}

			return $this->getTagVersions($tagList);
		}

		protected function getTagVersions(/*array*/ $tags)
		{
			$time = microtime(true);
			$tagsToFetch = array_keys($tags);

			if (
				!$result =
					Cache::me()->
						mark(self::TAG_VERSIONS)->
						getList($tagsToFetch)
			) {
				$result = array();
			}

			$fetchedTags = array();
			foreach ($tagsToFetch as $tag) {
				$fetched = false;
				foreach ($result as $key => $value) {
					if (strpos($key, $tag) !== false) {
						$fetched = true;
						$fetchedTags[$tag] = $value;
					}
				}
				if (!$fetched) {
					Cache::me()->
						mark(self::TAG_VERSIONS)->
						set(
							$tag,
							$time,
							Cache::EXPIRES_FOREVER
						);

					$fetchedTags[$tag] = $time;
				}
			}

			return $fetchedTags;
		}

		/**
		 * проверяет версии тегов
		 */
		protected function checkTagVersions(/*array*/ $tags)
		{
			$tagVersions = $this->getTagVersions($tags);
			if ($tagVersions == $tags) {
				return true;
			}

			return false;
		}

		protected function updateTagVersions(IdentifiableObject $object)
		{
			$tags = self::$handler->getUncacheObjectTags($object, $this->className);

			$this->updateTags($tags);

			return true;
		}

		protected function getTagsForObject(IdentifiableObject $object)
		{
			$tags = self::$handler->getCacheObjectTags($object, $this->className);
			$tagList = array();
			foreach ($tags as $tag) {
				$tagList[$tag] = 0;
			}

			return $this->getTagVersions($tagList);
		}

		protected function updateTags($tags)
		{
			$time = microtime(true);
			foreach ($tags as $tag) {
				Cache::me()->
					mark(self::TAG_VERSIONS)->
					set(
						$tag,
						$time,
						Cache::EXPIRES_FOREVER
					);
			}

			return true;
		}

		protected function makeIdKey($id)
		{
			return parent::makeIdKey($id).self::KEY_POSTFIX;
		}

		protected function makeQueryKey(SelectQuery $query, $suffix)
		{
			return parent::makeQueryKey($query, $suffix).self::KEY_POSTFIX;
		}
		//@}
	}
?>
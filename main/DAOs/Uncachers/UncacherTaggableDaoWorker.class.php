<?php
/***************************************************************************
 *   Copyright (C) 2012 by Aleksey S. Denisov                              *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Uncachers
	**/
	class UncacherTaggableDaoWorker implements UncacherBase
	{
		private $classNameMap = array();
		
		/**
		 * @return UncacherTaggableDaoWorker
		 */
		public static function create($className, $idKey, array $tags = array())
		{
			return new self($className, $idKey, $tags);
		}
		
		public function __construct($className, $idKey, array $tags = array())
		{
			$idKeyList = $idKey ? array($idKey) : array();
			$this->classNameMap[$className] = array($idKeyList, $tags);
		}
		
		/**
		 * @return array
		 */
		public function getClassNameMap()
		{
			return $this->classNameMap;
		}
		/**
		 * @param $uncacher UncacherNullDaoWorker same as self class
		 * @return BaseUncacher (this)
		 */
		public function merge(UncacherBase $uncacher)
		{
			Assert::isInstance($uncacher, 'UncacherTaggableDaoWorker');
			return $this->mergeSelf($uncacher);
		}
		
		public function uncache()
		{
			foreach ($this->classNameMap as $className => $uncaches) {
				list($idKeys, $tags) = $uncaches;
				$dao = ClassUtils::callStaticMethod("$className::dao");
				/* @var $dao StorableDAO */
				$worker = Cache::worker($dao);
				Assert::isInstance($worker, 'TaggableDaoWorker');
				
				$worker->expireTags($tags);
				
				foreach ($idKeys as $key)
					Cache::me()->mark($className)->delete($key);
				
				$dao->uncacheLists();
			}
		}
		
		private function mergeSelf(UncacherTaggableDaoWorker $uncacher) {
			foreach ($uncacher->getClassNameMap() as $className => $uncaches) {
				if (!isset($this->classNameMap[$className])) {
					$this->classNameMap[$className] = $uncaches;
				} else {
					//merging idkeys
					$this->classNameMap[$className][0] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className][0],
						$uncaches[0]
					);
					//merging tags
					$this->classNameMap[$className][1] = ArrayUtils::mergeUnique(
						$this->classNameMap[$className][1],
						$uncaches[1]
					);
				}
			}
			return $this;
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp;
	
	final class CascadeCache extends CachePeer
	{
		const LOCAL_NULL_VALUE		= 'local_nil';
		const NEGATIVE_CACHE_ON		= true;
		const NEGATIVE_CACHE_OFF	= false;
		
		private $localPeer		= null;
		private $remotePeer		= null;
		private $className		= null;
		private $cacheStrategy	= null;
		
		// map class -> local ttl
		private $localTtlMap	= array();
		
		/**
		 * @return CascadeCache
		**/
		public static function create(
			CachePeer $localPeer,
			CachePeer $remotePeer,
			$cacheStrategy = self::NEGATIVE_CACHE_ON
		)
		{
			return new self($localPeer, $remotePeer, $cacheStrategy);
		}
		
		public function __construct(
			CachePeer $localPeer,
			CachePeer $remotePeer,
			$cacheStrategy = self::NEGATIVE_CACHE_ON
		)
		{
			$this->localPeer = $localPeer;
			$this->remotePeer = $remotePeer;
			$this->cacheStrategy = $cacheStrategy;
		}
		
		/**
		 * @return CascadeCache
		**/
		public function mark($className)
		{
			$this->className = $className;
			
			$this->localPeer->mark($className);
			$this->remotePeer->mark($className);
			
			return $this;
		}
		
		/**
		 * @return CascadeCache
		**/
		public function setLocalTtlMap(array $map)
		{
			$this->localTtlMap = $map;
			
			return $this;
		}
		
		public function increment($key, $value)
		{
			$value = $this->remotePeer->increment($key, $value);
			
			$this->cacheLocal($key, $value);
			
			return $value;
		}
		
		public function decrement($key, $value)
		{
			$value = $this->remotePeer->decrement($key, $value);
			
			$this->cacheLocal($key, $value);
			
			return $value;
		}
		
		public function getList($indexes)
		{
			$valueList = $this->localPeer->getList($indexes);
			
			$returnList = array();
			foreach ($valueList as $key => $value)
				if ($value !== self::LOCAL_NULL_VALUE)
					$returnList[$key] = $value;
			
			$fetchIndexes = array_diff($indexes, array_keys($valueList));
			if (!empty($fetchIndexes)) {
				$valueList = $this->remotePeer->getList($fetchIndexes);
				$returnList = array_merge($returnList, $valueList);
				
				foreach ($fetchIndexes as $key)
					$this->cacheLocal(
						$key,
						array_key_exists($key, $valueList)
							? $valueList[$key]
							: null
					);
			}
			
			return $returnList;
		}
		
		public function get($key)
		{
			$value = $this->localPeer->get($key);
			
			if ($value === self::LOCAL_NULL_VALUE)
				return null;
			
			if (!$value) {
				$value = $this->remotePeer->get($key);
				
				$this->cacheLocal($key, $value);
			}
			
			return $value;
		}
		
		public function delete($key)
		{
			$result = $this->remotePeer->delete($key);
			
			$this->cacheLocal($key, null);
			
			return $result;
		}
		
		/**
		 * @return CascadeCache
		**/
		public function clean()
		{
			$this->remotePeer->clean();
			$this->localPeer->clean();
			
			return $this;
		}
		
		public function isAlive()
		{
			return $this->localPeer->isAlive() && $this->remotePeer->isAlive();
		}
		
		public function append($key, $data)
		{
			$result = $this->remotePeer->append($key, $data);
			
			$this->localPeer->delete($key);
			
			return $result;
		}
		
		protected function store(
			$action, $key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			$result = $this->remotePeer->store($action, $key, $value, $expires);
			
			if ($result)
				$this->cacheLocal($key, $value, $expires);
			elseif ($action == 'replace')
				$this->cacheLocal($key, null, $expires);
			
			return $result;
		}
		
		/**
		 * @return CascadeCache
		**/
		private function cacheLocal(
			$key, $value, $expires = Cache::EXPIRES_MEDIUM
		)
		{
			if (!$value) {
				if ($this->cacheStrategy == self::NEGATIVE_CACHE_ON)
					$value = self::LOCAL_NULL_VALUE;
				else
					return $this;
			}
			
			if (array_key_exists($this->className, $this->localTtlMap))
				$expires = $this->localTtlMap[$this->className];
			
			$this->localPeer->set($key, $value, $expires);
			
			return $this;
		}
	}
?>
<?php
/****************************************************************************
 *   Copyright (C) 2005-2006 by Anton E. Lebedevich, Konstantin V. Arkhipov *
 *   noiselist@pochta.ru, voxus@onphp.org                                   *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */
	
	/**
	 * System-wide access to selected CachePeer and DaoWorker.
	 *
	 * @see CachePeer
	 * @see http://onphp.org/examples/Cache.html
	 * 
	 * @ingroup Cache
	 * 
	 * @example cacheSettings.php
	**/
	final class Cache extends StaticFactory
	{
		const NOT_FOUND			= 'nil';

		const EXPIRES_FOREVER	= 259200; // 3 days
		const EXPIRES_MAXIMUM	= 21600; // 6 hrs
		const EXPIRES_MEDIUM	= 3600; // 1 hr
		const EXPIRES_MINIMUM	= 300; // 5 mins
		
		const DO_NOT_CACHE		= -2005;
		
		/// map dao -> worker
		private static $map		= null;
		
		/// selected peer
		private static $peer	= null;
		
		/// default worker
		private static $worker	= null;
		
		public static function me()
		{
			if (!self::$peer || !self::$peer->isAlive())
				self::$peer = new ReferencePool(new RuntimeMemory());
			
			return self::$peer;
		}

		public static function setPeer(CachePeer $peer)
		{
			self::$peer = $peer;
		}
		
		public static function setDefaultWorker($worker)
		{
			self::$worker = $worker;
		}
		
		/**
		 * associative array, className -> workerName
		**/
		public static function setDaoMap($map)
		{
			self::$map = $map;
		}
		
		public static function worker(GenericDAO $dao)
		{
			static $instances = array();
			
			$class = get_class($dao);
			
			if (!isset($instances[$class])) {
				
				if (isset(self::$map[$class])) {
					$className = self::$map[$class];
					$instances[$class] = new $className($dao);
				} elseif ($worker = self::$worker)
					$instances[$class] = new $worker($dao);
				else
					$instances[$class] = new CommonDaoWorker($dao);
			}
			
			return $instances[$class];
		}
	}
?>
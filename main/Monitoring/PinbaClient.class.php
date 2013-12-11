<?php
/****************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                             *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * Simple wrapper to pinba php extention
	 * @see http://pinba.org/
	 */
	namespace Onphp;

	final class PinbaClient extends Singleton
	{
		private static $enabled = null;
		private $timers = array();
		private $queue = array();
		private $treeLogEnabled = false;
		private $hostName = "localhost";
		private $firstUniq = null;
		private $idShift = 1;
		private $suffix = null;
		
		
		/**
		 * @return \Onphp\PinbaClient
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public static function isEnabled()
		{
			if (self::$enabled === null)
				self::$enabled = ini_get("pinba.enabled") === "1";
			
			return self::$enabled;
		}
		
		public function setTreeLogEnabled($orly = true)
		{
			$this->treeLogEnabled = ($orly === true);
			
			return $this;
		}
		
		public function isTreeLogEnabled()
		{
			return $this->treeLogEnabled;
		}
		
		public function getTreeQueue()
		{
			return $this->queue;
		}
		
		public function timerStart($name, array $tags, array $data = array())
		{
			$name .= $this->suffix;

			if (array_key_exists($name, $this->timers))
				throw new WrongArgumentException('the timer with name '.$name.' allready exists');
			
			if ($this->isTreeLogEnabled()) {
				if (empty($this->firstUniq)) {
					$this->firstUniq = uniqid($this->hostName);
				}
				
				//must be uniq for 20 minutes
				$id = $this->firstUniq.':'.($this->idShift++);
				$tags['treeId'] = $id;
				
				if (!empty($this->queue))
					$tags['treeParentId'] = end($this->queue);
				else
					$tags['treeParentId'] = 'root';
				
				$this->queue[] = $id;
			}
			
			$this->timers[$name] =
				count($data)
					? pinba_timer_start($tags, $data)
					: pinba_timer_start($tags);
			
			return $this;
		}
		
		public function timerStop($name)
		{
			if ($this->isTreeLogEnabled())
				array_pop($this->queue);
			 
			$name .= $this->suffix;

			if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			 
			pinba_timer_stop($this->timers[$name]);
			  
			unset($this->timers[$name]);
			  
			return $this;
		}
		
		public function isTimerExists($name)
		{
			return array_key_exists($name.$this->suffix, $this->timers);
		}
		
		public function timerDelete($name)
		{
			$name .= $this->suffix;

			if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			
			pinba_timer_delete($this->timers[$name]);
			
			unset($this->timers[$name]);
			
			return $this;
		}
		
		public function timerGetInfo($name)
		{
			$name .= $this->suffix;

			if (!array_key_exists($name, $this->timers))
				throw new WrongArgumentException('have no any timer with name '.$name);
			
			return pinba_timer_get_info($this->timers[$name]);
		}
		
		public function setScriptName($name)
		{
			pinba_script_name_set($name);
			
			return $this;
		}
		
		public function setHostName($name)
		{
			$this->hostName = $name;
			pinba_hostname_set($name);
			
			return $this;
		}

		public function setSuffix($suffix)
		{
			$this->suffix = $suffix;

			return $this;
		}

		public function getSuffix()
		{
			return $this->suffix;
		}

		public function dropSuffix()
		{
			$this->suffix = null;

			return $this;
		}

		/**
		 * NOTE: You don't need to flush data manually. Pinba do it for you.
		 */
		public function flush()
		{
			pinba_flush();
		}
	}
?>

<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Simple filesystem cache requiring external manual cleanup.
	 * 
	 * @ingroup Cache
	**/
	final class RubberFileSystem extends CachePeer
	{
		private $directory	= null;
		
		public static function create($directory = 'cache/')
		{
			return new RubberFileSystem($directory);
		}

		public function __construct($directory = 'cache/')
		{
			$directory = ONPHP_TEMP_PATH.$directory;
			
			if (!is_writable($directory)) {
				if (!mkdir($directory, 0700, true)) {
					throw new WrongArgumentException(
						"can not write to '{$directory}'"
					);
				}
			}
			
			if ($directory[strlen($directory) - 1] != DIRECTORY_SEPARATOR)
				$directory .= DIRECTORY_SEPARATOR;
			
			$this->directory = $directory;
		}
		
		public function isAlive()
		{
			return is_writable($this->directory);
		}
		
		public function clean()
		{
			// TODO: reimplement on php-level
			return `rm -rf {$this->directory}*`;
		}

		public function get($key)
		{
			$path = $this->makePath($key);
			
			if (is_readable($path)) {
				
				if (filemtime($path) <= time()) {
					try {
						unlink($path);
					} catch (BaseException $e) {
						// we're in race with unexpected clean()
					}
					return null;
				}
				
				return $this->operate($path);
			}
			
			return null;
		}
		
		public function delete($key)
		{
			try {
				unlink($this->makePath($key));
				return true;
			} catch (BaseException $e) {
				return false;
			}
		}
		
		protected function store($action, $key, $value, $expires = 0)
		{
			$path = $this->makePath($key);
			$time = time();
			
			$directory = dirname($path);
			
			if (!file_exists($directory)) {
				try {
					mkdir($directory);
				} catch (BaseException $e) {
					// we're in race
				}
			}
			
			// do not add, if file exist and not expired
			if (
				$action == 'add'
				&& is_readable($path)
				&& filemtime($path) > $time
			)
				return true;
			
			// do not replace, when file not exist or expired
			if (
				$action == 'replace'
			) {
				if (!is_readable($path)) {
					return false;
				} elseif (filemtime($path) <= $time) {
					$this->delete($key);
					return false;
				}
			}
			
			$this->operate($path, $value, $expires);
			
			return true;
		}
		
		private function operate($path, $value = null, $expires = null)
		{
			$key = hexdec(substr(md5($path), 3, 2)) + 1;

			$pool = SemaphorePool::me();
			
			if (!$pool->get($key))
				return null;
			
			try {
				$old = umask();
				umask(0077);
				$fp = fopen($path, $value !== null ? 'wb' : 'rb');
				umask($old);
			} catch (BaseException $e) {
				$pool->drop($key);
				return null;
			}
			
			if ($value !== null) {
				fwrite($fp, $this->prepareData($value));
				fclose($fp);
				
				if ($expires < parent::TIME_SWITCH)
					$expires += time();
				
				touch($path, $expires);
				
				return $pool->drop($key);
			} else {
				if (($size = filesize($path)) > 0)
					$data = fread($fp, $size);
				
				fclose($fp);
				
				$pool->drop($key);
				
				return $this->restoreData($data);
			}
			
			/* NOTREACHED */
		}
		
		private function makePath($key)
		{
			return
				$this->directory
				.$key[0].$key[1]
				.DIRECTORY_SEPARATOR
				.substr($key, 2);
		}
	}
?>
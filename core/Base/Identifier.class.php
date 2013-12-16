<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Garmonbozia Research Group                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @see Identifiable
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	namespace Onphp;

	final class Identifier implements Identifiable
	{
		private $id		= null;
		/**
		 * @var IdentifiableObject
		 */
		private $object = null;
		private $final	= false;
		/**
		 * @var Identifier
		 */
		private $proxy = null;
		
		/**
		 * @return \Onphp\Identifier
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return \Onphp\Identifier
		**/
		public static function wrap($id)
		{
			return self::create()->setId($id);
		}
		
		public function getId()
		{
			return $this->proxy ? $this->proxy->getId() : $this->id;
		}
		
		/**
		 * @return \Onphp\Identifier
		**/
		public function setId($id)
		{
			if ($this->proxy) {
				$this->proxy->setId($id);
			}
			$this->id = $id;
			
			return $this;
		}

		/**
		 * @param \Onphp\Identifiable $object
		 * @return \Onphp\Identifier
		 */
		public function setObject(Identifiable $object)
		{
			if ($this->proxy) {
				$this->proxy->setObject($object);
			}

			$this->object = $object;
			return $this;
		}

		/**
		 * @return \Onphp\Identifiable
		 */
		public function getObject()
		{
			return $this->proxy ? $this->proxy->getObject() : $this->object;
		}
		
		public function isFinalized()
		{
			return $this->proxy ? $this->proxy->isFinalized() : $this->final;
		}

		/**
		 * @return \Onphp\Identifier
		 **/
		public function finalize()
		{
			if ($this->proxy) {
				$this->proxy->finalize();
			}

			$this->final = true;
			if ($this->object && $this->object->_getId() === $this) {
				$this->object->setId($this->getId());
			}

			return $this;
		}

		/**
		 * @param Identifier $id
		 * @return $this
		 */
		public function setProxy(Identifier $id)
		{
			Assert::isFalse($this->final, "must not be final to setProxy");
			$this->proxy = $id;
			if ($this->object) {
				$this->proxy->setObject($this->object);
			}
			if ($this->proxy->final) {
				$this->finalize();
			}
			return $this;
		}
	}
?>
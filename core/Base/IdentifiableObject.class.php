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
	 * Ideal Identifiable interface implementation. ;-)
	 * 
	 * @see Identifiable
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	namespace Onphp;

	class /* spirit of */ IdentifiableObject implements Identifiable, DialectString
	{
		protected $id = null;
		
		/**
		 * @return \Onphp\IdentifiableObject
		**/
		public static function wrap($id)
		{
			$io = new self;
			
			return $io->setId($id);
		}

		public function _getId()
		{
			if ($this->id === null) {
				$this->id = Identifier::create()->
					setObject($this);
			}
			return $this->id;
		}
		
		public function getId()
		{
			$id = $this->_getId();
			if ($id instanceof Identifier) {
				return $id->isFinalized() ? $id->getId() : null;
			}
			return $id;
		}
		
		/**
		 * @return \Onphp\IdentifiableObject
		**/
		public function setId($id)
		{
			if ($this->id instanceof Identifier) {
				if ($id instanceof Identifier) {
					$oldId = $this->id;
					$oldId->setProxy($id);
					$this->id = $id;
				} else {
					$this->id->setId($id);
				}
			} else {
				$this->id = $id;
			}
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return $dialect->quoteValue($this->getId());
		}
	}
?>
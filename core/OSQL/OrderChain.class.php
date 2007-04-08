<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	final class OrderChain implements DialectString, MappableObject
	{
		private $chain = array();
		
		/**
		 * @return OrderChain
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return OrderChain
		**/
		public function add(MappableObject $order)
		{
			$this->chain[] = $order;
			
			return $this;
		}
		
		/**
		 * @return OrderChain
		**/
		public function prepend(MappableObject $order)
		{
			if ($this->chain)
				array_unshift($this->chain, $order);
			else
				$this->chain[] = $order;
			
			return $this;
		}
		
		/**
		 * @return OrderBy
		**/
		public function getLast()
		{
			return end($this->chain);
		}
		
		public function getList()
		{
			return $this->chain;
		}
		
		public function getCount()
		{
			return count($this->chain);
		}
		
		/**
		 * @return OrderChain
		**/
		public function toMapped(StorableDAO $dao, JoinCapableQuery $query)
		{
			$chain = new self;
			
			foreach ($this->chain as $order)
				$chain->add($order->toMapped($dao, $query));
			
			return $chain;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			if (!$this->chain)
				return null;
			
			$out = null;
			
			foreach ($this->chain as $order)
				$out .= $order->toDialectString($dialect).', ';
			
			return rtrim($out, ', ');
		}
	}
?>
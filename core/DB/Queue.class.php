<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * OSQL's queries queue.
	 *
	 * @see OSQL
	 * 
	 * @ingroup DB
	 * 
	 * @todo introduce DBs without multi-query support handling
	**/
	final class Queue implements Query
	{
		private $queue = array();
		
		public static function create()
		{
			return new Queue();
		}

		public function getId()
		{
			return sha1(serialize($this->queue));
		}
		
		public function setId($id)
		{
			throw new UnsupportedMethodException();
		}

		public function getQueue()
		{
			return $this->queue;
		}

		public function add(Query $query)
		{
			$this->queue[] = $query;
			
			return $this;
		}
		
		public function remove(Query $query)
		{
			if (!$id = array_search($query, $this->queue))
				throw new ObjectNotFoundException();

			unset($this->queue[$id]);
			
			return $this;
		}
		
		public function drop()
		{
			$this->queue = array();
			
			return $this;
		}
		
		public function run(DB $db)
		{
			$db->queryRaw($this->toString($db->getDialect()));
			
			return $this;
		}
		
		public function flush(DB $db)
		{
			return $this->run($db)->drop();
		}
		
		public function toString(Dialect $dialect)
		{
			$out = array();

			foreach ($this->queue as &$query)
				$out[] = $query->toString($dialect);
			
			return implode(";\n", $out);
		}
	}
?>
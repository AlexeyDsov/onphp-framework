<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Transaction-wrapped queries queue.
	 *
	 * @see Queue
	 * 
	 * @ingroup DB
	**/
	final class TransactionQueue extends TransactionSkeleton implements Query
	{
		private $queue = null;
		
		public function __construct(DB $db)
		{
			parent::__construct($db);
			$this->queue = new Queue();
		}
		
		public function getId()
		{
			return sha1(serialize($this));
		}
		
		public function setId($id)
		{
			throw new UnsupportedMethodException();
		}
		
		public function setDB(DB $db)
		{
			$this->db = $db;
			
			return $this;
		}

		public function add(Query $query)
		{
			$this->queue->add($query);
			
			return $this;
		}
		
		public function flush()
		{
			try {
				$this->db->queryRaw($this->getBeginString());
				$this->queue->flush($this->db);
				$this->db->queryRaw("commit;\n");
			} catch (DatabaseException $e) {
				$this->db->queryRaw("rollback;\n");
				throw $e;
			}
			
			return $this;
		}
		
		// to satisfy Query interface
		public function toString(Dialect $dialect)
		{
			$out	= array();
			$list	= $this->queue->getQueueList();
			
			foreach ($list as &$query)
				$out[] = $query->toString($dialect);
			
			return implode("\n;", $out);
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2011 by Timofey A. Anisimov                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class PostgresCopyToQuery
	{
		private $tableName = null;
		private $db = null;
		private $delimiter = ',';

		public static function create(DB $db)
		{
			return new self($db);
		}

		public function __construct(DB $db)
		{
			$this->db = $db;
		}

		public function setTableName($tableName)
		{
			$this->tableName = $tableName;

			return $this;
		}

		public function setDelimeter($delimeter)
		{
			$this->delimiter = $delimeter;

			return $this;
		}

		public function run()
		{
			return
				pg_copy_to(
					$this->db->getLink(),
					$this->tableName,
					$this->delimiter
				);
		}
	}
?>

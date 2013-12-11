<?php
/***************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * Factory for OSQL's queries.
	 * 
	 * @ingroup OSQL
	 * 
	 * @see http://onphp.org/examples.OSQL.en.html
	**/
	namespace Onphp;

	final class OSQL extends StaticFactory
	{
		/**
		 * @return \Onphp\SelectQuery
		**/
		public static function select()
		{
			return new SelectQuery();
		}
		
		/**
		 * @return \Onphp\InsertQuery
		**/
		public static function insert()
		{
			return new InsertQuery();
		}

		/**
		 * @return BulkInsertQuery
		 **/
		public static function bulkInsert()
		{
			return new BulkInsertQuery();
		}
		
		/**
		 * @return \Onphp\UpdateQuery
		**/
		public static function update($table = null)
		{
			return new UpdateQuery($table);
		}
		
		/**
		 * @return \Onphp\DeleteQuery
		**/
		public static function delete()
		{
			return new DeleteQuery();
		}
		
		/**
		 * @return \Onphp\TruncateQuery
		**/
		public static function truncate($whom = null)
		{
			return new TruncateQuery($whom);
		}
		
		/**
		 * @return \Onphp\CreateTableQuery
		**/
		public static function createTable(DBTable $table)
		{
			return new CreateTableQuery($table);
		}
		
		/**
		 * @return \Onphp\DropTableQuery
		**/
		public static function dropTable($name, $cascade = false)
		{
			return new DropTableQuery($name, $cascade);
		}
	}
?>
<?php
	class BulkInsertQueryTest extends PHPUnit_Framework_TestCase
	{
		public function testBulkInsertQueryWithPostgresDialect()
		{
			$query = new BulkInsertQuery();
			$query->into('test')->setRows(array(
				array(
					'id' => 1,
					'name' => 'test1'
				),
				array(
					'id' => 2,
					'name' => 'test2'
				)
			));

			$this->assertEquals(
				'INSERT INTO "test" ("id", "name") VALUES (\'1\', \'test1\'), (\'2\', \'test2\')',
				$query->toDialectString(PostgresDialect::me())
			);
		}
	}
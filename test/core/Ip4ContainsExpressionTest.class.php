<?php

	namespace Onphp\Test;
	
	final class Ip4ContainsExpressionTest extends TestCase
	{
		public function testToDialect()
		{
			$expression =
				\Onphp\Expression::containsIp(
					\Onphp\IpRange::create('127.0.0.1-127.0.0.5'),
					\Onphp\IpAddress::create('127.0.0.3')
				);
			
			$this->assertEquals(
				"'127.0.0.3' <<= '127.0.0.1-127.0.0.5'::ip4r",
				$expression->toDialectString(DBTestPool::me()->getDb('\Onphp\PgSQL')->getDialect())
			);
			
			$expression =
				\Onphp\Expression::containsIp(
					\Onphp\DBField::create('range'),
					'192.168.1.1'
				);
			$this->assertEquals(
				'\'192.168.1.1\' <<= "range"',
				$expression->toDialectString(DBTestPool::me()->getDb('\Onphp\PgSQL')->getDialect())
			);
			
		}
		
		public function testWithObjects()
		{
			$criteria =
				\Onphp\Criteria::create(TestUser::dao())->
				add(
					\Onphp\Expression::containsIp(
						\Onphp\IpRange::create('192.168.1.1-192.168.1.255'), 'ip')
				)->
				addProjection(\Onphp\Projection::property('id'));
			
			$this->assertEquals(
				$criteria->toDialectString(DBTestPool::me()->getDb('\Onphp\PgSQL')->getDialect()),
				'SELECT "test_user"."id" FROM "test_user" WHERE "test_user"."ip" <<= \'192.168.1.1-192.168.1.255\'::ip4r'
			);
			
			$criteria =
				\Onphp\Criteria::create(TestInternetProvider::dao())->
				add(
					\Onphp\Expression::containsIp(
						'range',
						\Onphp\IpAddress::create('42.42.42.42')
					)
				)->addProjection(\Onphp\Projection::property('id'));
			
			$this->assertEquals(
				$criteria->toDialectString(DBTestPool::me()->getDb('\Onphp\PgSQL')->getDialect()),
				'SELECT "test_internet_provider"."id" FROM "test_internet_provider" WHERE \'42.42.42.42\' <<= "test_internet_provider"."range"'
						
			);
		}
	}
?>
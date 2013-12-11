<?php
/***************************************************************************
 *   Copyright (C) 2011 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Test;
	use \Onphp\PeclMemcached;
	use \Onphp\CascadeCache;
	
	final class CascadeCacheTest extends TestCase
	{
		private $localPeer;
		private $remotePeer;
		private $peer;
		
		public function __construct()
		{
			$this->localPeer = PeclMemcached::create('localhost', 11211);
			$this->remotePeer = PeclMemcached::create('localhost', 11212);
			$this->peer = CascadeCache::create($this->localPeer, $this->remotePeer);
		}
		
		protected function setUp()
		{
			if (!$this->peer->isAlive())
				return $this->markTestSkipped('Local or remote peer is not alive');
			
			$this->peer->clean();
		}
		
		public function testGet()
		{
			$this->remotePeer->add('value', 42);
			
			$this->assertEquals($this->peer->get('value'), 42);
			$this->assertEquals($this->localPeer->get('value'), 42);
			
			$this->assertFalse($this->peer->get('neg_value')); // PeclMemcached
			$this->assertEquals(
				$this->localPeer->get('neg_value'),
				CascadeCache::LOCAL_NULL_VALUE
			);
			$this->remotePeer->add('neg_value', 'stuff');
			$this->assertNull($this->peer->get('neg_value')); // cached
		}
		
		public function testStore()
		{
			$this->assertTrue($this->peer->add('value', 42));
			$this->assertEquals($this->remotePeer->get('value'), 42);
			$this->assertEquals($this->localPeer->get('value'), 42);
			
			$this->assertTrue($this->peer->replace('value', 11));
			$this->assertEquals($this->remotePeer->get('value'), 11);
			$this->assertEquals($this->localPeer->get('value'), 11);
			
			$this->assertTrue($this->peer->set('value', 100));
			$this->assertEquals($this->remotePeer->get('value'), 100);
			$this->assertEquals($this->localPeer->get('value'), 100);
			
			$this->assertFalse($this->peer->replace('not_exist_value', 42));
			$this->assertEquals(
				$this->localPeer->get('not_exist_value'),
				CascadeCache::LOCAL_NULL_VALUE
			);
			$this->assertFalse($this->peer->add('value', 44));
			$this->assertEquals($this->localPeer->get('value'), 100);
		}
		
		public function testIncrementDecrement()
		{
			$this->remotePeer->add('value', 42);
			
			$this->assertEquals($this->peer->increment('value', 8), 50);
			$this->assertEquals($this->remotePeer->get('value'), 50);
			$this->assertEquals($this->localPeer->get('value'), 50);
			
			$this->assertEquals($this->peer->decrement('value', 2), 48);
			$this->assertEquals($this->remotePeer->get('value'), 48);
			$this->assertEquals($this->localPeer->get('value'), 48);
		}
		
		public function testDelete()
		{
			$this->remotePeer->add('value', 42);
			
			$this->assertTrue($this->peer->delete('value'));
			$this->assertEquals(
				$this->localPeer->get('value'),
				CascadeCache::LOCAL_NULL_VALUE
			);
		}
		
		/**
		 * @depends testStore
		**/
		public function testGetList()
		{
			$indexes = array('value1', 'value2');
			
			$this->assertTrue(
				\Onphp\ArrayUtils::isEmpty($this->peer->getList($indexes))
			);
			foreach ($indexes as $key)
				$this->assertEquals(
					$this->localPeer->get($key),
					CascadeCache::LOCAL_NULL_VALUE
				);
			
			$this->remotePeer->set('value1', 'one');
			$this->peer->set('value2', 'two');
			
			$valueList = $this->peer->getList($indexes);
			$this->assertArrayNotHasKey('value1', $valueList);
			$this->assertArrayHasKey('value2', $valueList);
			$this->assertEquals($valueList['value2'], 'two');
		}
		
		/**
		 * @depends testStore
		**/
		public function testLocalTtl()
		{
			$this->peer->setLocalTtlMap(
				array(
					'ttl_mark_2sec'	=> 2,
					'ttl_mark_1sec'	=> 1
				)
			);
			$this->peer->mark('ttl_mark_1sec')->set('value1', 'some data');
			$this->peer->mark('ttl_mark_2sec')->set('value2', 'something');
			$this->assertEquals($this->localPeer->get('value1'), 'some data');
			$this->assertEquals($this->localPeer->get('value2'), 'something');
			sleep(1);
			$this->assertFalse($this->localPeer->get('value1')); // PeclMemcached
			$this->assertEquals($this->localPeer->get('value2'), 'something');
		}
		
		/**
		 * NOTE: valid only for PeclMemcached (not Memcached)
		 * @depends testStore
		**/
		public function testAppend()
		{
			$this->peer->set('value', 'stuff_');
			$this->assertTrue($this->peer->append('value', 'append'));
			$this->assertFalse($this->localPeer->get('value'));
			$this->assertEquals($this->peer->get('value'), 'stuff_append');
		}
	}
?>
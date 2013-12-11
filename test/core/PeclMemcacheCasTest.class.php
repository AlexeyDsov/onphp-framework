<?php

	namespace Onphp\Test;
	
	final class PeclMemcacheCasTest extends TestCase
	{
		public function testPositive()
		{
			$cache = new \Onphp\PeclMemcached();
			
			$cas = null;
			$cache->set('cas_test_1', 42);
			$cache->getc('cas_test_1', $cas);
			
			$this->assertTrue($cache->cas('cas_test_1', '43', \Onphp\Cache::EXPIRES_MEDIUM, $cas));
		}
		
		public function testNegative()
		{
			$cache = new \Onphp\PeclMemcached();
			
			$cas = null;
			$cache->set('cas_test_2', 42);
			$cache->getc('cas_test_2', $cas);
			
			$anotherCache = new \Onphp\PeclMemcached();
			
			$anotherCache->set('cas_test_2', '666');
			
			$this->assertFalse($cache->cas('cas_test_1', '43', \Onphp\Cache::EXPIRES_MEDIUM, $cas));
		}
	}
?>
<?php
	namespace Onphp\Test;
	
	final class DBMock extends \Onphp\DB
	{
		private $connected = false;
		private $failConnect = false;
		
		public static function create()
		{
			return new self;
		}
		
		public function spawnDialect()
		{
			return \Onphp\ImaginaryDialect::me();
		}
		
		public function __destruct()
		{
			
		}
		
		public function setFailConnect($isConnectFail)
		{
			$this->failConnect = ($isConnectFail === true);
			
			return $this;
		}
		
		public function setConnected($isConnected)
		{
			$this->connected = ($isConnected === true);
			
			return $this;
		}
		
		public function isConnected()
		{
			return $this->connected;
		}
		
		public function getHostName()
		{
			return $this->hostname;
		}
		
		public function connect()
		{
			if ($this->failConnect)
					{
						throw new \Onphp\DatabaseException('Connection failed');
					}
			
			$this->connected = true;
			
			return $this;
		}

		public function disconnect()
		{
			return $this;
		}

		public function getTableInfo($table)
		{
			return null;
		}

		public function queryRaw($queryString)
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}

		public function queryRow(\Onphp\Query $query)
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}

		public function querySet(\Onphp\Query $query)
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}

		public function queryColumn(\Onphp\Query $query)
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}

		public function queryCount(\Onphp\Query $query)
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}

		public function setDbEncoding()
		{
			throw new \Onphp\DatabaseException($this->hostname);
		}
		
		public function obtainSequence($sequence)
		{
			return 1;
		}
	}

	final class FakeDAO extends \Onphp\StorableDAO
	{
		protected $useSlave = true;
		
		public function getLinkName()
		{
			return 'main';
		}
		
		public function getTable()
		{
			return 'test_table';
		}

		public function getObjectName()
		{
			return '\Onphp\Test\FakeObject';
		}
	}

	final class FakeObject extends \Onphp\IdentifiableObject implements \Onphp\Prototyped
	{
		public static function proto()
		{
			return \Onphp\Singleton::getInstance('\Onphp\Test\ProtoFakeObject');
		}
		
		public function getId()
		{
			return 1;
		}
	}

	final class ProtoFakeObject extends \Onphp\AbstractProtoClass
	{
		protected function makePropertyList()
		{
			return array();
		}
	}

	final class FakeWorker extends \Onphp\BaseDaoWorker
	{
		public function getById($id)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getByLogic(\Onphp\LogicalObject $logic)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getByQuery(\Onphp\SelectQuery $query)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getCustom(\Onphp\SelectQuery $query)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getListByIds(array $ids)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getListByQuery(\Onphp\SelectQuery $query)
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getListByLogic(\Onphp\LogicalObject $logic)
		{
			return $this->fetchList($this->getSimpleQuery());
		}

		public function getPlainList()
		{
			return $this->fetchObject($this->getSimpleQuery());
		}

		public function getCustomList(\Onphp\SelectQuery $query)
		{
			return $this->fetchList($this->getSimpleQuery());
		}

		public function getCustomRowList(\Onphp\SelectQuery $query)
		{
			return $this->fetchList($this->getSimpleQuery());
		}

		public function getQueryResult(\Onphp\SelectQuery $query)
		{
			return $this->fetchList($this->getSimpleQuery());
		}

		public function uncacheByIds($ids)
		{
			
		}

		public function uncacheLists()
		{
			
		}
		
		private function getSimpleQuery()
		{
			return \Onphp\OSQL::select()->from('test');
		}
	}

	final class SlavePoolTest extends TestCase
	{
		public function testSlavePoolDropLink()
		{			
			$slavePool =
				\Onphp\SlaveDBList::create()->
					addLink('test1', $this->getSlaveLink('test1'), 10)->
					addLink('test2', $this->getSlaveLink('test2'), 1);
			
			$slavePool->dropLink('test1');
			
			$link = $slavePool->getSlaveLink();
			
			$this->assertNotNull($link);
			$this->assertEquals('test2', $link->getHostName());
			
			$slavePool->dropLink('test2');
			
			$this->assertNull($slavePool->getSlaveLink());
			
			$slavePool =
				\Onphp\SlaveDBList::create()->
					addLink(
						'test', 
						$this->getSlaveLink('test')->setFailConnect(true),
						10
					);
			
			$this->assertNull($slavePool->getSlaveLink());
		}
		
		public function testSlavePoolAdd()
		{
			$slavePool = 
				\Onphp\SlaveDBList::create()->
				addLink('test', $this->getSlaveLink('test'), 10);
			
			try {
				$slavePool->addLink(
					'test',
					$this->getSlaveLink('test')
				);
				
				$this->fail('Dublicate slave link in slave pool');
			} catch (\Onphp\WrongArgumentException $e) {/* ok */}
			
			$dbPool = $this->makeDBPool();
			
			$dbPool->addSlaveList('main', $slavePool);
			
			try {
				$dbPool->addSlaveList('main', $slavePool);
				
				$this->fail('Dublicate slave pool');
			} catch (\Onphp\WrongStateException $e) {/* ok */}
			
			try {
				$dbPool->addSlaveList('no_such_link', $slavePool);
				
				$this->fail('Added slave pool for missing link');
			} catch (\Onphp\MissingElementException $e) {/* ok */}			
		}
		
		public function testSlaveChoose()
		{
			$pool = $this->makeDBPool();
			
			$pool->addSlaveList(
				'main', 
				\Onphp\SlaveDBList::create()->
					addLink(
						'slave1',
						$this->getSlaveLink('slave1'),
						1
					)
			);
			
			$this->assertEquals(
				'mainHost',
				$pool->getLink('main')->getHostName()
			);
			
			$this->assertEquals(
				'slave1',
				$pool->getLink('main', true)->getHostName()
			);
		}
		
		public function testSlaveForWorker()
		{
			$pool = $this->makeDBPool();
			
			$pool->
				addSlaveList(
					'main',
					\Onphp\SlaveDBList::create()->
						addLink('slave1', $this->getSlaveLink('slave1'))
				);
			
			try {
				\Onphp\Singleton::dropInstance('FakeDAO');
			} catch (\Onphp\MissingElementException $e) {}

			$dao = \Onphp\Singleton::getInstance('\Onphp\Test\FakeDAO');
			$worker = new FakeWorker($dao);
			
			try {
				$worker->getById(1);
				
				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('slave1', $e->getMessage());
			}
			
			$dao->setUseSlave(false);

			try {
				$worker->getById(1);

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}
			
			$dao->setUseSlave(true);

			try {
				$worker->getPlainList();

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('slave1', $e->getMessage());
			}

			$dao->setUseSlave(false);

			try {
				$worker->getPlainList();

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}
			
			$dao->setUseSlave(true);
			
			try {
				$worker->dropById(1);

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}

			$dao->setUseSlave(false);

			try {
				$worker->dropById(1);

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}

			$dao->setUseSlave(true);

			try {
				$worker->dropByIds(array(1, 2));

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}

			$dao->setUseSlave(false);

			try {
				$worker->dropByIds(array(1, 2));

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}
			
			$dao->setUseSlave(true);
			
			try {
				$dao->add(new FakeObject());

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}
			
			$dao->setUseSlave(false);

			try {
				$dao->add(new FakeObject());

				$this->fail('No exception is strange');
			} catch (\Onphp\DatabaseException $e) {
				$this->assertEquals('mainHost', $e->getMessage());
			}
		}
		
		private function makeDBPool()
		{
			\Onphp\DBPool::dropInstance('\Onphp\DBPool');
			
			return
				\Onphp\DBPool::me()->
					addLink('main', DBMock::create()->setHostname('mainHost'));
		}

		/**
		 * @param $hostname
		 * @return DBMock
		 */
		private function getSlaveLink($hostname)
		{
			return
				DBMock::create()->
					setHostname($hostname);
		}
	}
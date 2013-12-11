<?php
	/* $Id$ */
	
	namespace Onphp\Test;
	
	class DAOTest extends TestTables
	{
		public function create()
		{
			/**
			 * @see testRecursionObjects() and meta
			 * for TestParentObject and TestChildObject
			**/
			$this->schema->
				getTableByName('test_parent_object')->
				getColumnByName('root_id')->
				dropReference();
			
			return parent::create();
		}
		
		public function testSchema()
		{
			return $this->create()->drop();
		}
		
		public function testData()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$this->fill();
				
				$this->getSome(); // 41!
				\Onphp\Cache::me()->clean();
				$this->getSome();
				
				$this->nonIntegerIdentifier();
				
				$this->racySave();
				$this->binaryTest();
				$this->lazyTest();
			}
			
			$this->drop();
		}
		
		public function testBoolean()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				
				//creating moscow
				$moscow = TestCity::create()->setName('Moscow');
				$moscow = $moscow->dao()->add($moscow);
				$moscowId = $moscow->getId();
				/* @var $moscow TestCity */
				
				//now moscow capital
				$moscow->dao()->merge($moscow->setCapital(true));
				TestCity::dao()->dropIdentityMap();
				
				\Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::isTrue('capital'))->
					get();
				TestCity::dao()->dropIdentityMap();
				
				$moscow = \Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::isNull('large'))->
					get();
				TestCity::dao()->dropIdentityMap();
				
				//now moscow large
				$moscow = $moscow->dao()->merge($moscow->setLarge(true));
				
				TestCity::dao()->dropIdentityMap();
				$moscow = TestCity::dao()->getById($moscowId);
				$this->assertTrue($moscow->getCapital());
				$this->assertTrue($moscow->getLarge());
				
				\Onphp\Criteria::create(TestCity::dao())->
					setSilent(false)->
					add(\Onphp\Expression::not(\Onphp\Expression::isFalse('large')))->
					get();
				TestCity::dao()->dropIdentityMap();
			}
			
			$this->drop();
		}
		
		public function testCriteria()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$this->fill();
				
				$this->criteriaResult();
				
				\Onphp\Cache::me()->clean();
			}
			
			$this->deletedCount();
			
			$this->drop();
		}
		
		public function testUnified()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$this->fill();
				
				$this->unified();
				
				\Onphp\Cache::me()->clean();
			}
			
			$this->deletedCount();
			
			$this->drop();
		}
		
		public function testCount()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				
				$this->fill();
				
				$count = TestUser::dao()->getTotalCount();
				
				$this->assertGreaterThan(1, $count);
				
				$city =
					TestCity::create()->
					setId(1);
				
				$newUser =
					TestUser::create()->
					setCity($city)->
					setCredentials(
						Credentials::create()->
						setNickname('newuser')->
						setPassword(sha1('newuser'))
					)->
					setLastLogin(
						\Onphp\Timestamp::create(time())
					)->
					setRegistered(
						\Onphp\Timestamp::create(time())
					);
				
				TestUser::dao()->add($newUser);
				
				$newCount = TestUser::dao()->getTotalCount();
				
				$this->assertEquals($count + 1, $newCount);
			}
			
			$this->drop();
		}
		
		public function testGetByEmptyId()
		{
			$this->create();
			
			$this->getByEmptyIdTest(0);
			$this->getByEmptyIdTest(null);
			$this->getByEmptyIdTest('');
			$this->getByEmptyIdTest('0');
			$this->getByEmptyIdTest(false);
			
			$empty = TestLazy::create();
			
			$this->assertNull($empty->getCity());
			$this->assertNull($empty->getCityOptional());
			$this->assertNull($empty->getEnum());
			
			$this->drop();
		}
		
		public function deletedCount()
		{
			TestUser::dao()->dropById(1);
			
			try {
				TestUser::dao()->dropByIds(array(1, 2));
				$this->fail();
			} catch (WrongStateException $e) {
				// ok
			}
		}
		
		public function fill($assertions = true)
		{
			$moscow =
				TestCity::create()->
				setName('Moscow');
			
			$piter =
				TestCity::create()->
				setName('Saint-Peterburg');
			
			$mysqler =
				TestUser::create()->
				setCity($moscow)->
				setCredentials(
					Credentials::create()->
					setNickname('mysqler')->
					setPassword(sha1('mysqler'))
				)->
				setLastLogin(
					Timestamp::create(time())
				)->
				setRegistered(
					Timestamp::create(time())->modify('-1 day')
				);
			
			$postgreser = clone $mysqler;
			
			$postgreser->
				setCredentials(
					Credentials::create()->
					setNickName('postgreser')->
					setPassword(sha1('postgreser'))
				)->
				setCity($piter)->
				setUrl(\Onphp\HttpUrl::create()->parse('http://postgresql.org/'));
			
			$piter = TestCity::dao()->add($piter);
			$moscow = TestCity::dao()->add($moscow);
			
			if ($assertions) {
				$this->assertEquals($piter->getId(), 1);
				$this->assertEquals($moscow->getId(), 2);
			}
			
			$postgreser = TestUser::dao()->add($postgreser);
			
			for ($i = 0; $i < 10; $i++) {
				$encapsulant = TestEncapsulant::dao()->add(
					TestEncapsulant::create()->
					setName($i)
				);
				
				$encapsulant->getCities()->
					fetch()->
					setList(
						array($piter, $moscow)
					)->
					save();
			}
			
			$mysqler = TestUser::dao()->add($mysqler);
			
			if ($assertions) {
				$this->assertEquals($postgreser->getId(), 1);
				$this->assertEquals($mysqler->getId(), 2);
			}
			
			if ($assertions) {
				// put them in cache now
				TestUser::dao()->dropIdentityMap();
				
				TestUser::dao()->getById(1);
				TestUser::dao()->getById(2);
				
				$this->getListByIdsTest();
				
				Cache::me()->clean();
				
				$this->assertTrue(
					($postgreser == TestUser::dao()->getById(1))
				);
				
				$this->assertTrue(
					($mysqler == TestUser::dao()->getById(2))
				);
			}
			
			$firstClone = clone $postgreser;
			$secondClone = clone $mysqler;
			
			$firstCount = TestUser::dao()->dropById($postgreser->getId());
			$secondCount = TestUser::dao()->dropByIds(array($mysqler->getId()));
			
			if ($assertions) {
				$this->assertEquals($firstCount, 1);
				$this->assertEquals($secondCount, 1);
				
				try {
					TestUser::dao()->getById(1);
					$this->fail();
				} catch (ObjectNotFoundException $e) {
					/* pass */
				}
				
				$result =
					\Onphp\Criteria::create(TestUser::dao())->
					add(Expression::eq(1, 2))->
					getResult();
				
				$this->assertEquals($result->getCount(), 0);
				$this->assertEquals($result->getList(), array());
			}
			
			TestUser::dao()->import($firstClone);
			TestUser::dao()->import($secondClone);
			
			if ($assertions) {
				// cache multi-get
				$this->getListByIdsTest();
				$this->getListByIdsTest();
			}
		}
		
		public function criteriaResult()
		{
			$queryResult = \Onphp\Criteria::create(TestCity::dao())->getResult();
			$this->assertEquals(2, $queryResult->getCount());
		}
		
		public function unified()
		{
			$user = TestUser::dao()->getById(1);
			
			$encapsulant = TestEncapsulant::dao()->getPlainList();
			
			$collectionDao = $user->getEncapsulants();
			
			$collectionDao->fetch()->setList($encapsulant);
			
			$collectionDao->save();
			
			unset($collectionDao);
			
			// fetch
			$encapsulantsList = $user->getEncapsulants()->getList();
			
			$piter = TestCity::dao()->getById(1);
			$moscow = TestCity::dao()->getById(2);
			
			for ($i = 0; $i < 10; $i++) {
				$this->assertEquals($encapsulantsList[$i]->getId(), $i + 1);
				$this->assertEquals($encapsulantsList[$i]->getName(), $i);
				
				$cityList = $encapsulantsList[$i]->getCities()->getList();
				
				$this->assertEquals($cityList[0], $piter);
				$this->assertEquals($cityList[1], $moscow);
			}
			
			unset($encapsulantsList);
			
			// lazy fetch
			$encapsulantsList = $user->getEncapsulants(true)->getList();
			
			for ($i = 1; $i < 11; $i++)
				$this->assertEquals($encapsulantsList[$i], $i);
			
			// count
			$user->getEncapsulants()->clean();
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 10);
			
			$criteria = Criteria::create(TestEncapsulant::dao())->
				add(
					Expression::in(
						'cities.id',
						array($piter->getId(), $moscow->getId())
					)
				);
			
			$user->getEncapsulants()->setCriteria($criteria);
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 20);
			
			// distinct count
			$user->getEncapsulants()->clean();
			
			$user->getEncapsulants()->setCriteria(
				$criteria->
					setDistinct(true)
			);
			
			if (DBPool::me()->getLink() instanceof SQLite)
				// TODO: sqlite does not support such queries yet
				return null;
			
			$this->assertEquals($user->getEncapsulants()->getCount(), 10);
		}
		
		public function testWorkingWithCache()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				DBPool::me()->setDefault($db);
				
				$item =
					TestItem::create()->
					setName('testItem1');
				
				TestItem::dao()->add($item);
				
				$encapsulant =
					TestEncapsulant::create()->
					setName('testEncapsulant1');
				
				TestEncapsulant::dao()->add($encapsulant);
				
				$subItem1 =
					TestSubItem::create()->
					setName('testSubItem1')->
					setEncapsulant($encapsulant)->
					setItem($item);
				
				$subItem2 =
					TestSubItem::create()->
					setName('testSubItem2')->
					setEncapsulant($encapsulant)->
					setItem($item);
				
				TestSubItem::dao()->add($subItem1);
				TestSubItem::dao()->add($subItem2);
				
				$items =
					\Onphp\Criteria::create(TestItem::dao())->
					getList();
				
				foreach ($items as $item) {
					foreach ($item->getSubItems()->getList() as $subItem) {
						$this->assertEquals(
							$subItem->getEncapsulant()->getName(),
							'testEncapsulant1'
						);
					}
				}
				
				$encapsulant = TestEncapsulant::dao()->getById(1);
				
				$encapsulant->setName('testEncapsulant1_changed');
				
				TestEncapsulant::dao()->save($encapsulant);
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$items =
					Criteria::create(TestItem::dao())->
					getList();
				
				foreach ($items as $item) {
					foreach ($item->getSubItems()->getList() as $subItem) {
						$this->assertEquals(
							$subItem->getEncapsulant()->getName(),
							'testEncapsulant1_changed'
						);
					}
				}
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$subItem = TestSubItem::dao()->getById(1);
				
				$this->assertEquals(
					$subItem->getEncapsulant()->getName(),
					'testEncapsulant1_changed'
				);
				
				// drop identityMap
				TestEncapsulant::dao()->dropIdentityMap();
				TestSubItem::dao()->dropIdentityMap();
				TestItem::dao()->dropIdentityMap();
				
				$subItems =
					Criteria::create(TestSubItem::dao())->
					getList();
				
				foreach ($subItems as $subItem) {
					$this->assertEquals(
						$subItem->getEncapsulant()->getName(),
						'testEncapsulant1_changed'
					);
				}
			}
			
			$this->drop();
		}
		
		/**
		 * Install hstore
		 * /usr/share/postgresql/contrib # cat hstore.sql | psql -U pgsql -d onphp
		**/
		public function testHstore()
		{
			$this->create();
			
			foreach (DBTestPool::me()->getPool() as $connector => $db) {
				\Onphp\DBPool::me()->setDefault($db);
				$properties = array(
					'age' => '23',
					'weight' => 80,
					'comment' => null,
				);

				$user =
					TestUser::create()->
					setCity(
						$moscow = TestCity::create()->
						setName('Moscow')
					)->
					setCredentials(
						Credentials::create()->
						setNickname('fake')->
						setPassword(sha1('passwd'))
					)->
					setLastLogin(
						Timestamp::create(time())
					)->
					setRegistered(
						Timestamp::create(time())->modify('-1 day')
					)->
					setProperties(Hstore::make($properties));

				$moscow = TestCity::dao()->add($moscow);

				$user = TestUser::dao()->add($user);

				\Onphp\Cache::me()->clean();
				TestUser::dao()->dropIdentityMap();

				$user = TestUser::dao()->getById('1');

				$this->assertInstanceOf('Hstore', $user->getProperties());

				$this->assertEquals(
					$properties,
					$user->getProperties()->getList()
				);

				$form = TestUser::proto()->makeForm();

				$form->get('properties')->
					setFormMapping(
						array(
							Primitive::string('age'),
							Primitive::integer('weight'),
							Primitive::string('comment'),
						)
					);

				$form->import(
					array('id' => $user->getId())
				);

				$this->assertNotNull($form->getValue('id'));

				$object = $user;

				\Onphp\FormUtils::object2form($object, $form);

				$this->assertInstanceOf('Hstore', $form->getValue('properties'));

				$this->assertEquals(
					array_filter($properties),
					$form->getValue('properties')->getList()
				);

				$subform = $form->get('properties')->getInnerForm();

				$this->assertEquals(
					$subform->getValue('age'),
					'23'
				);

				$this->assertEquals(
					$subform->getValue('weight'),
					80
				);

				$this->assertNull(
					$subform->getValue('comment')
				);

				$user = new TestUser();

				\Onphp\FormUtils::form2object($form, $user, false);

				$this->assertEquals(
					$user->getProperties()->getList(),
					array_filter($properties)
				);
			}
			
			$this->drop();
		}
		
		/**
		 * @see http://lists.shadanakar.org/onphp-dev-ru/0811/0774.html
		**/
		public function testRecursiveContainers()
		{
			$this->markTestSkipped('wontfix');
			
			$this->create();
			
			TestObject::dao()->import(
				TestObject::create()->
				setId(1)->
				setName('test object')
			);
			
			TestType::dao()->import(
				TestType::create()->
				setId(1)->
				setName('test type')
			);
			
			$type = TestType::dao()->getById(1);
			
			$type->getObjects()->fetch()->setList(
				array(TestObject::dao()->getById(1))
			)->
			save();
			
			$object = TestObject::dao()->getById(1);
			
			TestObject::dao()->save($object->setName('test object modified'));
			
			$list = $type->getObjects()->getList();
			
			$modifiedObject = TestObject::dao()->getById(1);
			
			$this->assertEquals($list[0], $modifiedObject);
			
			$this->drop();
		}
		
		public function testRecursionObjects()
		{
			$this->create();

			$parentProperties =
				\Onphp\Singleton::getInstance('ProtoTestParentObject')->
				getPropertyList();

			$resultRoot = $parentProperties['root']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$childProperties =
				Singleton::getInstance('ProtoTestChildObject')->
				getPropertyList();

			$resultParent = $childProperties['parent']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$selfRecursiveProperties =
				Singleton::getInstance('ProtoTestSelfRecursion')->
				getPropertyList();

			$resultSelfRecursive = $selfRecursiveProperties['parent']->
				getFetchStrategyId() == FetchStrategy::LAZY;

			$this->drop();

			$this->assertTrue($resultRoot);
			$this->assertTrue($resultParent);
			$this->assertTrue($resultSelfRecursive);
		}

		public function testStringIdentifier()
		{
			$identifier =
				TestStringIdentifier::proto()->getPropertyByName('id');

			$this->assertEquals($identifier->getType(), 'scalarIdentifier');

			$identifier =
				TestStringIdentifierRelated::proto()->getPropertyByName('test');

			$this->assertEquals($identifier->getType(), 'scalarIdentifier');
		}

		public function nonIntegerIdentifier()
		{
			$id = 'non-integer-one';
			$binaryData = "\0!bbq!\0";
			
			$bin =
				TestBinaryStuff::create()->
				setId($id)->
				setData($binaryData);
			
			try {
				TestBinaryStuff::dao()->import($bin);
			} catch (DatabaseException $e) {
				return $this->fail($e->getMessage());
			}
			
			TestBinaryStuff::dao()->dropIdentityMap();
			\Onphp\Cache::me()->clean();
			
			$prm = \Onphp\Primitive::prototypedIdentifier('TestBinaryStuff', 'id');
			
			$this->assertTrue($prm->import(array('id' => $id)));
			$this->assertSame($prm->getValue()->getId(), $id);
			
			$binLoaded = TestBinaryStuff::dao()->getById($id);
			$this->assertEquals($binLoaded, $bin);
			$this->assertEquals($binLoaded->getData(), $binaryData);
			$this->assertEquals(TestBinaryStuff::dao()->dropById($id), 1);
			
			$integerIdPrimitive = \Onphp\rimitive::prototypedIdentifier('TestUser');
			try {
				$integerIdPrimitive->import(array('id' => 'string-instead-of-integer'));
			} catch (DatabaseException $e) {
				return $this->fail($e->getMessage());
			}
		}
		
		public function testIpAddressProperty()
		{
			$this->create();
			
			$city =
				TestCity::create()->
				setName('Khimki');
			
			TestCity::dao()->add($city);
			
			$userWithIp =
				TestUser::create()->
					setCredentials(
						Credentials::create()->
						setNickName('postgreser')->
						setPassword(sha1('postgreser'))
					)->
					setLastLogin(Timestamp::makeNow())->
					setRegistered(Timestamp::makeNow())->
					setCity($city)->
					setIp(IpAddress::create('127.0.0.1'));
			
			TestUser::dao()->add($userWithIp);
			
			$this->assertTrue($userWithIp->getId() >= 1);
			
			$this->assertTrue($userWithIp->getIp() instanceof IpAddress);
			
			$plainIp =
				\Onphp\DBPool::me()->getByDao(TestUser::dao())->
				queryColumn(
					OSQL::select()->get('ip')->
					from(TestUser::dao()->getTable())->
					where(Expression::eq('id', $userWithIp->getId()))
				);
			
			$this->assertEquals($plainIp[0], $userWithIp->getIp()->toString());
			
			$count =
				\Onphp\Criteria::create(TestUser::dao())->
				add(\Onphp\Expression::eq('ip', \Onphp\IpAddress::create('127.0.0.1')))->
				addProjection(\Onphp\Projection::count('*', 'count'))->
				getCustom('count');
			
			$this->assertEquals($count, 1);
			
			$this->drop();
		}
		
		public function testIpRangeProperty()
		{
			$this->create();
			
			$akado =
				TestInternetProvider::create()->
				setName('Akada')->
				setRange(
					IpRange::create(
						IpAddress::create('192.168.1.1'),
						IpAddress::create('192.168.1.42')
					)
				);
			
			TestInternetProvider::dao()->
				add($akado);
			
			$plainRange =
					\Onphp\Criteria::create(TestInternetProvider::dao())->
					addProjection(\Onphp\Projection::property('range'))->
					add(Expression::eq('name', 'Akada'))->
					getCustom();
			
			$this->assertEquals(
				$plainRange['range'],
				'192.168.1.1-192.168.1.42'
			);
			
			TestInternetProvider::dao()->
			add(
				TestInternetProvider::create()->
				setName('DomRu')->
				setRange(
					\Onphp\IpRange::create('192.168.2.0/24')
				)
			);
			
			$list =
				\Onphp\Criteria::create(TestInternetProvider::dao())->
				addOrder('id')->
				getList();
			
			$this->assertEquals(count($list), 2);
			
			$this->drop();
		}
		
		public function testLazy()
		{
			$this->create();
			
			$parent = TestParentObject::create();
			$child = TestChildObject::create()->setParent($parent);
			
			$parent->dao()->add($parent);
			
			$child->dao()->add($child);
			
			$this->assertEquals(
				$parent->getId(),
				\Onphp\Criteria::create(TestChildObject::dao())->
					setProjection(
						\Onphp\Projection::property('parent.id', 'parentId')
					)->
					add(Expression::eq('id', $child->getId()))->
					getCustom('parentId')
			);
			
			$this->drop();
		}
		
		public function testPrimitiveLists()
		{
			$this->create();
			
			$objectList = array();
			
			for ($i = 16; $i <= 18; $i ++)
				$objectList[] =
					TestCity::dao()->add(
						TestCity::create()->
						setName('Arzamas-'.$i)
					);
			
			//$this->assertNull(print_r($objectList, true));
			
			$prm =
				Primitive::identifierlist('cities')->
				setIgnoreWrong(true)->
				of('TestCity');
			
			$this->assertTrue(
				$prm->import(
						array(
						'cities' =>
							array_merge(
								ArrayUtils::getIdsArray($objectList),
								array(4,5,6, 'foo', 'bar')
							)
						)
					)
			);
			
			$this->assertEquals(3, count($prm->getValue()));
			
			$objectList = $prm->getValue();
			
			$this->assertInstanceOf('TestCity', reset($objectList));
			
			$prm =
				\Onphp\Primitive::identifierlist('cities')->
				setIgnoreWrong(false)->
				of('TestCity');
			
			$this->assertFalse(
				$prm->import(
						array(
						'cities' =>
							array_merge(
								ArrayUtils::getIdsArray($objectList),
								array(4,5,6)
							)
						)
					)
			);
			
			$this->drop();
		}
		
		public function testPointAndPolygonProperties()
		{
			$this->create();			
			
			$squareLocation =
				\Onphp\Polygon::create(
					array(
						array(-21, -21),
						array(-21,  21),
						array( 21,  21),
						array( 21, -21)				
					)
				);
			
			$squareCapital =
				\Onphp\Point::create(array(0, 0));	

			$triangleLocation =
				\Onphp\Polygon::create(
					array(
						array( 5,  5 ),
						array(55,  5 ),
						array( 5,  55)			
					)
				);
			
			$triangleCapital =
				\Onphp\Point::create(array(6, 6));
			
			TestRegion::dao()->
				add(
					TestRegion::create()-> 
					setName('Great Square')->
					setLocation($squareLocation)->
					setCapital($squareCapital)						
				);

			TestRegion::dao()->
				add(
					TestRegion::create()-> 
					setName('Great Triangle')->
					setLocation($triangleLocation)->
					setCapital($triangleCapital)		
				);				
			
			$triangle =
				\Onphp\Criteria::create(TestRegion::dao())->
				add(
					\Onphp\Expression::eqPoints(
						DBField::create('capital'),
						$triangleCapital
					)
				)->
				get();

			$this->assertInstanceOf('Polygon', $triangle->getLocation());	
			$this->assertEquals('Great Triangle', $triangle->getName());

			$list =
				\Onphp\Criteria::create(TestRegion::dao())->
				addOrder('id')->
				getList();

			$this->assertEquals(2, count($list));

			$this->assertInstanceOf('Polygon', $list[0]->getLocation());
			$this->assertInstanceOf('Polygon', $list[1]->getLocation());			

			$this->assertTrue(
				$squareLocation->
					isEqual($list[0]->getLocation())
			);

			$this->assertTrue(
				$triangleLocation->
					isEqual($list[1]->getLocation())
			);

			$square =
				\Onphp\Criteria::create(TestRegion::dao())->
				add(
					\Onphp\Expression::containsPoint(
						\Onphp\DBField::create('location'),
						\Onphp\Point::create(array(1, 1))
					)
				)->
				get();

			$this->assertInstanceOf('Polygon', $square->getLocation());
			$this->assertEquals('Great Square', $square->getName());
			
			$this->drop();			
		}		
		
		protected function getSome()
		{
			for ($i = 1; $i < 3; ++$i) {
				$this->assertTrue(
					TestUser::dao()->getByLogic(
						\Onphp\Expression::eq('city_id', $i)
					)
					== TestUser::dao()->getById($i)
				);
			}
			
			$this->assertEquals(
				count(TestUser::dao()->getPlainList()),
				count(TestCity::dao()->getPlainList())
			);
		}
		
		private function racySave()
		{
			$lost =
				TestCity::create()->
				setId(424242)->
				setName('inexistant city');
			
			try {
				TestCity::dao()->save($lost);
				
				$this->fail();
			} catch (\Onphp\WrongStateException $e) {
				/* pass */
			}
		}
		
		private function binaryTest()
		{
			$data = null;
			
			for ($i = 0; $i < 256; ++$i)
				$data .= chr($i);
			
			$id = sha1('all sessions are evil');
			
			$stuff =
				TestBinaryStuff::create()->
				setId($id)->
				setData($data);
			
			$stuff = $stuff->dao()->import($stuff);
			
			\Onphp\Cache::me()->clean();
			
			$this->assertEquals(
				TestBinaryStuff::dao()->getById($id)->getData(),
				$data
			);
			
			TestBinaryStuff::dao()->dropById($id);
		}
		
		private function getListByIdsTest()
		{
			$first = TestUser::dao()->getById(1);
			
			TestUser::dao()->dropIdentityMap();
			
			$list = TestUser::dao()->getListByIds(array(1, 3, 2, 1, 1, 1));
			
			$this->assertEquals(count($list), 5);
			
			$this->assertEquals($list[0]->getId(), 1);
			$this->assertEquals($list[1]->getId(), 2);
			$this->assertEquals($list[2]->getId(), 1);
			$this->assertEquals($list[3]->getId(), 1);
			$this->assertEquals($list[4]->getId(), 1);
			
			$this->assertEquals($list[0], $first);
			
			$this->assertEquals(
				array(),
				TestUser::dao()->getListByIds(array(42, 42, 1738))
			);
		}
		
		private function lazyTest()
		{
			$city = TestCity::dao()->getById(1);
			
			$object = TestLazy::dao()->add(
				TestLazy::create()->
					setCity($city)->
					setCityOptional($city)->
					setEnum(
						new \Onphp\ImageType(\Onphp\ImageType::getAnyId())
					)
			);
			
			Cache::me()->clean();
			
			$form = TestLazy::proto()->makeForm();
			$form->import(
				array('id' => $object->getId())
			);
			
			$this->assertNotNull($form->getValue('id'));
			
			\Onphp\FormUtils::object2form($object, $form);
			
			foreach ($object->proto()->getPropertyList() as $name => $property) {
				if (
					$property->getRelationId() ==\Onphp\MetaRelation::ONE_TO_ONE
					&& $property->getFetchStrategyId() == \Onphp\FetchStrategy::LAZY
				) {
					$this->assertEquals(
						$object->{$property->getGetter()}(),
						$form->getValue($name)
					);
				}
			}
		}
		
		private function getByEmptyIdTest($id)
		{
			try {
				TestUser::dao()->getById($id);
				$this->fail();
			} catch (\Onphp\WrongArgumentException $e) {
				// pass
			}
		}
	}
?>

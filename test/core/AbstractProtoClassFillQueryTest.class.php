<?php

	/**
	 * @group ap 
	 */
	final class AbstractProtoClassFillQueryTest extends TestCase
	{
		public function testFillFullQueryByCity()
		{
			$city = $this->spawnCity();
			
			$insertCity = $city->proto()->fillQuery(OSQL::insert(), $city);
			$this->assertEquals(
				'INSERT INTO  (id, name, capital, large) VALUES (20, Saint-Peterburg, TRUE, TRUE)',
				$insertCity->toDialectString(ImaginaryDialect::me())
			);
		}
		
		public function testFillFullQueryByUser()
		{
			$city = $this->spawnCity();
			$user = $this->spawnUser(array('city' => $city));
			$updateUser = $user->proto()->fillQuery(OSQL::update(), $user);
			$this->assertEquals(
				'UPDATE  SET id = 77, nickname = NULL, password = NULL, '
					.'very_custom_field_name = 2011-12-31 00:00:00, '
					.'registered = 2011-12-30 00:00:00, strange_time = 01:23:45, '
					.'city_id = 20, first_optional_id = NULL, second_optional_id = NULL, '
					.'url = https://www.github.com, '
					.'properties = "a"=>"apple","b"=>"bananas",, ip = 127.0.0.1',
				$updateUser->toDialectString(ImaginaryDialect::me())
			);
		}
		
		public function testFillFullQueryByUserWithContactExt()
		{
			$contactValue = $this->spawnContactValueExt();
			$user = $this->spawnUserWithContactExt(array('contactExt' => $contactValue));
			
			$updateUser = $user->proto()->fillQuery(OSQL::update(), $user);
			$this->assertEquals(
				'UPDATE  SET id = 77, name = Aleksey, surname = Alekseev, email = foo@bar.com, '
					.'icq = 12345678, phone = 89012345678, city_id = NULL, '
					.'web = https://www.github.com/, skype = github',
				$updateUser->toDialectString(ImaginaryDialect::me())
			);
		}
		
		public function testfillDiffQueryByCityOneBoolean()
		{
			$cityOld = $this->spawnCity(array('capital' => false));
			$city = $this->spawnCity(array('capital' => true)); //1918
			
			$updateCity = $city->proto()->fillQuery(OSQL::update(), $city, $cityOld);
			$this->assertEquals(
				'UPDATE  SET capital = TRUE',
				$updateCity->toDialectString(ImaginaryDialect::me())
			);
		}
		
		public function testfillDiffQueryByCityOneString()
		{
			$cityOld = $this->spawnCity(array('name' => 'Leningrad'));
			$city = $this->spawnCity(array('name' => 'Saint-Peterburg'));
			
			$updateCity = $city->proto()->fillQuery(OSQL::update(), $city, $cityOld);
			$this->assertEquals(
				'UPDATE  SET name = Saint-Peterburg',
				$updateCity->toDialectString(ImaginaryDialect::me())
			);
		}
		
		/**
		 * @return TestCity
		 */
		private function spawnCity($options = array())
		{
			$options += array(
				'capital' => true,
				'large' => true,
				'name' => 'Saint-Peterburg',
				'id' => 20,
			);
			
			return $this->spawnObject(TestCity::create(), $options);
		}
		
		/**
		 * @return TestUser
		 */
		private function spawnUser($options = array())
		{
			$options += array(
				'id' => '77',
				'credentials' => null,
				'lastLogin' => Timestamp::create('2011-12-31'),
				'registered' => Timestamp::create('2011-12-30'),
				'strangeTime' => Time::create('01:23:45'),
				'city' => null,
				'firstOptional' => null,
				'secondOptional' => null,
				'url' => HttpUrl::create()->parse('https://www.github.com'),
				'properties' => Hstore::make(array('a' => 'apple', 'b' => 'bananas')),
				'ip' => IpAddress::create('127.0.0.1'),
			);
			
			return $this->spawnObject(TestUser::create(), $options);
					
		}
		
		/**
		 * @return TestContactValueExtended
		 */
		private function spawnContactValueExt($options = array())
		{
			$options += array(
				'id' => 2,
				'web' => 'https://www.github.com/',
				'skype' => 'github',
				'email' => 'foo@bar.com',
				'icq' => 12345678,
				'phone' => '89012345678',
				'city' => null,
			);
			
			return $this->spawnObject(TestContactValueExtended::create(), $options);
		}
		
		/**
		 * @return TestUserWithContactExtended
		 */
		private function spawnUserWithContactExt($options = array())
		{
			$options += array(
				'id' => '77',
				'name' => 'Aleksey',
				'surname' => 'Alekseev',
				'contactExt' => null,
			);
			
			return $this->spawnObject(TestUserWithContactExtended::create(), $options);
		}
		
		private function spawnObject(Prototyped $object, array $options)
		{
			foreach ($object->proto()->getPropertyList() as $propName => $property) {
				/* @var $property LightMetaProperty */
				if (isset($options[$propName])) {
					$setter = $property->getSetter();
					$object->{$setter}($options[$propName]);
				}
			}
			return $object;
		}
	}
?>
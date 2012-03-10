<?php
	/* $Id$ */
	
	final class PrimitiveTimestampTZTest extends TestCase
	{
		/**
		 * @group fff
		 */
		public function testMarried()
		{
			$currentTimeZone = new DateTimeZone(date_default_timezone_get());
			$now = new DateTime('now', $currentTimeZone);
			$zone = $now->format('O');
			
			$prm = Primitive::timestampTZ('test')->setComplex();
			
			$array = array(
				'test' => array(
					PrimitiveDate::DAY		=> '1',
					PrimitiveDate::MONTH	=> '2',
					PrimitiveDate::YEAR		=> '',
					PrimitiveTimestamp::HOURS	=> '17',
					PrimitiveTimestamp::MINUTES	=> '38',
					PrimitiveTimestamp::SECONDS	=> '59',
					PrimitiveTimestampTZ::ZONE => $currentTimeZone->getName(),
				)
			);
			
//			$this->assertFalse($prm->import($array));
//			$this->assertEquals($prm->getRawValue(), $array['test']);
			
			//not supported other epochs
//			$array['test'][PrimitiveDate::YEAR] = '3456';
//			$this->assertTrue($prm->import($array));
//			$this->assertTrue($prm->getValue()->getYear() == 3456);
//			$this->assertTrue($prm->getValue()->getHour() == 17);
			
			$array['test'][PrimitiveDate::YEAR] = '2006';
			
			$this->assertTrue($prm->import($array));
			$this->assertEquals(
				$prm->getValue()->toString(),
				'2006-02-01 17:38.59'.$zone
			);
			
			$this->assertFalse($prm->importSingle($array)); // not single
		}
		
		/**
		 * @group ff
		 */
		public function testSingle()
		{
			$prm = Primitive::timestamp('test')->setSingle();
			
			$array = array('test' => '1234-01-02 17:38:59');
			
			$this->assertTrue($prm->import($array));
			$this->assertTrue($prm->getValue()->getYear() == 1234);
			
			$array = array('test' => '1975-01-02 17:38:59');
			
			$this->assertTrue($prm->import($array));
			
			$this->assertEquals(
				$prm->getValue()->toDateTime(),
				'1975-01-02 17:38.59'
			);
		}
	}
?>
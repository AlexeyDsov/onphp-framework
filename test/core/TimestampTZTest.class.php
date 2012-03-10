<?php
	/* $Id$ */
	
	final class TimestampTZTest extends TestCase
	{
		/**
		 * @group ff
		 */
		public function testDifferentZones()
		{
			$someDate = TimestampTZ::create('2011-01-01 00:00:00 Europe/Moscow');
			$this->assertEquals(
				$someDate->toTimestamp('Europe/Moscow')->toString(),
				'2011-01-01 00:00:00'
			);
			$this->assertEquals(
				$someDate->toTimestamp('Europe/London')->toString(),
				'2010-12-31 21:00:00'
			);
			
			$moscowTime = TimestampTZ::create('2011-01-01 00:00:00 Europe/Moscow');
			$londonTime = TimestampTZ::create('2010-12-31 21:00:00 Europe/London');
			
			$this->assertEquals(TimestampTZ::compare($moscowTime, $londonTime), 0);
			
			$moscowTime->modify('+ 1 second');
			$this->assertEquals(TimestampTZ::compare($moscowTime, $londonTime), 1);
			$moscowTime->modify('- 2 second');
			$this->assertEquals(TimestampTZ::compare($moscowTime, $londonTime), -1);
			
			
			$this->assertEquals(
				$moscowTime->toTimestamp('Europe/Moscow')->toString(),
				'2010-12-31 23:59:59'
			);
		}
		
		/**
		 * @group ff
		 */
		public function testDialect()
		{
			//setup
			$someDate = TimestampTZ::create('2012-02-23 12:12:12 GMT');
			//expectation
			$someTime = new DateTime('now');
			$expectation = $someDate->toTimestamp()->toString(date_default_timezone_get())
				.$someTime->format('O');
			
			//check
			$this->assertEquals(
				$someDate->toDialectString(ImaginaryDialect::me()),
				$expectation
			);
		}
		
		/**
		 * @group ff
		 */
		public function testSleeping() {
			$time = TimestampTZ::create('2011-03-08 12:12:12 PST');
			$sleepedTime = unserialize(serialize($time));
			
			$this->assertEquals(TimestampTZ::compare($time, $sleepedTime), 0);
		}
	}
?>

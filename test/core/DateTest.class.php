<?php
	/* $Id$ */
	
	final class DateTest extends TestCase
	{
		public function testDayDifference()
		{
			$today = Date::makeToday();
			
			$this->dayDifferenceTest($today, $today, 0);
			
			$timestamp = Timestamp::makeNow();
			
			$this->dayDifferenceTest($timestamp, $timestamp, 0);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-13');
			
			$this->dayDifferenceTest($left, $right, 1);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2009-01-13');
			
			$this->dayDifferenceTest($left, $right, 367);
			
			$left = Date::create('2008-01-12');
			$right = Date::create('2008-01-11');
			
			$this->dayDifferenceTest($left, $right, -1);
			
			$left = Timestamp::create('2008-01-12 01:23:00');
			$right = Timestamp::create('2008-01-13 13:01:17');
			
			$this->dayDifferenceTest($left, $right, 1);
			
			return $this;
		}
		
		private function oldDayDifference(Date $left, Date $right)
		{
			return
				gregoriantojd(
					$right->getMonth(),
					$right->getDay(),
					$right->getYear()
				)
				- gregoriantojd(
					$left->getMonth(),
					$left->getDay(),
					$left->getYear()
				);
		}
		
		private function dayDifferenceTest(Date $left, Date $right, $expected)
		{
			$this->assertEquals(
				Date::dayDifference($left, $right),
				$this->oldDayDifference($left, $right)
			);
			
			$this->assertEquals(Date::dayDifference($left, $right), $expected);
			
			return $this;
		}
	}
?>
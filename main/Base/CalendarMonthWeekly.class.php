<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Calendar month representation splitted by weeks.
	 *
	 * @ingroup Calendar
	**/
	class CalendarMonthWeekly
	{
		private $monthRange	= null;
		private $fullRange	= null;
		private $fullLength	= null;
		
		private $weeks		= array();
		private $days		= array();
		
		public function __construct(
			Timestamp $base, $weekStart = Timestamp::WEEKDAY_MONDAY
		)
		{
			$firstDayOfMonth = Timestamp::create(
				$base->getYear().'-'.$base->getMonth().'-01'
			);
			
			$lastDayOfMonth	= Timestamp::create(
				$base->getYear().'-'.$base->getMonth().'-'
				.date('t', $base->toStamp()));
			
			$start = $firstDayOfMonth->getFirstDayOfWeek($weekStart);
			
			$end = $lastDayOfMonth->getLastDayOfWeek($weekStart);
			
			$this->monthRange = DateRange::create()->lazySet(
				$firstDayOfMonth, $lastDayOfMonth
			);
			
			$this->fullRange = DateRange::create()->lazySet(
				$start, $end
			);
			
			$rawDays = $this->fullRange->split();
			$this->fullLength = 0;
			
			foreach ($rawDays as $rawDay) {
				$day = CalendarDay::create($rawDay->toStamp());
				
				if ($this->monthRange->contains($day))
					$day->setOutside(false);
				else 
					$day->setOutside(true);
					
				$this->days[$day->toDate()] = $day;
				
				$weekNumber = floor($this->fullLength/7);
				
				if (!isset($this->weeks[$weekNumber]))
					$this->weeks[$weekNumber] = CalendarWeek::create();
				
				$this->weeks[$weekNumber]->addDay($day);
				++$this->fullLength;
			}
			
			++$this->fullLength;
		}
		
		public static function create(
			Timestamp $base, $weekStart = Timestamp::WEEKDAY_MONDAY
		)
		{
			return new CalendarMonthWeekly($base, $weekStart);
		}
		
		public function getWeeks()
		{
			return $this->weeks;
		}
		
		public function getDays()
		{
			return $this->days;
		}
		
		public function getFullRange()
		{
			return $this->fullRange;
		}
		
		public function getFullLength()
		{
			return $this->fullLength;
		}
		
		public function getMonthRange()
		{
			return $this->monthRange;
		}
		
		public function setSelected(Timestamp $day)
		{
			if (!isset($this->days[$day->toDate()]))
				throw new WrongArgumentException($day->toDate().' not in calendar');
			
			$this->days[$day->toDate()]->setSelected(true);
			
			return $this;
		}
		
		public function getNextMonthBase()
		{
			return $this->monthRange->getEnd()->spawn('+1 day');
		}

		public function getPrevMonthBase()
		{
			return $this->monthRange->getStart()->spawn('-1 day');
		}
		
		public function getBase()
		{
			return $this->monthRange->getStart();
		}
	}
?>
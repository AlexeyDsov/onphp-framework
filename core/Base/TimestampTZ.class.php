<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexey S. Denisov                               *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * time with timezone container and utilities.
	 *
	 * @see Date
	 *
	 * @ingroup Base
	**/
	class TimestampTZ extends Timestamp
	{	
		/**
		 * @return TimestampTZ
		**/
		public static function create($timestamp)
		{
			return new self($timestamp);
		}
		
		/**
		 * @return TimestampTZ
		**/
		public static function makeNow()
		{
			return new self(time());
		}
		
		/**
		 * @return TimestampTZ
		**/
		public static function makeToday()
		{
			return new self(self::today());
		}
		
		protected static function getFormat()
		{
			return 'Y-m-d H:i:sO';
		}
		
		/**
		 * @param mixed $zone string|DateTimeZone
		 * @return Timestamp
		**/
		public function toTimestamp($zone = null)
		{
			if ($zone && is_string($zone)) {
				$zone = new DateTimeZone($zone);
			}
			
			if ($zone) {
				$defaultZone = new DateTimeZone(date_default_timezone_get());
				$zoneOffset = $defaultZone->getOffset(new DateTime($this->string))
					- $zone->getOffset(new DateTime($this->string));
			} else {
				$zoneOffset = 0;
			}
			return Timestamp::create($this->int - $zoneOffset);
		}
		
		/* void */ protected function stringImport($string)
		{
			if (
				preg_match(
					'~^'
						.'(\d{1,4})-(\d{1,2})-(\d{1,2})'
						.'(?:'
							.'\s\d{1,2}:\d{1,2}:\d{1,2}'
							.'(?:\s*[+\-]\d{2,4})?'
						.')?'
						.'$~',
					$string,
					$matches
				)
			) {
				if (checkdate($matches[2], $matches[3], $matches[1])) {
					var_dump($matches);
					exit;
					$this->string = date($this->getFormat(), strtotime($string));
				}
			} elseif (($stamp = strtotime($string)) !== false)
				$this->string = date($this->getFormat(), $stamp);
		}
		
		/* void */ protected function import($string)
		{
			list($date, $timezone) = explode(' ', $string, 2);
			$delimetr = '-';
			if (mb_strpos($timezone, $delimetr)) {
				list($time, $zone) = explode($delimetr, $timezone);
			} else {
				list($time, $zone) = explode('+', $timezone);
				$delimetr = '+';
			}
			
			parent::import($date.' '.$time);
			
			$this->string .= $delimetr.$zone;
		}
	}
?>
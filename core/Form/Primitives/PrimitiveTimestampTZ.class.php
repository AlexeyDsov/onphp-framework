<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Primitives
	**/
	final class PrimitiveTimestampTZ extends PrimitiveTimestamp
	{
		const ZONE = 'zone';
		
		public function importMarried($scope)
		{
			if (
				BasePrimitive::import($scope)
				&& isset(
					$scope[$this->name][self::DAY],
					$scope[$this->name][self::MONTH],
					$scope[$this->name][self::YEAR],
					$scope[$this->name][self::HOURS],
					$scope[$this->name][self::MINUTES],
					$scope[$this->name][self::SECONDS],
					$scope[$this->name][self::ZONE]
				)
				&& is_array($scope[$this->name])
			) {
				if ($this->isEmpty($scope))
					return !$this->isRequired();
				
				$zone = $scope[$this->name][self::ZONE];
				
				$hours = (int) $scope[$this->name][self::HOURS];
				$minutes = (int) $scope[$this->name][self::MINUTES];
				$seconds = (int) $scope[$this->name][self::SECONDS];
				
				$year = (int) $scope[$this->name][self::YEAR];
				$month = (int) $scope[$this->name][self::MONTH];
				$day = (int) $scope[$this->name][self::DAY];
				
				if (!checkdate($month, $day, $year))
					return false;
				
				try {
					$stamp = new TimestampTZ(
						$year.'-'.$month.'-'.$day.' '
						.$hours.':'.$minutes.':'.$seconds
						.' '.$zone
					);
					print $year.'-'.$month.'-'.$day.' '
						.$hours.':'.$minutes.':'.$seconds
						.' '.$zone."\n";
				} catch (WrongArgumentException $e) {
					var_dump(get_class($e), $e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
					// fsck wrong stamps
					return false;
				}
				
				if ($this->checkRanges($stamp)) {
					$this->value = $stamp;
					return true;
				}
			}
			
			return false;
		}
		
		protected function getObjectName()
		{
			return 'TimestampTZ';
		}
	}
?>
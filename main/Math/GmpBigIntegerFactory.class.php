<?php
/***************************************************************************
 *   Copyright (C) 2007 by Anton E. Lebedevich                             *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class GmpBigIntegerFactory extends BigNumberFactory
	{
		/**
		 * @return GmpBigIntegerFactory
		**/
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function makeNumber($number, $base = 10)
		{
			return GmpBigInteger::create($number, $base);
		}
		
		/**
		 * @return GmpBigInteger
		**/
		public function makeFromBinary($binary)
		{
			return GmpBigInteger::makeFromBinary($binary);
		}
	}
?>
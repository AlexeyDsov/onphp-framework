<?php
/***************************************************************************
 *   Copyright (C) 2005-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Widely used assertions.
	 * 
	 * @ingroup Base
	**/
	final class Assert extends StaticFactory
	{
		public static function isTrue($boolean, $message = null)
		{
			if ($boolean !== true)
				self::fail($message);
		}

		public static function isFalse($boolean, $message = null)
		{
			if ($boolean !== false)
				self::fail($message);
		}

		public static function isArray(&$variable, $message = null)
		{
			if (!is_array($variable))
				self::fail($message);
		}

		public static function isInteger($variable, $message = null)
		{
			if (
				!(
					$variable == (int) $variable
					&& is_numeric($variable)
				)
			)
				self::fail($message);
		}

		public static function isFloat($variable, $message = null)
		{
			if (
				!(
					$variable == (float) $variable
					&& is_numeric($variable)
				)
			)
				self::fail($message);
		}

		public static function isString(&$variable, $message = null)
		{
			if (!is_string($variable))
				self::fail($message);
		}
		
		public static function isBoolean(&$variable, $message = null)
		{
			if (!($variable === true || $variable === false))
				self::fail($message);
		}

		public static function isTernaryBase(&$variable, $message = null)
		{
			if (
				!(
					($variable === true)
					|| ($variable === false)
					|| ($variable === null)
				)
			)
				self::fail($message);
		}

		public static function brothers(&$first, &$second, $message = null)
		{
			if (get_class($first) !== get_class($second))
				self::fail($message);
		}
		
		public static function isUnreachable()
		{
			self::fail('unreachable code reached');
		}
		
		/// exceptionless methods
		//@{
		public static function checkInteger($value)
		{
			return (
				is_numeric($value)
				&& ($value == (int) $value)
				&& (strlen($value) == strlen((int) $value))
			);
		}
		//@}
		
		private static function fail($message = null)
		{
			throw new WrongArgumentException(
				$message
				.(
					defined('__LOCAL_DEBUG__')
						? "\n\n".print_r(debug_backtrace(), true)
						: null
				)
			);
		}
	}
?>
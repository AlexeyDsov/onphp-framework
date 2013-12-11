<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Logic
	**/
	final class ArrayExpression extends StaticFactory
	{
		const EQUAL				= '=';
		const NOT_EQUAL			= '<>';
		
		const LOWER_THAN		= '<';
		const LOWER_OR_EQUAL	= '<=';
		
		const GREATER_THAN		= '>';
		const GREATER_OR_EQUAL	= '>=';
		
		const CONTAINS			= '@>';
		const CONTAINED_BY		= '<@';
		
		const OVERLAP			= '&&';
		const CONCAT			= '||';
		
		/**
		 * @return BinaryExpression
		**/
		public static function eq($field, $value)
		{
			return new BinaryExpression($field, $value, self::EQUAL);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function notEq($field, $value)
		{
			return new BinaryExpression($field, $value, self::NOT_EQUAL);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function lt($field, $value)
		{
			return new BinaryExpression($field, $value, self::LOWER_THAN);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function ltEq($field, $value)
		{
			return new BinaryExpression($field, $value, self::LOWER_OR_EQUAL);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function gt($field, $value)
		{
			return new BinaryExpression($field, $value, self::GREATER_THAN);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function gtEq($field, $value)
		{
			return new BinaryExpression($field, $value, self::GREATER_OR_EQUAL);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function containsValue($field, $value)
		{
			return new BinaryExpression($field, $value, self::CONTAINS);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function containedByValue($field, $value)
		{
			return new BinaryExpression($field, $value, self::CONTAINED_BY);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function overlap($field, $value)
		{
			return new BinaryExpression($field, $value, self::OVERLAP);
		}
		
		/**
		 * @return BinaryExpression
		**/
		public static function concat($field, $value)
		{
			return new BinaryExpression($field, $value, self::CONCAT);
		}
		
		/**
		 * @return ArrayAccessExpression
		**/
		public static function access(/* ... */)
		{
			return new ArrayAccessExpression(func_get_args());
		}
	}
?>
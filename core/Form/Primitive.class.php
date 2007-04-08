<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Factory for various Primitives.
	 * 
	 * @ingroup Form
	**/
	final class Primitive extends StaticFactory
	{
		/**
		 * @return BasePrimitive
		**/
		public static function spawn($primitive, $name)
		{
			Assert::isTrue(class_exists($primitive, true));
			
			return new $primitive($name);
		}
		
		/**
		 * @return PrimitiveInteger
		**/
		public static function integer($name)
		{
			return new PrimitiveInteger($name);
		}
		
		/**
		 * @return PrimitiveFloat
		**/
		public static function float($name)
		{
			return new PrimitiveFloat($name);
		}

		/**
		 * @return PrimitiveIdentifier
		**/
		public static function identifier($name)
		{
			return new PrimitiveIdentifier($name);
		}
		
		/**
		 * @return PrimitiveIdentifierList
		**/
		public static function identifierlist($name)
		{
			return new PrimitiveIdentifierList($name);
		}

		/**
		 * @return PrimitiveEnumeration
		**/
		public static function enumeration($name)
		{
			return new PrimitiveEnumeration($name);
		}
		
		/**
		 * @return PrimitiveDate
		**/
		public static function date($name)
		{
			return new PrimitiveDate($name);
		}
		
		/**
		 * @return PrimitiveTimestamp
		**/
		public static function timestamp($name)
		{
			return new PrimitiveTimestamp($name);
		}
		
		/**
		 * @return PrimitiveTime
		**/
		public static function time($name)
		{
			return new PrimitiveTime($name);
		}
		
		/**
		 * @return PrimitiveString
		**/
		public static function string($name)
		{
			return new PrimitiveString($name);
		}
		
		/**
		 * @return PrimitiveRange
		**/
		public static function range($name)
		{
			return new PrimitiveRange($name);
		}
		
		/**
		 * @return PrimitiveDateRange
		**/
		public static function dateRange($name)
		{
			return new PrimitiveDateRange($name);
		}
		
		public static function timestampRange($name)
		{
			throw new UnimplementedFeatureException();
		}
		
		/**
		 * @return PrimitiveList
		**/
		public static function choice($name)
		{
			return new PrimitiveList($name);
		}
		
		/**
		 * @return PrimitiveArray
		**/
		public static function set($name)
		{
			return new PrimitiveArray($name);
		}

		/**
		 * @return PrimitiveMultiList
		**/
		public static function multiChoice($name)
		{
			return new PrimitiveMultiList($name);
		}
		
		/**
		 * @return PrimitivePlainList
		**/
		public static function plainChoice($name)
		{
			return new PrimitivePlainList($name);
		}
		
		/**
		 * @return PrimitiveBoolean
		**/
		public static function boolean($name)
		{
			return new PrimitiveBoolean($name);
		}
		
		/**
		 * @return PrimitiveTernary
		**/
		public static function ternary($name)
		{
			return new PrimitiveTernary($name);
		}
		
		/**
		 * @return PrimitiveFile
		**/
		public static function file($name)
		{
			return new PrimitiveFile($name);
		}
		
		/**
		 * @return PrimitiveImage
		**/
		public static function image($name)
		{
			return new PrimitiveImage($name);
		}
		
		/**
		 * @return ExplodedPrimitive
		**/
		public static function exploded($name)
		{
			return new ExplodedPrimitive($name);
		}
		
		/**
		 * @return PrimitiveInet
		**/
		public static function inet($name)
		{
			return new PrimitiveInet($name);
		}
	}
?>
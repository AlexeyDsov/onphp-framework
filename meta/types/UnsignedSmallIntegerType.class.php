<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Types
	**/
	final class UnsignedSmallIntegerType extends SmallIntegerType
	{
		public function toColumnType()
		{
			return
				parent::toColumnType()
				."->\n"
				.'setUnsigned(true)';
		}
		
		public function toPrimitiveLimits()
		{
			return 'setMin(0)->'."\n".'setMax(PrimitiveInteger::UNSIGNED_SMALL_MAX)';
		}
	}
?>
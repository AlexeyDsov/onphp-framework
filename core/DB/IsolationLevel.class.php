<?php
/***************************************************************************
 *   Copyright (C) 2005 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Transaction isolation levels.
	 *
	 * @see http://www.postgresql.org/docs/8.0/interactive/sql-start-transaction.html
	 * 
	 * @ingroup DB
	**/
	final class IsolationLevel extends Enumeration
	{
		const READ_COMMITTED	= 0x01;
		const READ_UNCOMMITTED	= 0x02;
		const REPEATABLE_READ	= 0x03;
		const SERIALIZABLE		= 0x04;
		
		protected $names	= array(
			0 => 'read commited',
			1 => 'read uncommitted',
			2 => 'repeatable read',
			3 => 'serializable'
		);
	}
?>
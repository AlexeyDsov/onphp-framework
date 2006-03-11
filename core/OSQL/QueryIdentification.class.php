<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *   voxus@onphp.org                                                       *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup OSQL
	**/
	abstract class QueryIdentification implements Query
	{
		public function getId()
		{
			static $dialect = null;
			
			if ($dialect === null)
				$dialect = new ImaginaryDialect();

			return sha1($this->toString($dialect));
		}
		
		final public function setId($id)
		{
			throw new UnsupportedMethodException();
		}
	}
?>
<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * Essential interface for DAO-related operations.
	 * 
	 * @see IdentifiableObject
	 * 
	 * @ingroup Base
	 * @ingroup Module
	**/
	interface Identifiable
	{
		public function getId();
		public function setId($id);
	}
?>
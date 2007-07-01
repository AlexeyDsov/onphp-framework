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
	 * @ingroup Projections
	**/
	abstract class CountProjection extends BaseProjection
	{
		/**
		 * @return JoinCapableQuery
		**/
		public function process(Criteria $criteria, JoinCapableQuery $query)
		{
			return $query->get($this->getFunction($criteria, $query));
		}
		
		/**
		 * @return SQLFunction
		**/
		protected function getFunction(
			Criteria $criteria,
			JoinCapableQuery $query
		)
		{
			return
				SQLFunction::create(
					'count',
					$this->property
						? $criteria->getDao()->guessAtom($this->property, $query)
						: $criteria->getDao()->getIdName()
				)->
				setAlias($this->alias);
		}
	}
?>
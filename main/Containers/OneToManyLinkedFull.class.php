<?php
/***************************************************************************
 *   Copyright (C) 2005-2006 by Konstantin V. Arkhipov                     *
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
	 * @ingroup Containers
	**/
	final class OneToManyLinkedFull extends OneToManyLinkedWorker
	{
		public function makeFetchQuery()
		{
			$uc = $this->container;
			
			return
				$this->targetize(
					$this->oq
						? $this->oq->toSelectQuery($uc->getDao())
						: $uc->getDao()->makeSelectHead()
				);
		}

		public function sync(&$insert, &$update = array(), &$delete)
		{
			$uc = $this->container;
			$dao = $uc->getDao();

			if ($insert)
				for ($i = 0, $size = count($insert); $i < $size; ++$i)
					$dao->add($insert[$i]);

			if ($update)
				for ($i = 0, $size = count($update); $i < $size; ++$i)
					$dao->save($update[$i]);

			if ($delete) {
				DBFactory::getDefaultInstance()->queryNull(
					OSQL::delete()->from($dao->getTable())->
					where(
						Expression::eq(
							new DBField($uc->getParentIdField()),
							$uc->getParentObject()->getId()
						)
					)->
					andWhere(
						Expression::in(
							$uc->getChildIdField(),
							ArrayUtils::getIdsArray($delete)
						)
					)
				);
				
				foreach ($delete as $object)
					$dao->uncacheById($object->getId());
			}

			return $this;
		}
	}
?>
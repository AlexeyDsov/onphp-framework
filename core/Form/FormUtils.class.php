<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Form
	**/
	final class FormUtils extends StaticFactory
	{
		/* void */ public static function object2form(
			$object, Form $form, $ignoreNull = true
		)
		{
			Assert::isTrue(is_object($object));
			
			$class = new ReflectionClass($object);
			$primitives = $form->getPrimitiveList();
			
			foreach ($class->getProperties() as $property) {
				$name = $property->getName();
				
				if (isset($primitives[$name])) {
					
					$getter	= 'get'.ucfirst($name);
					$value	= $object->$getter();
					$prm	= $primitives[$name];
					
					if (
						$class->hasMethod($getter)
						&& (!$ignoreNull || ($value !== null))
					) {
						$form->importValue($name, $value);
					}
				}
			}
		}
		
		/* void */ public static function form2object(
			Form $form, $object, $ignoreNull = true
		)
		{
			Assert::isTrue(is_object($object));
			
			$class = new ReflectionClass($object);
			
			foreach ($form->getPrimitiveList() as $name => $prm) {
				$setter = 'set'.ucfirst($name);
				
				if ($prm instanceof ListedPrimitive)
					$value = $prm->getChoiceValue();
				else
					$value = $prm->getValue();
				
				if (
					$class->hasMethod($setter)
					&& (!$ignoreNull || ($value !== null))
				) {
					if ($prm instanceof PrimitiveList) {
						$list = $prm->getList();
						$value = $list[$value];
					} elseif ( // magic!
						$prm->getName() == 'id'
						&& $value instanceof Identifiable
					) {
						$value = $value->getId();
					}
					
					if ($value === null) {
						$dropper = 'drop'.ucfirst($name);
						
						if ($class->hasMethod($dropper)) {
							$object->$dropper();
							continue;
						}
					}

					$object->$setter($value);
				}
			}
		}
		
		public static function checkPrototyped(Prototyped $object)
		{
			$form = $object->proto()->makeForm();
			
			self::object2form($object, $form, false);
			
			return $form->getErrors();
		}
	}
?>
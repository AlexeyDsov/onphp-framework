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
	 * @ingroup Builders
	**/
	final class ProtoClassBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			$out = self::getHead();
			
			if ($type = $class->getType())
				$type = $type->toString().' ';
			else
				$type = null;

			$parent = $class->getParent();
			
			if ($parent) {
				$out .= <<<EOT
{$type}class Proto{$class->getName()} extends Proto{$parent->getName()}
{
	public function makeForm()
	{
		return
			parent::makeForm()->
			add(
EOT;
			} else {
				$out .= <<<EOT
{$type}class Proto{$class->getName()} extends AbstractProtoClass
{
	public function makeForm()
	{
		return
			Form::create()->
			add(
EOT;
			}
			
			// sort out for wise and common defaults
			$prms = array();
			
			foreach ($class->getProperties() as $property) {
				
				if (
					(
						$property->getType() instanceof ObjectType
						&& !$property->getType()->isGeneric()
					)
					|| $property->isIdentifier()
				) {
					
					if (
						!$property->isIdentifier() 
						&&
							MetaConfiguration::me()->getClassByName(
								$property->getType()->getClass()
							)->
							getPattern() instanceof EnumerationClassPattern
					)
						$isEnum = true;
					else
						$isEnum = false;

					if ($isEnum) {
						$className = MetaConfiguration::me()->getClassByName(
							$property->getType()->getClass()
						)->getName();
						
						$primitiveName = $property->getName()/*.'Id'*/;
					} elseif ($property->isIdentifier()) {
						$className = $class->getName();
						$primitiveName = 'id';
					} else {
						$className = $property->getType()->getClass();
						$primitiveName = $property->getName()/*.'Id'*/;
					}
					
					if ($isEnum) {
						$primitive =
							"\nPrimitive::enumeration('{$primitiveName}')->\n"
							."of('{$className}')->\n";
					} else {
						if (
							!$property->getRelation()
							|| (
								$property->getRelation()->getId()
								== MetaRelation::ONE_TO_ONE
							)
						) {
							$primitive =
								"\nPrimitive::identifier('{$primitiveName}')->\n"
								."of('{$className}')->\n";
						} else {
							$primitive = null;
						}
					}
					
					if ($primitive) {
						if ($property->getType()->hasDefault())
							$primitive .=
								"setDefault({$property->getType()->getDefault()})->\n";
						
						if ($property->isRequired())
							$primitive .= "required()\n";
						else
							$primitive .= "optional()\n";
						
					}
				} else
					$primitive = $property->toPrimitive();
				
				if ($primitive)
					$prms[] = $primitive;
			}
			
			$out .= implode(")->\nadd(", $prms).");";
			
			$out .= <<<EOT

	}
}

EOT;

			return $out.self::getHeel();
		}
	}
?>
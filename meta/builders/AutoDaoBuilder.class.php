<?php
/***************************************************************************
 *   Copyright (C) 2006-2008 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Builders
	**/
	namespace Onphp;

	final class AutoDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			if (!$class->hasBuildableParent())
				return DictionaryDaoBuilder::build($class);
			else
				$parent = $class->getParent();
			
			if (
				$class->getParent()->getPattern()
					instanceof InternalClassPattern
			) {
				$parentName = '\Onphp\StorableDAO';
			} else {
				$parentName = $parent->getFullClassName('', 'DAO');
			}
			
			$out = self::getHead();
			
			if ($namespace = trim($class->getNamespace(), '\\'))
				$out .= "namespace {$namespace};\n\n";
			
			$out .= <<<EOT
abstract class {$class->getName('Auto', 'DAO')} extends {$parentName}
{

EOT;

			if (
				$class->isFromSlave()
				&& (
				!$class->getParent()->getPattern()
				instanceof InternalClassPattern
				)
			) {
				$propertyValue = $class->isFromSlave() ? 'true' : 'false';

				$out .= <<<EOT
	protected \$useSlave = {$propertyValue};
	

EOT;
			}
			
			$out .= self::buildPointers($class)."\n}\n";
			
			return $out.self::getHeel();
		}
	}
?>

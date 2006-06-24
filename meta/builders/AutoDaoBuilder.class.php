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
	final class AutoDaoBuilder extends BaseBuilder
	{
		public static function build(MetaClass $class)
		{
			if (!$parent = $class->getParent())
				return DictionaryDaoBuilder::build($class);
			
			$parentName = $parent->getName().'DAO';
			$className = $class->getName();
			$varName = strtolower($className[0]).substr($className, 1);

			$out = self::getHead();
			
			$out .= <<<EOT
abstract class Auto{$class->getName()}DAO extends {$parentName}
{
	public function __construct()
	{
		\$this->mapping = array_merge(
			\$this->mapping,
			array(

EOT;
			if ($parent->getPattern() instanceof StraightMappingPattern) {
				$out .= "parent::__construct();\n\n";
			}
			
			$mapping = self::buildMapping($class);
			$pointers = self::buildPointers($class);
			
			$out .= implode(",\n", $mapping)."\n";

			$out .= <<<EOT
				)
			);
		}

{$pointers}

public function setQueryFields(InsertOrUpdateQuery \$query, /* {$className} */ \${$varName})
{
	return
		parent::setQueryFields(\$query, \${$varName})->

EOT;

			$out .= self::buildFillers($class);
			
			return $out.self::getHeel();
		}
	}
?>
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
/* $Id$ */

	/**
	 * @ingroup Builders
	**/
	abstract class BaseBuilder extends StaticFactory
	{
		public static function build(MetaClass $class)
		{
			throw new UnimplementedFeatureException('i am forgotten method');
		}
		
		protected static function buildPointers(MetaClass $class)
		{
			$out = null;
			
			if (!$class->getPattern() instanceof AbstractClassPattern) {
				if (
					$class->getIdentifier()->getColumnName() !== 'id'
				) {
					$out .= <<<EOT
public function getIdName()
{
	return '{$class->getIdentifier()->getColumnName()}';
}

EOT;
				}
				
				$out .= <<<EOT
public function getTable()
{
	return '{$class->getTableName()}';
}

public function getObjectName()
{
	return '{$class->getName()}';
}

public function getSequence()
{
	return '{$class->getTableName()}_id';
}
EOT;
			} elseif ($class->getWithInternalProperties()) {
				$out .= <<<EOT
// no get{Table,ObjectName,Sequence} for abstract class
EOT;
			}
			
			if ($source = $class->getSourceLink()) {
				$out .= <<<EOT


public function getLinkName()
{
	return '{$source}';
}

EOT;
			}
			
			return $out;
		}
		
		protected static function getHead()
		{
			$head = self::startCap();
			
			$head .=
				' *   This file is autogenerated - do not edit.'
				.'                               *';

			return $head."\n".self::endCap();
		}
		
		protected static function startCap()
		{
			$version = ONPHP_VERSION;
			$date = date('Y-m-d H:i:s');
			
			$info = " *   Generated by onPHP-{$version} at {$date}";
			$info = str_pad($info, 77, ' ', STR_PAD_RIGHT).'*';
			
			$cap = <<<EOT
<?php
/*****************************************************************************
 *   Copyright (C) 2006-2008, onPHP's MetaConfiguration Builder.             *
{$info}

EOT;

			return $cap;
		}
		
		protected static function endCap()
		{
			$cap = <<<EOT
 *****************************************************************************/
/* \$Id\$ */


EOT;
			return $cap;
		}
		
		protected static function getHeel()
		{
			return '?>';
		}
	}
?>
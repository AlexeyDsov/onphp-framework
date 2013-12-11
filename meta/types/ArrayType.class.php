<?php
/***************************************************************************
 *   Copyright (C) 2012 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Types
	**/
	class ArrayType extends BasePropertyType
	{
		public function getPrimitiveName()
		{
			return 'array';
		}
		
		public function getDeclaration()
		{
			return 'array()';
		}
		
		public function toColumnType()
		{
			return null;
		}
		
		public function isMeasurable()
		{
			return false;
		}
		
		public function setDefault($default)
		{
			throw new UnsupportedMethodException(
				'Arrays can\'t have default values atm'
			);
		}
		
		public function toSetter(
			MetaClass $class,
			MetaClassProperty $property,
			MetaClassProperty $holder = null
		)
		{
			$name = $property->getName();
			$methodName = 'set'.ucfirst($name);
			
			if ($holder) {
				return <<<EOT

/**
 * @return {$holder->getClass()->getName()}
**/
public function {$methodName}(array \${$name})
{
	\$this->{$holder->getName()}->{$methodName}(\${$name});
	
	return \$this;
}

EOT;
			} else {
				$method = <<<EOT

/**
 * @return {$class->getName()}
**/
public function {$methodName}(array \${$name})
{
	\$this->{$name} = \${$name};
	
	return \$this;
}

EOT;
			}
			
			return $method;
		}
	}
?>

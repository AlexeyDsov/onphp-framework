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
	 * @ingroup MetaBase
	**/
	final class MetaConfiguration extends Singleton implements Instantiatable
	{
		private $classes = array();
		
		public static function me()
		{
			return Singleton::getInstance('MetaConfiguration');
		}
		
		public function load($metafile)
		{
			$xml = simplexml_load_file($metafile);
			
			$liaisons = array();
			
			foreach ($xml->classes[0] as $xmlClass) {
				
				$class = new MetaClass((string) $xmlClass['name']);
				
				if (isset($xmlClass['type']))
					$class->setType(
						new MetaClassType(
							(string) $xmlClass['type']
						)
					);
				
				// lazy existence checking
				if (isset($xmlClass['extends']))
					$liaisons[$class->getName()] = (string) $xmlClass['extends'];
				
				// populate implemented interfaces
				foreach ($xmlClass->implement as $xmlImplement)
					$class->addInterface((string) $xmlImplement['interface']);
				
				// populate properties
				foreach ($xmlClass->properties[0] as $xmlProperty) {
					
					$property = $this->buildProperty(
						(string) $xmlProperty['name'],
						(string) $xmlProperty['type']
					);
					
					if ((string) $xmlProperty['required'] == 'true')
						$property->required();
					
					if ((string) $xmlProperty['identifier'] == 'true') {
						$property->setIdentifier(true);
						
						// we don't need anything but
						// only identifier for spooked classes
						if (
							$class->getType()
							&& $class->getType()->getId()
								== MetaClassType::CLASS_SPOOKED
						) {
							$class->addProperty($property);
							
							break;
						}
					}
					
					if (isset($xmlProperty['size']))
						$property->setSize((int) $xmlProperty['size']);
					
					if (!$property->getType()->isGeneric()) {
						
						if (!isset($xmlProperty['relation']))
							throw new MissingElementException(
								'relation should be set for non-generic '
								."type '".get_class($property->getType())."'"
								." of '{$class->getName()}' class"
							);
						else {
							$property->setRelation(
								new MetaRelation(
									(string) $xmlProperty['relation']
								)
							);
						}
					}
					
					if (isset($xmlProperty['default']))
						// will be correctly autocasted further down the code
						$property->getType()->setDefault(
							(string) $xmlProperty['default']
						);
					
					$class->addProperty($property);
				}
				
				$class->setPattern(
					$this->guessPattern((string) $xmlClass->pattern['name'])
				);
				
				$this->classes[$class->getName()] = $class;
			}
			
			foreach ($liaisons as $class => $parent) {
				if (isset($this->classes[$parent])) {
					
					if (
						$this->classes[$class]->getPattern()
							instanceof DictionaryClassPattern
					)
						throw new UnsupportedMethodException(
							'DictionaryClass pattern does '
							.'not support inheritance'
						);
					
					$this->classes[$class]->setParent(
						$this->classes[$parent]
					);
				} else
					throw new MissingElementException(
						"unknown parent class '{$parent}'"
					);
			}
			
			// final sanity checking
			foreach ($this->classes as $name => $class) {
				$this->checkSanity($class);
			}
			
			return $this;
		}
		
		public function build()
		{
			foreach ($this->classes as $name => $class) {
				echo $name."\n";
				$class->dump();
			}
			
			$schema = SchemaBuilder::getHead();
			
			foreach ($this->classes as $name => $class) {
				$schema .= SchemaBuilder::build($class);
			}
			
			foreach ($this->classes as $name => $class) {
				$schema .= SchemaBuilder::buildRelations($class);
			}
			
			$schema .= '?>';
			
			BasePattern::dumpFile(
				ONPHP_META_AUTO_DIR.'schema.php',
				Format::indentize($schema)
			);
		}
		
		public function getClassByName($name)
		{
			if (isset($this->classes[$name]))
				return $this->classes[$name];
			
			throw new MissingElementException(
				"knows nothing about '{$name}' class"
			);
		}
		
		private function buildProperty($name, $type)
		{
			if (is_readable(ONPHP_META_TYPES.$type.'Type'.EXT_CLASS))
				$class = $type.'Type';
			else
				$class = 'ObjectType';
			
			return new MetaClassProperty($name, new $class($type));
		}
		
		private function guessPattern($name)
		{
			$class = $name.'Pattern';
			
			if (is_readable(ONPHP_META_PATTERNS.$class.EXT_CLASS))
				return Singleton::getInstance($class);
			
			throw new MissingElementException(
				"unknown pattern '{$name}'"
			);
		}
		
		private function checkSanity(MetaClass $class)
		{
			if (!$class->getParent()) {
				Assert::isTrue(
					$class->getIdentifier() !== null,
					
					'no one can live without identifier'
				);
			} else {
				$parent = $class->getParent();
				
				while ($parent->getParent())
					$parent = $parent->getParent();
				
				Assert::isTrue(
					$parent->getIdentifier() !== null,
					
					'can not find parent with identifier'
				);
			}
			
			if (
				$class->getType() 
				&& $class->getType()->getId() 
					== MetaClassType::CLASS_SPOOKED
			) {
				Assert::isFalse(
					count($class->getProperties()) > 1,
					'spooked classes must have only identifier'
				);
			
				Assert::isTrue(
					$class->getPattern() instanceof SpookedClassPattern,
					'spooked classes must use SpookedClass pattern only'
				);
			}
			
			return $this;
		}
	}
?>
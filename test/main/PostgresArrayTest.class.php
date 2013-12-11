<?php
/***************************************************************************
 *   Copyright (C) 2013 by Alexander A. Zaytsev                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	namespace Onphp\Test;
	
	final class PostgresArrayTest extends TestCase
	{
		public function testNumericQuoting()
		{
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					array(
						array(1, 2, 3),
						array(4, 5, 6)
					)
				)->
				toString(),
				'{{1,2,3},{4,5,6}}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					array()
				)->
				toString(),
				'{}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					array(
						array(1.1, 2.2, 3),
						array(4.2, 5.1, 6.12)
					),
					';'
				)->
				toString(),
				'{{1.1;2.2;3};{4.2;5.1;6.12}}'
			);
		}
		
		public function testNumericUnquoting()
		{
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					'{{{11,22},{33,44}},{{55,66},{77,88}}}'
				)->
				getArrayCopy(),
				array(
					array(
						array('11', '22'),
						array('33', '44')
					),
					array(
						array('55', '66'),
						array('77', '88')
					)
				)
			);
			
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					'{{1;2};{3;4}}',
					';'
				)->
				getArrayCopy(),
				array(
					array(1, 2),
					array(3, 4)
				)
			);
			
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					'{}'
				)->
				getArrayCopy(),
				array()
			);
			
			$this->assertEquals(
				\Onphp\PostgresArray::create(
					''
				)->
				getArrayCopy(),
				array()
			);
		}
		
		public function testTextQuoting()
		{
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					array(
						array(1, 2, 3),
						array(4, 5, 6)
					)
				)->
				toString(),
				'{{"1","2","3"},{"4","5","6"}}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					array('one """ string with "quotes"')
				)->
				toString(),
				'{"one \\"\\"\\" string with \\"quotes\\""}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					array('\'single\' quotes \n\n\n')
				)->
				toString(),
				'{"\\\'single\\\' quotes \\\\n\\\\n\\\\n"}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					array('"some" string\\ with $var')
				)->
				toString(),
				'{"\\"some\\" string\\\\ with $var"}'
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					array(
						array(''),
						array('')
					)
				)->
				toString(),
				'{{""},{""}}'
			);
		}
		
		public function testTextUnquoting()
		{
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					'{"string\\\\ with $var","\\"quoted\\" string"}'
				)->
				getArrayCopy(),
				array(
					'string\ with $var',
					'"quoted" string'
				)
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					'{"string\\\\ with $var","\\"quoted\\" string"}'
				)->
				getArrayCopy(),
				array(
					'string\ with $var',
					'"quoted" string'
				)
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					'{{simpletext;"text with spaces"};{\'quoted\'text;"z;z"}}',
					';'
				)->
				getArrayCopy(),
				array(
					array('simpletext', 'text with spaces'),
					array('\'quoted\'text', 'z;z')
				)
			);
			
			$this->assertEquals(
				\Onphp\PostgresTextArray::create(
					'{"",""}'
				)->
				getArrayCopy(),
				array('', '')
			);

		}
	}
?>
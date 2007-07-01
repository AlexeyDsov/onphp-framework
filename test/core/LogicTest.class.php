<?php
	/* $Id$ */
	
	final class LogicTest extends UnitTestCase
	{
		public function testBaseSqlGeneration()
		{
			$dialect = ImaginaryDialect::me();
			
			$this->assertWantedPattern(
				'/^\(a (AND|and) b\)$/',
				Expression::expAnd('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (OR|or) b\)$/',
				Expression::expOr('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				Expression::eq('a', 'b')->toDialectString($dialect), 
				'(a = b)'
			);
			
			$some = IdentifiableObject::wrap(123);
			$this->assertEqual(
				Expression::eqId('a', $some)->toDialectString($dialect), 
				'(a = 123)'
			);
			
			$this->assertEqual(
				Expression::notEq('a', 'b')->toDialectString($dialect), 
				'(a != b)'
			);
			
			$this->assertEqual(
				Expression::gt('a', 'b')->toDialectString($dialect), 
				'(a > b)'
			);
			
			$this->assertEqual(
				Expression::gtEq('a', 'b')->toDialectString($dialect), 
				'(a >= b)'
			);
			
			$this->assertEqual(
				Expression::lt('a', 'b')->toDialectString($dialect), 
				'(a < b)'
			);
			
			$this->assertEqual(
				Expression::ltEq('a', 'b')->toDialectString($dialect), 
				'(a <= b)'
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS NOT NULL)|(is not null)) *\)$/',
				Expression::notNull('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS NULL)|(is null)) *\)$/',
				Expression::isNull('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS TRUE)|(is true)) *\)$/',
				Expression::isTrue('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((IS FALSE)|(is false)) *\)$/',
				Expression::isFalse('a')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (LIKE|like) b\)$/',
				Expression::like('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT LIKE)|(not like)) b\)$/',
				Expression::notLike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (ILIKE|ilike) b\)$/',
				Expression::ilike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT ILIKE)|(not like)) b\)$/',
				Expression::notIlike('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((SIMILAR TO)|(similar to)) b\)$/',
				Expression::similar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((NOT SIMILAR TO)|(not similar to)) b\)$/',
				Expression::notSimilar('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(lower\(a\) += +lower\(b\)\)$/',
				Expression::eqLower('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (BETWEEN|between) b (AND|and) c\)$/',
				Expression::between('a', 'b', 'c')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a = 123)',
				Expression::in('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a = 123)',
				Expression::in('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (in|IN) \(123, 456\)\)$/',
				Expression::in('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a != 123)',
				Expression::notIn('a', 123)->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a != 123)',
				Expression::notIn('a', array(123))->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a ((not in)|(NOT IN)) \(123, 456\)\)$/',
				Expression::notIn('a', array(123, 456))->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a + b)',
				Expression::add('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a - b)',
				Expression::sub('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a * b)',
				Expression::mul('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(a / b)',
				Expression::div('a', 'b')->toDialectString($dialect)
			);
			
			$this->assertWantedPattern(
				'/^\(a (between|BETWEEN) b (and|AND) c\)$/',
				Expression::between('a', 'b', 'c')->toDialectString($dialect)
			);
			
			$this->assertEqual(
				'(-1 IS NULL)',
				Expression::isNull(-1)->toDialectString($dialect)
			);
		}
		
		public function testPgGeneration()
		{
			$dialect = PostgresDialect::me();
			$this->assertWantedPattern(
				'/^\(\(\(\(\'asdf\' = "b"\) (AND|and) \("e" != \("i" \/ 123\)\) (AND|and) \(\(lower\("a"\) += +lower\("b"\)\) ((IS TRUE)|(is true))\) (AND|and) \("g" = 12\) (AND|and) \("j" (BETWEEN|between) 3 (AND|and) "p"\)\) (OR|or) \("table"\."c" ((IS NOT NULL)|(is not null))\)\) (AND|and) \("sometable"\."a" ((not in)|(NOT IN)) \(\'q\', \'qwer\', \'xcvzxc\', \'wer\'\)\)\)$/',
 				Expression::expAnd(
					Expression::expOr(
						Expression::andBlock(
							Expression::eq(
								new DBValue('asdf'),
								new DBField('b')
							),
							Expression::notEq(
								new DBField('e'),
								Expression::div(
									new DBField('i'),
									new DBValue(123)
								)
							),
							Expression::isTrue(
								Expression::eqLower('a', 'b')
							),
							Expression::eq(new DBField('g'), new DBValue(12))
							,
							Expression::between('j', new DBValue(3), new DBField('p'))
						),
						Expression::notNull(new DBField('c', 'table'))
					),
					Expression::notIn(
						new DBField('a', 'sometable'),
						array('q', 'qwer', 'xcvzxc', 'wer')
					)
				)->toDialectString($dialect)
			);
		}
		
		public function testFormCalculation()
		{
			$form = Form::create()->
				add(
					Primitive::string('a')
				)->
				add(
					Primitive::boolean('b')
				)->
				add(
					Primitive::integer('c')
				)->
				add(
					Primitive::integer('d')
				)->
				add(
					Primitive::integer('e')
				)->
				add(
					Primitive::boolean('f')
				)->
				import(
					array(
						'a' => 'asDfg',
						'b' => 'true',
						'c' => '1',
						'd' => '2',
						'e' => '3'
					)
				);
			
			$this->assertTrue(
				Expression::isTrue(new FormField('b'))->toBoolean($form)
			);
			
			$this->assertFalse(
				Expression::isTrue(new FormField('f'))->toBoolean($form)
			);
			
			$this->assertFalse(
				Expression::eq('asdf', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::eqLower('asdfg', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::eq('asDfg', new FormField('a'))->toBoolean($form)
			);
			
			$this->assertTrue(
				Expression::andBlock(
					Expression::expOr(
						new FormField('b'),
						new FormField('f')
					),
					Expression::eq(
						7,
						Expression::add(
							new FormField('c'),
							Expression::mul(
								new FormField('d'),
								new FormField('e')
							)
						)
					)
				)
			);
			
			$this->assertTrue(
				Expression::between(new FormField('d'), new FormField('c'), new FormField('e'))->toBoolean($form)
			);
			
			$this->assertFalse(
				Expression::between(new FormField('c'), new FormField('d'), new FormField('e'))->toBoolean($form)
			);
			
		}
		
		public function testChainSQL()
		{
			$this->assertWantedPattern(
				'/^\(\(a (OR|or) \(b ((IS NOT NULL)|(is not null)) *\)\) (AND|and) \(c = d\) (AND|and) \(e ((IS FALSE)|(is false)) *\)\)$/',
				Expression::chain()->
					expAnd(
						Expression::expOr(
							'a',
							Expression::notNull('b')
						)
					)->
					expAnd(
						Expression::eq('c', 'd')
					)->
					expAnd(
						Expression::isFalse('e')
					)->
					toDialectString(ImaginaryDialect::me())
			);
			
			$this->assertWantedPattern(
				'/^\(\(a = b\) (OR|or) \(d (OR|or) \(c > e\)\) (OR|or) \(f (in|IN) \(qwer, asdf, zxcv\)\)\)$/',
				Expression::chain()->
					expOr(
						Expression::eq('a', 'b')
					)->
					expOr(
						Expression::expOr('d', Expression::gt('c', 'e'))
					)->
					expOr(
						Expression::in('f', array('qwer', 'asdf', 'zxcv'))
					)->
					toDialectString(ImaginaryDialect::me())
			);
		}

		public function testChainForm()
		{
			$form = Form::create()->
				add(
					Primitive::string('a')
				)->
				add(
					Primitive::string('b')
				)->
				add(
					Primitive::integer('c')
				)->
				add(
					Primitive::integer('d')
				)->
				add(
					Primitive::boolean('e')
				)->
				add(
					Primitive::string('f')
				)->
				import(
					array(
						'a' => 'true',
						'c' => 123,
						'd'	=> 123,
					)
				);
			
			$andChain = Expression::chain()->
				expAnd(
					Expression::expOr(
						new FormField('a'),
						Expression::notNull(new FormField('b'))
					)
				)->
				expAnd(
					Expression::eq(
						new FormField('c'),
						new FormField('d'))
				)->
				expAnd(
					Expression::isFalse(new FormField('e'))
				);

			$this->assertTrue($andChain->toBoolean($form));
			
			$form->importMore(array('e' => 'on'));
			$this->assertFalse($andChain->toBoolean($form));

			$orChain = Expression::chain()->
				expOr(
					Expression::eq(new FormField('a'), new FormField('b'))
				)->
				expOr(
					Expression::expOr(
						new FormField('e'),
						Expression::gt(
							new FormField('c'), 
							new FormField('d')
						)
					)
				)->
				expOr(
					Expression::in(new FormField('f'), array('qwer', 'asdf', 'zxcv'))
				);

			$form->import(array());
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'e' => '1'
			));
			$this->assertTrue($orChain->toBoolean($form));
			
			$form->import(array(
				'a' => 'asdf',
				'b' => 'qwerq',
				'c' => '13',
				'd' => '1313',
				'f' => 'iukj'
			));
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'c' => '13',
				'd' => '12'
			));
			$this->assertTrue($orChain->toBoolean($form));
			
			$form->import(array(
				'f' => 'asdfwer'
			));
			$this->assertFalse($orChain->toBoolean($form));
			
			$form->import(array(
				'f' => 'qwer'
			));
			$this->assertTrue($orChain->toBoolean($form));
		}
	}
?>
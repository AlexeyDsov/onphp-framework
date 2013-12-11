<?php
	/* $Id$ */

	namespace Onphp\Test;
	
	final class PrimitiveClassByMethodTest extends TestCase
	{
		public function testOf()
		{
			$prm = \Onphp\Primitive::classByMethod('form');

			try {
				$prm->of('InExIsNaNtClass');
				$this->fail('wrong parameter, should be WrongArgumentException exception');
			} catch (\Onphp\WrongArgumentException $e) {
				// pass
			}

			$this->assertFalse(
				$prm->
					of('\Onphp\MappedForm')->
					importValue('form')
			);

			$form = \Onphp\Form::create();
			$this->assertTrue(
				$prm->
					of('\Onphp\MappedForm')->
					importValue($form)
			);

			$this->assertFalse(
				$prm->
					of('\Onphp\Identifiable')->
					importValue(1)	//there is no method 'create'
			);
		}

		public function testSetMethodName()
		{
			$prm = \Onphp\Primitive::classByMethod('io');
			$testId = 1;

			try {
				$prm->setMethodName('::wrap');
				$this->fail('wrong parameter, should be ClassNotFoundException exception');
			} catch (\Onphp\ClassNotFoundException $e) {
				// pass
			}

			try {
				$prm->setMethodName('wrap');
				$this->fail('wrong parameter, should be WrongArgumentException exception');
			} catch (\Onphp\WrongArgumentException $e) {
				// pass
			}

			try {
				$prm->setMethodName('wrap::');
				$this->fail('wrong parameter, should be ClassNotFoundException exception');
			} catch (\Onphp\ClassNotFoundException $e) {
				// pass
			}

			$prm->
				of('\Onphp\IdentifiableObject')->
				setMethodName('wrap')->
				importValue($testId);

			$io = \Onphp\IdentifiableObject::wrap($testId);

			$this->assertEquals(
				$prm->getValue()->getId(),
				$io->getId()
			);

			$prm->
				setMethodName('\Onphp\IdentifiableObject::wrap')->
				importValue($testId);

			$this->assertEquals(
				$prm->getValue()->getId(),
				$io->getId()
			);
		}
	}
	?>
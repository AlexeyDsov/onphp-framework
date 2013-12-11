<?php

	namespace Onphp\Test;
	
	final class PrimitiveMultiListTest extends TestCase
	{
		protected $list =
			array(
				'choice_1'	=> 'bluePill',
				'choice_2'	=> 'redPill',
				3			=> 42,
			);

		protected $defaultScope = array('choice_2', 3);

		protected $importScope =
			array(
				'multiChoice' => array('choice_1', 3)
			);

		public function testImport() {
			$choicePrimitive = $this->makePrimitive();
			$choicePrimitive->import(array());

			$this->assertEquals($choicePrimitive->getSafeValue(), $this->defaultScope);

			$choicePrimitive->import($this->importScope);

			$this->assertEquals($choicePrimitive->isImported(), true);
			$this->assertEquals(
				$choicePrimitive->getValue(),
				$this->importScope['multiChoice']
			);

			$choiced = $choicePrimitive->getChoiceValue();

			foreach ($choiced as $key => $value)
				$this->assertEquals(
					$choiced[$key],
					$this->list[$key]
				);
		}

		protected function makePrimitive() {
			return \Onphp\Primitive::multiChoice('multiChoice')->
				setList($this->list)->
				setDefault($this->defaultScope);
		}
	}
?>
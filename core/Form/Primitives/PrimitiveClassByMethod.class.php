<?php

	/**
	 * @author Daniil Mashkin <d.mashkin@co.wapstart.ru>
	 * @copyright Copyright (c) 2013, Wapstart
	 */

	/**
	 * @ingroup Primitives
	 **/
	 namespace Onphp;
	 
	final class PrimitiveClassByMethod extends PrimitiveClass
	{
		private $methodName	= 'create';

		public function import($scope)
		{
			if (!($result = BasePrimitive::import($scope)))
				return $result;

			try {
				$this->value = $scope[$this->name];

				$result =
					ClassUtils::callStaticMethod(
						$this->getMethodSignature(),
						$this->value
					);

				Assert::isInstance($result, $this->ofClassName);
				$this->value = $result;

				return true;
			} catch (\Exception $e) {
				$this->value = null;

				return false;
			}

			Assert::isUnreachable();
		}

		/**
		 * @return PrimitiveClassByMethod
		 **/
		public function setMethodName($methodName)
		{
			if (strpos($methodName, '::') === false) {
				Assert::isNotNull(
					$this->ofClassName,
					'specify class name first of all. Use of(class) or setMethodName(class::method)'
				);

				Assert::isTrue(
					method_exists($this->ofClassName, $methodName),
					"knows nothing about '". $this->ofClassName
					."::{$methodName}' method"
				);

				$this->methodName = $methodName;
			} else {
				$nameParts = ClassUtils::checkStaticMethod($methodName);
				$this->of($nameParts[0]);
				$this->setMethodName($nameParts[1]);
			}

			return $this;
		}

		private function getMethodSignature()
		{
			if (!$this->ofClassName)
				throw new WrongStateException(
					"no class defined for PrimitiveClassByMethod'{$this->name}'"
				);

			return $this->ofClassName . '::' . $this->methodName;
		}
	}
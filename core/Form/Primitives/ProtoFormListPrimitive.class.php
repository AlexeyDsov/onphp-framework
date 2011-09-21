<?php
	/**
	 * @author Timofey Anisimov <t.anisimov@co.wapstart.ru>
	 * @copyright Copyright (c) 2011, Wapstart
	 */
	class ProtoFormListPrimitive extends BasePrimitive
	{
		private $className = null;

		public static function create($name)
		{
			return new self($name);
		}

		protected static function guessClassName($class)
		{
			if (is_string($class))
				return $class;
			elseif (is_object($class)) {
				if ($class instanceof Identifiable)
					return get_class($class);
				elseif ($class instanceof GenericDAO)
					return $class->getObjectName();
			}

			throw new WrongArgumentException('strange class given - '.$class);
		}

		public function of($class)
		{
			$className = $this->guessClassName($class);

			Assert::classExists($className);

			Assert::isInstance(
				$className,
				'Prototyped',
				"class '{$className}' must implement Prototyped interface"
			);

			$this->className = $className;

			return $this;
		}

		public function import($scope)
		{
			if (!$this->className)
				throw new WrongStateException(
					"no class defined for ProtoFormListPrimitive '{$this->name}'"
				);

			$proto = call_user_func(array($this->className, 'proto'));
			
			if (
				!isset($scope[$this->name])
				|| !is_array($scope[$this->name])
				|| (count($scope[$this->name]) == 0)
			)
			{
				$this->value = null;
				$this->imported = true;
				
				return false;
			}

			$result = array();

			foreach ($scope[$this->name] as $value) {
				$result[] = $proto->makeForm()->import($value);
			}

			$this->value = $result;

			return true;
		}

		public function importValue($value)
		{
			$this->value = $value;
		}
	}
?>

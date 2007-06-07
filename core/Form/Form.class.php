<?php
/****************************************************************************
 *   Copyright (C) 2004-2007 by Konstantin V. Arkhipov, Anton E. Lebedevich *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 *                                                                          *
 ****************************************************************************/
/* $Id$ */

	/**
	 * Complete Form class.
	 * 
	 * @ingroup Form
	 * 
	 * @see http://onphp.org/examples.Form.en.html
	**/
	final class Form extends RegulatedForm
	{
		const WRONG 		= 0x0001;
		const MISSING 		= 0x0002;
		
		private $errors			= array();
		private $labels			= array();
		private $describedLabels 	= array();
		
		/**
		 * @return Form
		**/
		public static function create()
		{
			return new self;
		}
		
		public function getErrors()
		{
			return $this->errors + $this->violated;
		}

		/**
		 * @return Form
		**/
		public function dropAllErrors()
		{
			$this->errors	= array();
			$this->violated	= array();
			
			return $this;
		}

		/**
		 * primitive marking
		**/
		//@{
		/**
		 * @return Form
		**/
		public function markMissing($primitiveName)
		{
			return $this->markCustom($primitiveName, Form::MISSING);
		}
		
		/**
		 * rule or primitive
		 * 
		 * @return Form
		**/
		public function markWrong($name)
		{
			if (
				isset($this->rules[$name])
				|| ($name == $this->get($name)->getName())
			)
				$this->errors[$name] = Form::WRONG;
			
			return $this;
		}

		/**
		 * @return Form
		**/
		public function markGood($primitiveName)
		{
			$prm = $this->get($primitiveName);

			unset($this->errors[$prm->getName()]);
			
			return $this;
		}

		/**
		 * Set's custom error mark for primitive.
		 * 
		 * @return Form
		**/
		public function markCustom($primitiveName, $customMark)
		{
			Assert::isInteger($customMark);
			
			$this->errors[$this->get($primitiveName)->getName()] = $customMark;
			
			return $this;
		}
		//@}
		
		/**
		 * Returns plain list of error's labels
		**/
		public function getTextualErrors()
		{
			$list = array();
			
			foreach (array_keys($this->labels) as $name) {
				if ($label = $this->getTextualErrorFor($name))
					$list[] = $label;
			}
					
			return $list;
		}
		
		public function getTextualErrorFor($name)
		{
			if (
				isset(
					$this->violated[$name],
					$this->labels[$name][$this->violated[$name]]
				)
			)
				return $this->labels[$name][$this->violated[$name]];
			elseif (
				isset(
					$this->errors[$name],
					$this->labels[$name][$this->errors[$name]]
				)
			)
				return $this->labels[$name][$this->errors[$name]];
			else
				return null;
		}
		
		public function getErrorDescriptionFor($name)
		{
			if (
				isset(
					$this->violated[$name],
					$this->describedLabels[$name][$this->violated[$name]]
				)
			)
				return $this->describedLabels[$name][$this->violated[$name]];
			elseif (
				isset(
					$this->errors[$name],
					$this->describedLabels[$name][$this->errors[$name]]
				)
			)
				return $this->describedLabels[$name][$this->errors[$name]];
			else
				return null;
		}
		
		/**
		 * @return Form
		**/
		public function addWrongLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::WRONG, $label);
		}
		
		/**
		 * @return Form
		**/
		public function addMissingLabel($primitiveName, $label)
		{
			return $this->addErrorLabel($primitiveName, Form::MISSING, $label);
		}

		/**
		 * @return Form
		**/
		public function addCustomLabel($primitiveName, $customMark, $label)
		{
			return $this->addErrorLabel($primitiveName, $customMark, $label);
		}
		
		/**
		 * @return Form
		**/
		public function import($scope)
		{
			foreach ($this->primitives as $prm)
				$this->importPrimitive($scope, $prm);
					
			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function importMore($scope)
		{
			foreach ($this->primitives as $prm) {
				if (
					$prm->getValue() === null ||
					($prm instanceof PrimitiveBoolean && !$prm->getValue())
				)
					$this->importPrimitive($scope, $prm);
			}

			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function importOne($primitiveName, $scope)
		{
			return $this->importPrimitive($scope, $this->get($primitiveName));
		}
		
		/**
		 * @return Form
		**/
		public function importValue($primitiveName, $value)
		{
			$prm = $this->get($primitiveName);
			
			return $this->checkImportResult($prm, $prm->importValue($value));
		}
		
		/**
		 * @return Form
		**/
		public function importOneMore($primitiveName, $scope)
		{
			$prm = $this->get($primitiveName);
			
			if (
				$prm->getValue() === null
				|| ($prm instanceof PrimitiveBoolean && !$prm->getValue())
			)
				return $this->importPrimitive($scope, $prm);

			return $this;
		}
		
		public function toFormValue($value)
		{
			if ($value instanceof FormField)
				return $this->getValue($value->getName());
			elseif ($value instanceof LogicalObject)
				return $value->toBoolean($this);
			else
				return $value;
		}
		
		/**
		 * @return Form
		**/
		private function importPrimitive($scope, BasePrimitive $prm)
		{
			return $this->checkImportResult($prm, $prm->import($scope));
		}
		
		/**
		 * @return Form
		**/
		private function checkImportResult(BasePrimitive $prm, $result)
		{
			$name = $prm->getName();
			
			if (null === $result) {
				if ($prm->isRequired())
					$this->errors[$name] = self::MISSING;
			} elseif (true === $result) {
				unset($this->errors[$name]);
			} else
				$this->errors[$name] = self::WRONG;
			
			return $this;
		}
		
		/**
		 * Assigns specific label for given primitive and error type.
		 * One more example of horrible documentation style.
		 *
		 * @param	$name		string	primitive or rule name
		 * @param	$errorType	enum	Form::(WRONG|MISSING)
		 * @param	$label		string	YDFB WTF is this :-) (c) /.
		 * @throws	MissingElementException
		 * @return	Form
		**/
		private function addErrorLabel($name, $errorType, $label)
		{
			if (
				!isset($this->rules[$name])
				&& !$this->get($name)->getName()
			)
				throw new MissingElementException(
					"knows nothing about '{$name}'"
				);

			$this->labels[$name][$errorType] = $label;
			
			return $this;
		}
		
		/**
		 * @return Form
		**/
		public function addErrorDescription($name, $errorType, $description)
		{
			
			if (
				!isset($this->rules[$name])
				&& !$this->get($name)->getName()
			)
				throw new MissingElementException(
					"knows nothing about '{$name}'"
				);
				
			$this->describedLabels[$name][$errorType] = $description;

			return $this;
		}
	}
?>
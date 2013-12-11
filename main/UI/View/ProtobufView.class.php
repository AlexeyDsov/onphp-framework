<?php
/***************************************************************************
 *   Copyright (C) 2013 by Nikita V. Konstantinov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Flow
	 **/
	class ProtobufView implements View, Stringable
	{
		/**
		 * @var HttpStatus
		 */
		protected $httpStatus = null;
		protected $contentType = 'application/octet-stream';

		/**
		 * @return ProtobufView
		 **/
		public static function create()
		{
			return new static;
		}

		/**
		 * @return ProtobufView
		**/
		public function setHttpStatus(HttpStatus $httpStatus)
		{
			$this->httpStatus = $httpStatus;

			return $this;
		}

		/**
		 * @return ProtobufView
		 **/
		public function setContentType($contentType)
		{
			$this->contentType = $contentType;

			return $this;
		}

		/**
		 * @return ProtobufView
		 **/
		public function render(/* Model */ $model = null)
		{
			if ($this->httpStatus)
				header($this->httpStatus->toString());

			$content = $this->toString($model);

			header('Content-type: '.$this->contentType);
			header('Content-length: '.strlen($content));

			echo $content;

			return $this;
		}

		public function toString(/* Model */ $model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);

			if ($model && $model->get('data') instanceof \DrSlump\Protobuf\Message) {
				return $model->get('data')->serialize();
			}
		}
	}
	?>
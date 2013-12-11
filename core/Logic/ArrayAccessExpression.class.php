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
	 * @ingroup Logic
	**/
	final class ArrayAccessExpression implements MappableObject
	{
		private $field	= null;
		private $keys	= array();
		
		/**
		 * @return ArrayAccessExpression
		**/
		public static function create(/* ... */)
		{
			return new self(func_get_args());
		}
		
		public function __construct($args)
		{
			$this->field	= array_shift($args);
			$this->keys		= $args;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			return
				$dialect->toFieldString($this->field)
				.'['.implode('][', $this->keys).']';
		}
		
		/**
		 * @return ArrayAccessExpression
		**/
		public function toMapped(ProtoDAO $dao, JoinCapableQuery $query)
		{
			return new self(
				array_merge(
					array(
						$dao->guessAtom($this->field, $query)
					),
					$this->keys
				)
			);
		}
	}
?>
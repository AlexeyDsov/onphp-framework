<?php
/***************************************************************************
 *   Copyright (C) 2013 by Andrey Ryaguzov                                 *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	final class RouterStaticArrayRule extends RouterBaseRule
	{
		protected $routes;

		public static function create(array $routes = array())
		{
			return new self($routes);
		}

		public function __construct(array $routes)
		{
			$this->routes = $routes;
		}

		public function match(HttpRequest $request)
		{
			$result = false;
			$path = rtrim($this->processPath($request)->toString(), '/');

			if (array_key_exists($path, $this->routes)) {
				$result = $this->routes[$path];
			}

			return array('controller' => $result);
		}

		public function assembly(
			array $data = array(),
			$reset = false,
			$encode = false
		)
		{
			return null;
		}
	}
?>
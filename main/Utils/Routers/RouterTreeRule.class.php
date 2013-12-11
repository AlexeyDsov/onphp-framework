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

	final class RouterTreeRule extends RouterBaseRule
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
			$uriParts = explode(
				'/',
				rtrim($this->processPath($request)->toString(), '/')
			);

			list($controller, $uriParts) = $this->findController(
				$uriParts,
				$this->routes
			);

			$additionals = array();
			$partsCount = count($uriParts);
			for($i = 0; $i < $partsCount; $i++) {
				if (isset($uriParts[$i + 1])) {
					$additionals[$uriParts[$i]] = $uriParts[$i + 1];
				}

				$i += 1;
			}

			return array(
				'controller' => $controller,
				'additionals' => $additionals
			);
		}

		protected function findController($uriParts, $routes)
		{
			$part = array_shift($uriParts);

			if (!isset($routes[$part])) {
				return false;
			}

			if (is_array($routes[$part])) {
				return $this->findController($uriParts, $routes[$part]);
			} elseif (is_string($routes[$part])) {
				return array($routes[$part], $uriParts);
			} else {
				throw new RouterException('Invalid routes config');
			}
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
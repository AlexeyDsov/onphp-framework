<?php

/* * *************************************************************************
 *   Copyright (C) 2012 by Alexey Denisov                                  *
 *   alexeydsov@gmail.com                                                  *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 * ************************************************************************* */

namespace Onphp\NsConverter\EntitieProto;

use \Onphp\NsConverter\Test\TestCase;

/**
 * @group ce
 */
class ConverterEntityTest extends TestCase
{
	/**
	 * @group ce
	 */
	public function testSimple()
	{
		$scope = $this->getScope();

		$form = ConverterEntity::me()->makeForm();
		$form->import($scope);
		ConverterEntity::me()->validate(null, $form);
		var_dump($form->export());
		exit;
	}

	/**
	 * @return array
	 */
	private function getScope()
	{
		return array(
			'confdir' => '/tmp/converter/',
			'pathes' => array(
				array(
					'action' => 'scan',
					'path' => '../core/',
					'psr0' => null,
				),
				array(
					'action' => 'replace',
					'path' => '../main',
					'namespace' => 'onPHP',
				),
			),
		);
	}
}
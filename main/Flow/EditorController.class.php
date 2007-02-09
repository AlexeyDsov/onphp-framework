<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */
	
	/**
	 * @ingroup Flow
	**/
	abstract class EditorController extends BaseEditor
	{
		public function __construct(Prototyped $subject)
		{
			$this->commandMap['drop'] = new DropCommand();
			$this->commandMap['save'] = new SaveCommand();
			$this->commandMap['edit'] = new EditCommand();
			$this->commandMap['add'] = new AddCommand();
			
			parent::__construct($subject);
		}
		
		/**
		 * @return ModelAndView
		**/
		public function handleRequest(HttpRequest $request)
		{
			$this->map->import($request);
			
			$form = $this->map->getForm();
			
			if ($command = $form->getValue('action')) {
				$mav = $this->commandMap[$command]->run(
					$this->subject, $form, $request
				);
			} else
				$mav = ModelAndView::create();
			
			return parent::postHandleRequest($mav, $request);
		}
	}
?>
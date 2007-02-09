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
	class SaveCommand extends TakeCommand 
	{
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			if (!$form->getErrors() {
				$subject = $form->getValue('id');
				
				return parent::run($subject, $form, $request);
			}
			
			return new ModelAndView();
		}
	}
?>
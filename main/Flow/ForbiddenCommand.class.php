<?php
/***************************************************************************
 *   Copyright (C) 2006-2007 by Konstantin V. Arkhipov                     *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	final class ForbiddenCommand implements EditorCommand
	{
		public function run(Prototyped $subject, Form $form, HttpRequest $request)
		{
			return
				ModelAndView::create()->setView(
					EditorController::COMMAND_FAILED
				);
		}
	}
?>
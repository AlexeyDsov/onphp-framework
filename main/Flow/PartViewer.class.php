<?php
/***************************************************************************
 *   Copyright (C) 2006 by Anton E. Lebedevich                             *
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
	class PartViewer
	{
		protected $viewResolver 	= null;
		protected $model			= null;
		
		public function __construct(ViewResolver $resolver, $model = null)
		{
			$this->viewResolver = $resolver;
			$this->model = $model;
		}
		
		/**
		 * @return PartViewer
		**/
		public function view($partName, $model = null)
		{
			Assert::isTrue($model === null || $model instanceof Model);
			
			// use model from outer template if none specified
			if ($model === null)
				$model = $this->model;
				
			$this->viewResolver->resolveViewName($partName)->render($model);
			
			return $this;
		}
		
		public function partExists($partName)
		{
			return $this->viewResolver->viewExists($partName);
		}
	}
?>
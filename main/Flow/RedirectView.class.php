<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
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
	class RedirectView implements View
	{
		protected $url = null;
		
		public function __construct($url)
		{
			$this->url = $url;
		}
		
		public function render($model = null)
		{
			$postfix = null;
			
			if ($model && $model->getList()) {
				$qs = array();
				
				foreach ($model->getList() as $key => $val) {
					$qs[] = "{$key}={$val}";
				}
			
				if (strpos($this->getUrl(), '?') === false)
					$first = '?';
				else
					$first = '&';
					
				if ($qs)
					$postfix = $first.implode('&', $qs);
			}
			
			HeaderUtils::redirectRaw($this->getUrl().$postfix);
		}
		
		protected function getUrl()
		{
			return $this->url;
		}
	}
?>
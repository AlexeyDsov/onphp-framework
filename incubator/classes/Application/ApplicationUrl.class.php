<?php
/***************************************************************************
 *   Copyright (C) 2007 by Ivan Y. Khvostishkov                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	class ApplicationUrl
	{
		private $scheme			= 'http';
		private $domain			= null;
		private $path			= '/';

		private $queryString	= null;
		
		/**
		 * @return ApplicationUrl
		**/
		public static function create()
		{
			return new self;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function http()
		{
			$this->scheme = 'http';

			return $this;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function https()
		{
			$this->scheme = 'https';

			return $this;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setScheme($scheme)
		{
			$this->scheme = $scheme;

			return $this;
		}

		public function getScheme()
		{
			return $this->scheme;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setDomain($domain)
		{
			$this->domain = $domain;
			
			return $this;
		}

		public function getDomain($level = null)
		{
			if (!$level)
				return $this->domain;

			$domainParts = explode('.', $this->domain);

			while (count($domainParts) > $level)
				$domainParts = array_shift($domainParts);

			return implode('.', $domainParts);
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setQueryString($queryString)
		{
			$this->queryString = $queryString;
			
			return $this;
		}

		public function getQueryString()
		{
			return $this->queryString;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setPath($path)
		{
			if (substr($path, 1, 1) !== DIRECTORY_SEPARATOR)
				$path = DIRECTORY_SEPARATOR.$path;

			if (substr($path, -1, 1) !== DIRECTORY_SEPARATOR)
				throw new WrongArgumentException('path must end in / (slash)');

			$this->path = $path;
			
			return $this;
		}

		public function getPath()
		{
			return $this->path;
		}
		
		/**
		 * @return ApplicationUrl
		**/
		public function setUrl($url)
		{
			Assert::isString($url);

			$info = parse_url($url);

			if ($info === false)
				throw new WrongArgumentException('seriously malformed url');

			if (!isset($info['host']))
				throw new WrongArgumentException('domain must be specified');

			$notAllowedParts =
				array('port', 'user', 'pass', 'fragment');

			foreach ($notAllowedParts as $notAllowedPart) {
				if (isset($info[$notAllowedPart]))
					throw
						new WrongArgumentException(
							"not allowed url part: $notAllowedPart"
						);
			}

			if (isset($info['scheme']))
				$this->setScheme($info['scheme']);

			$this->setDomain($info['host']);

			if (isset($info['path']))
				$this->setPath($info['path']);

			if (isset($info['query']))
				$this->setQueryString($info['query']);
			
			return $this;
		}

		public function getBaseUrl()
		{
			return $this->scheme.'://'.$this->domain.$this->path;
		}

		public function getUrl()
		{
			$result = $this->getBaseUrl();

			if ($this->queryString)
				$result .= '?'.$this->queryString;

			return $result;
		}
	}
?>
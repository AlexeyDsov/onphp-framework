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

	/**
	 * see RFC 3986, 2396
	 *
	 * TODO: base url and referense url resolving, normalization and
	 * urls comparsion
	**/
	class GenericUri
	{
		private $scheme		= null;
		
		private $userInfo	= null;
		private $host		= null;
		private $port		= null;
		
		private $path		= null;
		private $query		= null;
		private $fragment	= null;
		
		private $unreserved	= 'a-z0-9-._~';
		private $pctEncoded	= '%[0-9a-f][0-9a-f]';
		private $subDelims	= '!$&\'()*+,;=';
		
		/**
		 * @return GenericUri
		 *
		 * NOTE: html compatible mode treats _http_ relative urls in browsers' manner.
		 * 
		 * Check this out (all URIs are syntaxically valid):
		 * //localhost.com
		 * //localhost.com:8080
		 * http:/uri
		 * http:uri
		 * mailto://spam@localhost.com
		 * (last is not a http url, try using getSchemeSpecificPart() for it)
		**/
		public static function parse($uri, $htmlCompatible = false)
		{
			$result = new self;
			
			$schemePattern = '([^:/?#]+):';
			$hierPattern = '(//([^/?#]*))';
			
			if (!$htmlCompatible)
				$schemeHierPattern = "($schemePattern)?$hierPattern?";
			else
				$schemeHierPattern = "({$schemePattern}$hierPattern)?";
			
			$pattern = "~^$schemeHierPattern([^?#]*)(\?([^#]*))?(#(.*))?~";
			
			if (!preg_match($pattern, $uri, $matches))
				throw new WrongArgumentException('not well-formed URI');
			
			if ($matches[1])
				$result->scheme = $matches[2];
			
			if ($matches[3]) {
				$authorityPattern = '~^(([^@]+)@)?((\[.+\])|([^:]+))(:(.*))?$~';
				
				if (
					!preg_match(
						$authorityPattern, $matches[4], $authorityMatches
					)
				)
					throw new WrongArgumentException(
						'not well-formed authority part'
					);
				
				if ($authorityMatches[1])
					$result->userInfo = $authorityMatches[2];
				
				$result->host = $authorityMatches[3];
				
				if (!empty($authorityMatches[6]))
					$result->port = $authorityMatches[7];
			}
			
			$result->path = $matches[5];
			
			if (!empty($matches[6]))
				$result->query = $matches[7];
			
			if (!empty($matches[8]))
				$result->fragment = $matches[9];
			
			return $result;
		}
		
		/**
		 * @return GenericUri
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
		 * @return GenericUri
		**/
		public function setUserInfo($userInfo)
		{
			$this->userinfo = $userInfo;
			
			return $this;
		}
		
		public function getUserInfo()
		{
			return $this->userInfo;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setHost($host)
		{
			$this->host = $host;
			
			return $this;
		}
		
		public function getHost()
		{
			return $this->host;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setPort($port)
		{
			$this->port = $port;
			
			return $this;
		}
		
		public function getPort()
		{
			return $this->port;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setPath($path)
		{
			$this->path = $path;
			
			return $this;
		}
		
		public function getPath()
		{
			return $this->path;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setQuery($query)
		{
			$this->query = $query;
			
			return $this;
		}
		
		public function getQuery()
		{
			return $this->query;
		}
		
		/**
		 * @return GenericUri
		**/
		public function setFragment($fragment)
		{
			$this->fragment = $fragment;
			
			return $this;
		}
		
		public function getFragment()
		{
			return $this->fragment;
		}

		public function getAuthority()
		{
			$result = null;
			
			if ($this->userInfo !== null)
				$result .= $this->userInfo.'@';
			
			if ($this->host !== null)
				$result .= $this->host;
				
			if ($this->port !== null)
				$result .= ':'.$this->port;
			
			return $result;
		}
		
		public function getSchemeSpecificPart()
		{
			$result = null;
			
			$authority = $this->getAuthority();
			
			if ($authority !== null)
				$result .= '//'.$authority;
			
			$result .= $this->path;
			
			if ($this->query !== null)
				$result .= '?'.$this->query;
			
			if ($this->fragment !== null)
				$result .= '#'.$this->fragment;
			
			return $result;
		}
		
		public function toString()
		{
			$result = null;
			
			if ($this->scheme !== null)
				$result .= $this->scheme.':';
			
			$result .= $this->getSchemeSpecificPart();
			
			return $result;
		}
		
		public function isValid()
		{
			return
				$this->isValidScheme()
				&& $this->isValidUserInfo()
				&& $this->isValidHost()
				&& $this->isValidPath()
				&& $this->isValidQuery()
				&& $this->isValidFragment();
		}
		
		public function isValidScheme()
		{
			return (
				$this->scheme === null
				|| preg_match('~^[a-z][-+.a-z0-9]*$~i', $this->scheme) == 1
			);
		}
		
		public function isValidUserInfo()
		{
			$charPattern = $this->charPattern(':');
			
			return (preg_match("/^$charPattern*$/i", $this->scheme) == 1);
		}
		
		public function isValidHost()
		{
			if (empty($this->host))
				return true;
			
			$decOctet = 
				'(\d)|'			// 0-9
				.'([1-9]\d)|'	// 10-99
				.'(1\d\d)|'		// 100-199
				.'(2[0-4]\d)|'	// 200-249
				.'(25[0-5])';	// 250-255
			
			$ipV4Address = "($decOctet)\.($decOctet)\.($decOctet)\.($decOctet)";
			
			$hexdig = '[0-9a-f]';
			
			$h16 = "$hexdig{1,4}";
			$ls32 = "(($h16:$h16)|($ipV4Address))";
			
			$ipV6Address =
				"  (                        ($h16:){6} $ls32)"
				."|(                      ::($h16:){5} $ls32)"
				."|(              ($h16)? ::($h16:){4} $ls32)"
				."|( (($h16:){0,1} $h16)? ::($h16:){3} $ls32)"
				."|( (($h16:){0,2} $h16)? ::($h16:){2} $ls32)"
				."|( (($h16:){0,3} $h16)? :: $h16:     $ls32)"
				."|( (($h16:){0,4} $h16)? ::           $ls32)"
				."|( (($h16:){0,5} $h16)? ::           $h16 )"
				."|( (($h16:){0,6} $h16)? ::                )";
			
			$ipVFutureAddress =
				"v$hexdig+\.[{$this->unreserved}{$this->subDelims}:]+";
			
			if (
				preg_match(
					"/^\[(($ipV6Address)|($ipVFutureAddress))\]$/ix",
					$this->host
				)
			)
				return true;
			
			if (preg_match("/^$ipV4Address$/i", $this->host)) {
				return true;
			}
			
			$charPattern = $this->charPattern(null);
			
			// NOTE: no back compatibility with rfc 3986!
			// using top label from rfc 2396, in order to detect bad ip
			// address ranges like 666.666.666.666
			
			$topLabelPattern = '(([a-z])|([a-z]([a-z0-9-])*[a-z0-9]))\.?';
			
			return (
				preg_match(
					"/^($charPattern*\.)?{$topLabelPattern}$/i",
					$this->host
				) == 1
			);
		}
		
		public function isValidPort()
		{
			return (preg_match('~^\d*$~', $this->port) == 1);
		}
		
		public function isValidPath()
		{
			$charPattern = $this->charPattern(':@');
			
			return (
				preg_match(
					"/^($charPattern+)?"
					."(\/$charPattern*)*$/i",
					$this->path
				) == 1
			);
		}
		
		public function isValidQuery()
		{
			return $this->isValidFragmentOrQuery($this->query);
		}
		
		public function isValidFragment()
		{
			return $this->isValidFragmentOrQuery($this->fragment);
		}
		
		private function isValidFragmentOrQuery($string)
		{
			$charPattern = $this->charPattern(':@\/?');
			
			return (preg_match("/^$charPattern*$/i", $string) == 1);
		}
		
		private function charPattern($extraChars = null)
		{
			return
				"(([{$this->unreserved}{$this->subDelims}$extraChars])"
				."|({$this->pctEncoded}))";
		}
	}
?>
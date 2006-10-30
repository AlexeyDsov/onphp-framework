<?php
/***************************************************************************
 *   Copyright (C) 2006 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 *   Based on PEAR's Mail::MIME by Richard Heyes                           *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Mail
	**/
	final class MimePart implements MailBuilder
	{
		private $contentId		= null;
		private $contentType	= null;
		private $boundary		= null;
		
		private $encoding		= null;
		private $charset		= null;
		
		private $filename		= null;
		private $description	= null;
		
		private $body			= null;
		
		private $inline			= false;
		
		// sub-parts aka childrens
		private $parts			= array();
		
		public static function create()
		{
			return new self;
		}
		
		public function __construct()
		{
			// useful defaults
			
			$this->encoding		= MailEncoding::seven();
			$this->contentType	= 'text/plain';
		}
		
		public function setBoundary($boundary)
		{
			$this->boundary = $boundary;
			
			return $this;
		}
		
		public function getBoundary()
		{
			return $this->boundary;
		}
		
		public function getContentId()
		{
			return $this->contentId;
		}
		
		public function setContentId($id)
		{
			$this->contentId = $id;
			
			return $this;
		}
		
		public function getContentType()
		{
			return $this->contentType;
		}
		
		public function setContentType($type)
		{
			$this->contentType = $type;
			
			return $this;
		}
		
		public function getEncoding()
		{
			return $this->encoding;
		}
		
		public function setEncoding(MailEncoding $encoding)
		{
			$this->encoding = $encoding;
			
			return $this;
		}
		
		public function getCharset()
		{
			return $this->charset;
		}
		
		public function setCharset($charset)
		{
			$this->charset = $charset;
			
			return $this;
		}
		
		public function getFilename()
		{
			return $this->filename;
		}
		
		public function setFilename($name)
		{
			$this->filename = $name;
			
			return $this;
		}
		
		public function getDescription()
		{
			return $this->description;
		}
		
		public function setDescription($description)
		{
			$this->description = $description;
			
			return $this;
		}
		
		public function loadBodyFromFile($path)
		{
			Assert::isTrue(is_readable($path));
			
			$this->body = file_get_contents($path);
			
			return $this;
		}
		
		public function setBody($body)
		{
			$this->body = $body;
			
			return $this;
		}
		
		public function getBody()
		{
			return $this->body;
		}
		
		public function addSubPart(MimePart $part)
		{
			$this->parts[] = $part;
			
			return $this;
		}
		
		public function setInline($inline = true)
		{
			$this->inline = $inline;
			
			return $this;
		}
		
		public function getEncodedBody()
		{
			$body = null;
			
			switch ($this->encoding->getId()) {
				case MailEncoding::SEVEN_BITS:
				case MailEncoding::EIGHT_BITS:
					
					$body = "{$this->body}";
					break;
				
				/**
				 * quoted-printable encoding voodoo by <bendi at interia dot pl>
				 * 
				 * @see http://www.php.net/quoted_printable_decode
				**/
				case MailEncoding::QUOTED:
					
					$string =
						preg_replace(
							'/[^\x21-\x3C\x3E-\x7E\x09\x20]/e',
							'sprintf("=%02x", ord ("$0"));',
							$this->body
						);
					
					$matches = array();
					
					preg_match_all('/.{1,73}([^=]{0,3})?/', $body, $matches);
					
					$body = implode("=\r\n", $matches[0]);
				
					break;
					
				case MailEncoding::BASE64:
					
					$body =
						rtrim(
							chunk_split(
								base64_encode($this->body),
								76,
								"\r\n"
							)
						);
					
					break;
					
				default:
					throw new WrongStateException('unknown mail encoding given');
			}
					
			return $body;
		}
		
		public function getHeaders()
		{
			$headers = array();
			
			if ($this->contentType) {
				$header =
					"Content-Type: {$this->contentType};";
				
				if ($this->charset)
					$header .= " charset=\"{$this->charset}\"";
				
				if ($this->boundary)
					$header .= "\r\n\tboundary=\"{$this->boundary}\"";
				
				$headers[] = $header;
			}
			
			$headers[] = "Content-Transfer-Encoding: {$this->encoding->toString()}";
			
			if ($this->contentId)
				$headers[] = "Content-ID: <{$this->contentId}>";
			
			if (!$this->inline && $this->filename)
				$headers[] =
					"Content-Disposition: attachment; "
					."filename=\"{$this->filename}\"";
			elseif ($this->inline)
				$headers = 'Content-Disposition: inline';
			
			if ($this->description)
				$headers[] = "Content-Description: {$this->description}";
			
			return implode("\r\n", $headers);
		}
	}
?>
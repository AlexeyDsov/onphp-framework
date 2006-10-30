<?php
/***************************************************************************
 *   Copyright (C) 2004-2006 by Anton E. Lebedevich                        *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup Exceptions
	**/
	class MailException extends BaseException {/*_*/};
	
	/**
	 * @ingroup Exceptions
	**/
	class MailNotSentException extends MailException {/*_*/};
	
	/**
	 * @ingroup Mail
	**/
	class Mail
	{
		private $to				= null;
		private $cc				= null;
		private $text			= null;
		private $subject		= null;
		private $from			= null;
		private $encoding		= null;
		private $contentType	= null;
		
		public static function create()
		{
			return new self();
		}

		public function send()
		{
			if ($this->to == null)
				throw new WrongArgumentException("mail to: is not specified");
				
			$siteEncoding = mb_get_info('internal_encoding');
			
			if (!$this->encoding 
				|| $this->encoding == $siteEncoding
			) {
				$encoding = $siteEncoding;
				$to = $this->to;
				$from = $this->from;
				$subject =
					 "=?".$encoding."?B?"
					 .base64_encode($this->subject)
					 ."?=";
				$body = $this->text;
				
			} else {
				$encoding = $this->encoding;
				$to = mb_convert_encoding($this->to, $encoding);
				$from = mb_convert_encoding($this->from, $encoding);
	
				$subject =
					 "=?".$encoding."?B?"
					 .base64_encode(
					 	iconv(
					 		$siteEncoding,
					 		$encoding.'//TRANSLIT',
					 		$this->subject
					 	)
					 )."?=";
					 
				$body = iconv(
					$siteEncoding,
					$encoding.'//TRANSLIT', 
					$this->text
				);
			}

			$headers = null;
			
			if ($this->from != null) {
				$headers .= "From: ".$this->from."\n";
				$headers .= "Return-Path: ".$this->from."\n";
			}
			
			if ($this->cc != null)
				$headers .= "Cc: ".$this->cc."\n";
			
			if ($this->contentType === null)
				$this->contentType = 'text/plain';

			$headers .= "Content-type: ".$this->contentType
				."; charset=".$encoding."\n";
			$headers .= "Content-Transfer-Encoding: 8bit\n";
			$headers .= "Date: ".date('r')."\n";

			if (!mail($to, $subject, $body, $headers))
				throw new MailNotSentException();
				
			return $this;
		}
		
		public function setTo($to)
		{
			$this->to = $to;
			return $this;
		}
		
		public function setCc($cc)
		{
			$this->cc = $cc;
			return $this;
		}		

		public function setSubject($subject)
		{
			$this->subject = $subject;
			return $this;
		}
		
		public function setText($text)
		{
			$this->text = $text;
			return $this;
		}
		
		public function setFrom($from)
		{
			$this->from = $from;
			return $this;
		}
		
		public function setEncoding($encoding)
		{
			$this->encoding = $encoding;
			return $this;
		}
		
		public function getContentType()
		{
			return $this->contentType;
		}
		
		public function setContentType($contentType)
		{
			$this->contentType = $contentType;
			return $this;
		}
	}
?>
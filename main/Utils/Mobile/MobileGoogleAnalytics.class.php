<?php
/***************************************************************************
 *   Copyright (C) 2011 by Evgeny V. Kokovikhin                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU Lesser General Public License as        *
 *   published by the Free Software Foundation; either version 3 of the    *
 *   License, or (at your option) any later version.                       *
 *                                                                         *
 ***************************************************************************/

	/**
	 * @ingroup Utils
	**/
	final class MobileGoogleAnalytics extends Singleton
	{
		const VERSION		= '4.4sh';
		const COOKIE_NAME	= '__utmmobile';
		const COOKIE_LIFE_TIME = 63072000; //two years
		
		private $utmGifLocation = "http://www.google-analytics.com/__utm.gif";
		
		private $accountId	= null;
		private $imageUrl	= null;
		
		/**
		 * @return MobileGoogleAnalytics
		 */
		public static function me()
		{
			return Singleton::getInstance(__CLASS__);
		}
		
		public function setAccountId($id)
		{
			$this->accountId = $id;
			
			return $this;
		}
		
		public function getAccountId()
		{
			return $this->accountId;
		}
		
		public function setImageBaseUrl(HttpUrl $url)
		{
			$this->imageUrl = $url;
			
			return $this;
		}
		
		/**
		 * @return HttpUrl
		 */
		public function getImageBaseUrl()
		{
			return
				$this->imageUrl
					? $this->imageUrl
					: HttpUrl::create()->setPath('/ga.php');
		}
		
		public function getImageUrl(HttpRequest $request)
		{
			$url =
				$this->getImageBaseUrl()->
				appendQuery('utmac='.$this->getAccountId())->
				appendQuery('guid=ON')->
				appendQuery('utmn='.$this->getRandomForGoogle());
			
			if ($request->hasServerVar('HTTP_REFERER'))
				$url->appendQuery(
					'utmr='.$request->getServerVar('HTTP_REFERER')
				);
			
			if ($request->hasServerVar('HTTP_REFERER'))
				$url->appendQuery(
					'utmp='.urlencode($request->getServerVar('REQUEST_URI'))
				);
			
			return $url;
		}
		
		public function showImage(HttpRequest $request)
		{
			$this->sendRequest($request);
			
			$this->writeGifData();
		}
		
		private function getGifData()
		{
			return array(
			  chr(0x47), chr(0x49), chr(0x46), chr(0x38), chr(0x39), chr(0x61),
			  chr(0x01), chr(0x00), chr(0x01), chr(0x00), chr(0x80), chr(0xff),
			  chr(0x00), chr(0xff), chr(0xff), chr(0xff), chr(0x00), chr(0x00),
			  chr(0x00), chr(0x2c), chr(0x00), chr(0x00), chr(0x00), chr(0x00),
			  chr(0x01), chr(0x00), chr(0x01), chr(0x00), chr(0x00), chr(0x02),
			  chr(0x02), chr(0x44), chr(0x01), chr(0x00), chr(0x3b)
			);
		}
		
		private static function cropIp(IpAddress $ip)
		{
			return
				IpAddress::create(
					$ip->getLongIp()
					& IpAddress::create('255.255.255.0')->getLongIp()
				);
		}
		
		private function getUniqueVisitorId($account, $userAgent, $cookie)
		{
			// If there is a value in the cookie, don't change it.
			if (!empty($cookie)) {
				return $cookie;
			}

			$message = $userAgent . uniqid($this->getRandomForGoogle(), true);
			$md5String = md5($message);

			return "0x" . substr($md5String, 0, 16);
		}
		
		/*void*/ private function writeGifData()
		{
			header("Content-Type: image/gif");
			
			HeaderUtils::sendNotCachedHeader();
			
			echo join($this->getGifData());
		}
		
		private function sendRequest(HttpRequest $request)
		{
			$context = array(
				"http" => array(
					"method" => "GET",
					"user_agent" =>
						$request->hasServerVar('HTTP_USER_AGENT')
							? $request->getServerVar('HTTP_USER_AGENT')
							: null,
					"header" => (
						"Accepts-Language: "
						.(
							$request->hasServerVar('HTTP_ACCEPT_LANGUAGE')
								? $request->getServerVar('HTTP_ACCEPT_LANGUAGE')
								: null
						)
					)
				)
			);
			
			try {
				$data =
					@file_get_contents(
						$this->getRequestUrl($request),
						false,
						stream_context_create($context)
					);
				
			} catch (BaseException $e) {/**/}
			
			return $this;
		}
		
		private function getRequestUrl(HttpRequest $request)
		{
			$timeStamp = time();
			$domainName =
				$request->hasServerVar('SERVER_NAME')
					? $request->getServerVar('SERVER_NAME')
					: '';
			
			// Get the referrer from the utmr parameter, this is the referrer to the
			// page that contains the tracking pixel, not the referrer for tracking
			// pixel.
			$documentReferer =
				$request->hasGetVar('utmr')
					? urlencode($request->getGetVar('utmr'))
					: '-';
			
			$documentPath =
				$request->hasGetVar('utmp')
					? urlencode($request->getGetVar('utmp'))
					: '-';
			
			$account =
			$request->hasGetVar('utmac')
					? urlencode($request->getGetVar('utmac'))
					: '-';
			
			$userAgent =
				$request->hasServerVar("HTTP_USER_AGENT")
					? $request->getServerVar("HTTP_USER_AGENT")
					: '';
			
			// Try and get visitor cookie from the request.
			$cookie =
				$request->hasCookieVar(self::COOKIE_NAME)
					? $request->getCookieVar(self::COOKIE_NAME)
					: '';

			$visitorId = $this->getUniqueVisitorId($account, $userAgent, $cookie);

			// Always try and add the cookie to the response.
			try {
				Cookie::create(self::COOKIE_NAME)->
					setValue($visitorId)->
					setMaxAge(self::COOKIE_LIFE_TIME)->
					setPath('/')->
					httpSet();
			} catch (BaseException $e) {/*oops*/}
			
			// Construct the gif hit url.
			$utmUrl =
				$this->utmGifLocation."?"
				."utmwv=".self::VERSION
				."&utmn=".$this->getRandomForGoogle()
				."&utmhn=".urlencode($domainName)
				."&utmr=".urlencode($documentReferer)
				."&utmp=".urlencode($documentPath)
				."&utmac=".$account
				."&utmcc=__utma%3D999.999.999.999.999.1%3B"
				."&utmvid=".$visitorId
				."&utmip="
				.$this->cropIp(
					IpAddress::create(
						$request->getServerVar("REMOTE_ADDR")
					)
				)->
				toString();

			return $utmUrl;
		}

		private function getRandomForGoogle()
		{
			return rand(0, 0x7fffffff);
		}
	}
?>

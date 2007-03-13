<?php
/***************************************************************************
 *   Copyright (C) 2007 by Konstantin V. Arkhipov                          *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/
/* $Id$ */

	/**
	 * @ingroup DAOs
	**/
	final class MessageSegmentHandler implements SegmentHandler
	{
		private $id = null;
		
		public function __construct($segmentId)
		{
			$this->id = $segmentId;
		}
		
		public function touch($key)
		{
			try {
				$q = msg_get_queue($this->id, 0660);
			} catch (BaseException $e) {
				// race
				return false;
			}
			
			try {
				return msg_send($q, $key, 1, false, false);
			} catch (BaseException $e) {
				// queue is full
				$msg = $type = null;

				msg_receive($q, -(PHP_INT_MAX), $type, 2, $msg, false);

				return msg_send($q, $key, 1, false, false);
			}
		}
		
		public function unlink($key)
		{
			try {
				$q = msg_get_queue($this->id, 0660);
			} catch (BaseException $e) {
				// race
				return false;
			}
			
			$type = $msg = null;
			
			return msg_receive($q, $key, $type, 2, $msg, false, MSG_IPC_NOWAIT);
		}
		
		public function ping($key)
		{
			try {
				$q = msg_get_queue($this->id, 0660);
			} catch (BaseException $e) {
				// race
				return false;
			}
			
			$type = $msg = null;
			
			// YANETUT
			if (msg_receive($q, $key, $type, 2, $msg, false, MSG_IPC_NOWAIT)) {
				msg_send($q, $key, 1, false, false);
				return true;
			}
			
			return false;
		}
		
		public function drop()
		{
			try {
				return msg_remove_queue(msg_get_queue($this->id, 0660));
			} catch (BaseException $e) {
				// removed in race
				return true;
			}
		}
	}
?>
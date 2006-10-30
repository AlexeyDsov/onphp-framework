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
	 * @ingroup OSQL
	**/
	final class TruncateQuery extends QueryIdentification
	{
		private $targets = array();
		
		public function __construct($whom = null)
		{
			if ($whom) {
				if (is_array($whom))
					$this->targets = $whom;
				else
					$this->targets[] = $whom;
			}
		}
		
		public function getId()
		{
			throw new UnsupportedMethodException();
		}
		
		public function table($table)
		{
			if ($table instanceof SQLTableName)
				$this->targets[] = $table->getTable();
			else
				$this->tables[] = $table;
			
			return $this;
		}
		
		public function toDialectString(Dialect $dialect)
		{
			Assert::isTrue(
				($count = count($this->targets)) > 0,
				'do not know who should i truncate'
			);

			if ($dialect->hasTruncate()) {
				$head = 'TRUNCATE TABLE ';
			} else {
				$head = 'DELETE FROM ';
			}
			
			if ($dialect->hasMultipleTruncate()) {
				$query = $head.$this->dumpTargets($dialect, null, ',');
			} else {
				$query = $this->dumpTargets($dialect, $head, ';');
			}
			
			return $query.';';
		}
		
		private function dumpTargets(
			Dialect $dialect, $prepend = null, $append = null
		)
		{
			if (count($this->targets) == 1) {
				return $prepend.$dialect->quoteTable(reset($this->targets));
			} else {
				$tables = array();
				
				foreach ($this->targets as $target) {
					if ($target instanceof DialectString)
						$table =
							$dialect->quoteTable(
								$target->toDialectString($dialect)
							);
					else
						$table = $dialect->quoteTable($target);
					
					$tables[] = $prepend.$table;
				}
				
				return implode($append.' ', $tables);
			}
			
			/* NOTREACHED */
		}
	}
?>
<?php
/****************************************************************************
 *   Copyright (C) 2013 by Andrey Ryaguzov                                  *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU Lesser General Public License as         *
 *   published by the Free Software Foundation; either version 3 of the     *
 *   License, or (at your option) any later version.                        *
 *                                                                          *
 ****************************************************************************/

	/**
	 * @ingroup OSQL
	 */
	final class BulkInsertQuery extends InsertQuery
	{
		/**
		 * Rows
		 *
		 * @var array
		 */
		protected $rows = array();

		/**
		 * @return array
		 */
		public function getRows()
		{
			return $this->rows;
		}

		/**
		 * @param array $rows
		 * @return $this
		 */
		public function setRows(array $rows)
		{
			$this->rows = $rows;

			return $this;
		}

		/**
		 * @see InsertQuery::toDialectStringValues()
		 */
		protected function toDialectStringValues($query, Dialect $dialect)
		{
			if (empty($this->rows)) {
				return parent::toDialectStringValues($query, $dialect);
			}

			$rows = $this->rows;
			// insert first element through InsertQuery
			$row = array_shift($rows);
			$this->arraySet($row);
			$query = parent::toDialectStringValues($query, $dialect);

			foreach($rows as $row) {
				$query .= ', (';
				$value = array_shift($row);
				$query .= $this->toDialectStringValue($value, $dialect);

				foreach($row as $value) {
					$query .= ', ' . $this->toDialectStringValue($value, $dialect);
				}

				$query .= ')';
			}

			return $query;
		}
	}
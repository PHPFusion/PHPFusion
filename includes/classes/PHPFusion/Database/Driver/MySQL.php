<?php
/*-------------------------------------------------------
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
 --------------------------------------------------------
| Filename: MySQL.php
| Author: Takács Ákos (Rimelek)
 --------------------------------------------------------
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
 --------------------------------------------------------*/

namespace PHPFusion\Database\Driver;


use PHPFusion\Database\Exception\ConnectionException;
use PHPFusion\Database\Exception\SelectionException;
use PHPFusion\Database\AbstractDatabaseDriver;

class MySQL extends AbstractDatabaseDriver {
	/**
	 * @var resource
	 */
	private $connection = NULL;

	/**
	 * Connect to the database
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $pass
	 * @param string $db
	 * @throws SelectionException
	 * @throws ConnectionException
	 */
	public function __construct($host, $user, $pass, $db) {
		$this->connection = @mysql_connect($host, $user, $pass);
		if (!$this->connection) {
			throw new ConnectionException(mysql_error(), mysql_errno());
		}
		mysql_set_charset('utf8', $this->connection);

		if(!@mysql_select_db($db, $this->connection)) {
			throw new SelectionException(mysql_error($this->connection), mysql_errno($this->connection));
		}
	}

	/**
	 * Send a database query
	 *
	 * This method will be called from AbstractDatabase::query()
	 * AbstractDatabase::query() will log the queries and check the
	 * execution time.
	 *
	 * @param string $query SQL
	 * @param array $parameters
	 * @return mixed The result of the query or FALSE on error
	 */
	protected function _query($query, array $parameters = array()) {
		if ($parameters) {
			foreach ($parameters as $k => $parameter) {
				$parameters[$k] = $this->quote($parameter);
			}
			$query = strtr($query, $parameters);
		}
		$result = mysql_query($query, $this->connection); echo mysql_error($this->connection);
		return $result ? : FALSE;
	}

	/**
	 * Implementation of \PDO::quote()
	 *
	 * @see http://php.net/manual/en/pdo.quote.php
	 *
	 * @param $value
	 * @return string
	 */
	public function quote($value) {
		if (is_string($value) or (is_object($value) and method_exists($value, '__tostring'))) {
			$value = "'".mysql_real_escape_string(strval($value), $this->connection)."'";
		} elseif ($value === NULL) {
			$value = 'NULL';
		} elseif (is_bool($value)) {
			$value = $value ? 'TRUE' : 'FALSE';
		}
		return $value;
	}

	/**
	 * Count the number of rows in a table filtered by conditions
	 *
	 * @param string $field Parenthesized field name
	 * @param string $table Table name
	 * @param string $conditions conditions after "where"
	 * @return int
	 */
	public function count($field, $table, $conditions = "") {
		$cond = ($conditions ? " WHERE ".$conditions : "");
		$sql = "SELECT COUNT".$field." FROM ".$table.$cond;
		$result = $this->query($sql);
		return $result ? $this->fetchFirstColumn($result) : FALSE;
	}

	/**
	 * Fetch the first column of a specific row
	 *
	 * @param mixed $result The result of a query
	 * @param int $row
	 * @return mixed
	 */
	public function fetchFirstColumn($result, $row = 0) {
		$value = mysql_result($result, 0);
		return $value ? : FALSE;
	}

	/**
	 * Count the number of selected rows by the given query
	 *
	 * @param mixed $result The result of a query
	 * @return int
	 */
	public function countRows($result) {
		return @mysql_num_rows($result);
	}

	/**
	 * Fetch one row as an associative array
	 *
	 * @param mixed $result The result of a query
	 * @return array Associative array
	 */
	public function fetchAssoc($result) {
		$row = @mysql_fetch_assoc($result);
		return $row ? : FALSE;
	}

	/**
	 * Fetch one row as a numeric array
	 *
	 * @param mixed $result The result of a query
	 * @return array Numeric array
	 */
	public function fetchRow($result) {
		$row = @mysql_fetch_row($result);
		return $row ? : FALSE;
	}

	/**
	 * Get the last inserted auto increment id
	 *
	 * @return int
	 */
	public function getLastId() {
		return (int) mysql_insert_id($this->connection);
	}

}
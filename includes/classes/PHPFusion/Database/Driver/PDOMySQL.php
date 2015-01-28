<?php
/*-------------------------------------------------------
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
 --------------------------------------------------------
| Filename: PDOMySQL.php
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

use PDO;
use PDOException;
use PDOStatement;
use PHPFusion\Database\Exception\ConnectionException;
use PHPFusion\Database\Exception\SelectionException;
use PHPFusion\Database\AbstractDatabaseDriver;

class PDOMySQL extends AbstractDatabaseDriver {

	/**
	 * @var \PDO
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
		try {
			$pdo = $this->connection = new PDO("mysql:host=".$host.";dbname=".$db.";charset=utf8", $user, $pass);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
		} catch (PDOException $error) {
			throw $error->getCode() === self::ERROR_UNKNOWN_DATABASE
				? new SelectionException($error->getMessage(), $error->getCode(), $error)
				: new ConnectionException($error->getMessage(), $error->getCode(), $error);
		}
	}

	/**
	 * Send a database query
	 *
	 * @param string $query SQL
	 * @param array $parameters
	 * @return PDOStatement or FALSE on error
	 */
	public function _query($query, array $parameters = array()) {
		try {
			$result = $this->connection->prepare($query);
			$result->execute($parameters);
			return $result;
		} catch (PDOException $e) {
			trigger_error($e->getMessage(), E_USER_ERROR);
			return FALSE;
		}
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
		$statement = $this->query($sql);
		return $statement ? $statement->fetchColumn() : FALSE;
	}

	/**
	 * Fetch the first column of a specific row
	 *
	 * @param \PDOStatement $statement
	 * @param int $row
	 * @return mixed
	 */
	public function fetchFirstColumn($statement, $row = 0) {
		//seek
		for ($i = 0; $i < $row; $i++) {
			$statement->fetchColumn();
		}
		//returns false when an error occurs
		return $statement->fetchColumn();
	}

	/**
	 * Count the number of affected rows by the given query
	 *
	 * @param \PDOStatement $statement
	 * @return int
	 */
	public function countRows($statement) {
		return $statement->rowCount();
	}

	/**
	 * Fetch one row as an associative array
	 *
	 * @param \PDOStatement $statement
	 * @return array Associative array
	 */
	public function fetchAssoc($statement) {
		$statement->setFetchMode(PDO::FETCH_ASSOC);
		return $statement->fetch();
	}

	/**
	 * Fetch one row as a numeric array
	 *
	 * @param \PDOStatement $statement
	 * @return array Numeric array
	 */
	public function fetchRow($statement) {
		$statement->setFetchMode(PDO::FETCH_NUM);
		return $statement->fetch();
	}

	/**
	 * Get the last inserted auto increment id
	 *
	 * @return int
	 */
	public function getLastId() {
		return (int)$this->connection->lastInsertId();
	}

	/**
	 * @return PDO
	 */
	public function getConnection() {
		return $this->connection;
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
		return $this->connection->quote($value);
	}


}
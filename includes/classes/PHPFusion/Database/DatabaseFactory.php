<?php
/*-------------------------------------------------------
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
 --------------------------------------------------------
| Filename: DatabaseFactory.php
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

namespace PHPFusion\Database;

class DatabaseFactory {

	/**
	 * use mysql_* functions
	 */
	const DRIVER_MYSQL = 'MySQL';

	/**
	 * use \PDO class
	 */
	const DRIVER_PDO_MYSQL = 'PDOMySQL';

	/**
	 * Use the default driver (PDOMySQL)
	 */
	const DRIVER_DEFAULT = self::DRIVER_PDO_MYSQL;

	/**
	 * @var DatabaseFactory[]
	 */
	private static $instances = array();

	/**
	 * MySQL or PDOMySQL
	 *
	 * @var string
	 */
	private static $defaultDriver = self::DRIVER_DEFAULT;

	/**
	 * @var bool
	 */
	private static $debug = FALSE;

	/**
	 * @var string
	 */
	private $driver;

	/**
	 * @var AbstractDatabaseDriver
	 */
	private $connection = NULL;

	/**
	 * @param $driver
	 */
	private function __construct($driver) {
		$this->driver = $driver;
	}

	/**
	 * @param string $mode
	 * @return DatabaseFactory
	 */
	public static function getInstance($mode = NULL) {
		if (!$mode) {
			$mode = self::$defaultDriver;
		}
		if (!isset(self::$instances[$mode])) {
			self::$instances[$mode] = new static($mode);
		}
		return self::$instances[$mode];
	}

	public static function setDefaultDriver($defaultDriver) {
		self::$defaultDriver = $defaultDriver;
	}

	public static function getDefaultDriver() {
		return self::$defaultDriver;
	}

	public static function setDebug($debug = TRUE) {
		self::$debug = $debug;
	}

	public static function isDebug() {
		return self::$debug;
	}

	public function getDriver() {
		return $this->driver;
	}

	/**
	 * Connect to the database and store the connection object
	 *
	 * @param string $host
	 * @param string $user
	 * @param string $password
	 * @param string $db
	 * @return AbstractDatabaseDriver
	 * @throws Exception\SelectionException
	 * @throws Exception\ConnectionException
	 */
	public function connect($host, $user, $password, $db) {
		$className = __NAMESPACE__.'\Driver\\'.$this->getDriver();
		$this->connection = new $className($host, $user, $password, $db);
		$this->connection->setDebug(self::isDebug());
		return $this->connection;
	}

	/**
	 * Get the database connection object
	 *
	 * @return AbstractDatabaseDriver
	 */
	public function getConnection() {
		return $this->connection;
	}

}
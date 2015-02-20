<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: all_functions_include.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Database\DatabaseFactory;
use PHPFusion\Database\AbstractDatabaseDriver;
use PHPFusion\Database\Exception\SelectionException;

/*
 * It will be called after everything else even if the script is
 * halted by exit(), die(), fatal error or exception.
 *
 * It shows all executed
 */
register_shutdown_function(function() {
	if (DatabaseFactory::isDebug()) {
		$log = AbstractDatabaseDriver::getGlobalQueryLog();
		foreach ($log as $connectionid => $value) {
			if (!DatabaseFactory::isDebug($connectionid)) {
				unset($log[$connectionid]);
			}
		}
		print_p($log);
	}
});

/**
 * Send a database query
 *
 * @param string $query SQL
 * @param array $parameters
 * @return mixed The result of query or FALSE on error
 */
function dbquery($query, array $parameters = array()) {
	return dbconnection()->query($query, $parameters);
}

/**
 * Count the number of rows in a table filtered by conditions
 *
 * @param string $field Parenthesized field name
 * @param string $table Table name
 * @param string $conditions conditions after "where"
 * @return boolean
 */
function dbcount($field, $table, $conditions = "") {
	return dbconnection()->count($field, $table, $conditions);
}

/**
 * Fetch the first column of a specific row
 *
 * @param mixed $result
 * @param int $row
 * @return mixed
 */
function dbresult($result, $row) {
	return dbconnection()->fetchFirstColumn($result, $row);
}

/**
 * Count the number of affected rows by the given query
 *
 * @param mixed $result
 * @return int
 */
function dbrows($result) {
	return dbconnection()->countRows($result);
}

/**
 * Fetch one row as an associative array
 *
 * @param mixed $result
 * @return array Associative array
 */
function dbarray($result) {
	return dbconnection()->fetchAssoc($result);
}

/**
 * Fetch one row as a numeric array
 *
 * @param mixed $result
 * @return array Numeric array
 */
function dbarraynum($result) {
	return dbconnection()->fetchRow($result);
}

/**
 * Connect to the database
 *
 * @param string $db_host
 * @param string $db_user
 * @param string $db_pass
 * @param string $db_name
 * @param boolean $halt_on_error If it is TRUE, the script will halt in case of error
 * @return array
 */
function dbconnect($db_host, $db_user, $db_pass, $db_name, $halt_on_error = TRUE) {
	$connection_success = TRUE;
	$dbselection_success = TRUE;
	try {
		DatabaseFactory::connect($db_host, $db_user, $db_pass, $db_name);
	} catch (\Exception $e) {
		$connection_success = $e instanceof SelectionException;
		$dbselection_success = FALSE;
		if ($halt_on_error and !$connection_success) {
			die("<strong>Unable to establish connection to MySQL</strong><br />".$e->getCode()." : ".$e->getMessage());
		} elseif ($halt_on_error) {
			die("<strong>Unable to select MySQL database</strong><br />".$e->getCode()." : ".$e->getMessage());
		}

	}
	return array(
		'connection_success' => $connection_success,
		'dbselection_success' => $dbselection_success
	);
}

/**
 * Get the last inserted auto increment id
 *
 * @return int
 */
function dblastid() {
	return (int) dbconnection()->getLastId();
}

/**
 * Get the AbstractDatabase instance
 *
 * @return AbstractDatabaseDriver
 */
function dbconnection() {
	return DatabaseFactory::getConnection();
}

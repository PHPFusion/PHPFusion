<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: pdo_functions_include.php
| Author: Yodix
| Co-Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }
// MySQL database functions
/**
 * Send a database query
 * @global int   $mysql_queries_count
 * @global array $mysql_queries_time
 * @param string $query SQL
 * @return \PDOStatement or FALSE on error
 */
function dbquery($query, $print = FALSE) {
	global $mysql_queries_count, $mysql_queries_time;
	$start_time = microtime(TRUE);
	try {
		$result = dbconnection()->prepare($query);
		$result->execute();
		if ($print == 1) var_dump($query);
		$query_time = round((microtime(TRUE)-$start_time), 7);
		$mysql_queries_time[++$mysql_queries_count] = array($query_time, $query);
		return $result;
	} catch (PDOException $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
		if ($print == 1) var_dump($query);
		echo $e;
		return FALSE;
	}
}

/**
 * Count the number of rows in a table filtered by conditions
 * @global int   $mysql_queries_count
 * @global array $mysql_queries_time
 * @param string $field      Parenthesized field name
 * @param string $table      Table name
 * @param string $conditions conditions after "where"
 * @return boolean
 */
function dbcount($field, $table, $conditions = "") {
	global $mysql_queries_count, $mysql_queries_time;
	$cond = ($conditions ? " WHERE ".$conditions : "");
	$start_time = microtime(TRUE);
	$sql = "SELECT COUNT".$field." FROM ".$table.$cond;
	try {
		$statement = dbconnection()->prepare($sql);
		$statement->execute();
		$query_time = round((microtime(TRUE)-$start_time), 7);
		$mysql_queries_time[++$mysql_queries_count] = array($query_time, $sql);
		return $statement->fetchColumn();
	} catch (PDOException $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
		echo $e;
		return FALSE;
	}
}

/**
 * Fetch the first column of a specific row
 * @param \PDOStatement $statement
 * @param int           $row
 * @return mixed
 */
function dbresult($statement, $row) {
	//seek
	for ($i = 0; $i < $row; $i++) {
		$statement->fetchColumn();
	}
	//returns false when an error occurs
	return $statement->fetchColumn();
}

/**
 * Count the number of affected rows by the given query
 * @param \PDOStatement $statement
 * @return int
 */
function dbrows($statement) {
	return $statement->rowCount();
}

/**
 * Fetch one row as an associative array
 * @param \PDOStatement $statement
 * @return array Associative array
 */
function dbarray($statement) {
	$statement->setFetchMode(PDO::FETCH_ASSOC);
	return $statement->fetch();
}

/**
 * Fetch one row as a numeric array
 * @param \PDOStatement $statement
 * @return array Numeric array
 */
function dbarraynum($statement) {
	$statement->setFetchMode(PDO::FETCH_NUM);
	return $statement->fetch();
}

/**
 * Connect to the database
 * @param string  $db_host
 * @param string  $db_user
 * @param string  $db_pass
 * @param string  $db_name
 * @param boolean $halt_on_error If it is TRUE, the script will halt in case of error
 */
function dbconnect($db_host, $db_user, $db_pass, $db_name, $halt_on_error = TRUE) {
	$db_connect = TRUE;
	$db_select = TRUE;
	try {
		$pdo = dbconnection(new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8", $db_user, $db_pass));
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
	} catch (PDOException $error) {
		$db_connect = $error->getCode() === 1049; //unknown database
		$db_select = FALSE;
		if ($halt_on_error and !$db_connect) {
			die("<strong>Unable to establish connection to MySQL</strong><br />".$error->getCode()." : ".$error->getMessage());
		} elseif ($halt_on_error) {
			die("<strong>Unable to select MySQL database</strong><br />".$error->getCode()." : ".$error->getMessage());
		}
	}
	return array('connection_success' => $db_connect,
		'dbselection_success' => $db_select);
}

/**
 * Get the last inserted auto increment id
 * @global resource $db_connect
 * @return int
 */
function dbnextid($table_name) {
	$query = dbconnection()->prepare("SHOW TABLE STATUS LIKE '$table_name'");
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
      if (!empty($result)) {
         return $result['Auto_increment'];
      }
      return false;
}
/**
 * Get the last inserted auto increment id
 * @return int
 */
function dblastid() {
	return (int)dbconnection()->lastInsertId();
}

/**
 * Get and set the \PDO instance
 * @static \PDO|NULL $_pdo
 * @param \PDO $pdo
 * @return \PDO|NULL
 */
function dbconnection(\PDO $pdo = NULL) {
	static $_pdo = NULL;
	if (!empty($pdo) and $pdo instanceof \PDO) {
		$_pdo = $pdo;
	}
	return $_pdo;
}

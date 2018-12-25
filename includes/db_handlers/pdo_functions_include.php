<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: pdo_functions_include.php
| Author: Yodix
| Co-Author: Joakim Falk (Falk)
| Co-Author: Krelli (Systemweb)
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
	try {
		$mysql_queries_count++;
		$query_time = get_microtime();
		$result = dbconnection()->prepare($query);
		$result->execute();
		$query_time = substr((get_microtime()-$query_time), 0, 7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
		if ($print == 1) var_dump($query);
		return $result;
	} catch (PDOException $e) {
		trigger_error($e->getMessage(), E_USER_ERROR);
		if ($print == 1) var_dump($query);
		echo "<div style='text-align:center; padding:40px;'>\n";
		echo "<div style='width:50%; margin:15px auto;' class='tbl'>\n";
		echo "<div class='tbl-border' style='padding:15px; text-align:left;'>\n";
		echo "<pre style='white-space:pre-wrap !important;'>".$e."</pre>";
		echo "</div></div></div><br />";
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
	$cond = ($conditions ? " WHERE ".$conditions : "");
	$sql = "SELECT COUNT".$field." FROM ".$table.$cond;
	try {
		$statement = dbconnection()->prepare($sql);
		$statement->execute();
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
	$result = $statement->fetchColumn();
	return $result;
}

/**
 * Count the number of affected rows by the given query
 * @param \PDOStatement $statement
 * @return int
 */
function dbrows($statement) {
	$result = $statement->rowCount();
	return $result;
}

/**
 * Fetch one row as an associative array
 * @param \PDOStatement $statement
 * @return array Associative array
 */
function dbarray($statement) {
	$statement->setFetchMode(PDO::FETCH_ASSOC);
	$result = $statement->fetch();
	return $result;
}

/**
 * Fetch one row as a numeric array
 * @param \PDOStatement $statement
 * @return array Numeric array
 */
function dbarraynum($statement) {
	$statement->setFetchMode(PDO::FETCH_NUM);
	$result = $statement->fetch();
	return $result;
}

/**
 * Connect to the database
 * @param string  $db_host
 * @param string  $db_user
 * @param string  $db_pass
 * @param string  $db_name
 * @param boolean $halt_on_error If it is TRUE, the script will halt in case of error
 */
function dbconnect($db_host, $db_user, $db_pass, $db_name, $db_port = 3306) {
	$db_connect = TRUE;
	$db_select = TRUE;
	$halt_on_error = TRUE;
	try {
		$pdo = dbconnection(new PDO("mysql:host=".$db_host.";dbname=".$db_name.";charset=utf8;port=".$db_port, $db_user, $db_pass));
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
 * @return int
 */
function db_lastid() {
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

function dbclose() {
    global $pdo;
	/** @var PDO $pdo */
    return $pdo = null;
}

function dbnew_result($res, $row, $field=0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

function db_affrows() {
	global $pdo;
	/** @var PDO $pdo */
	return $pdo->rowCount();
}

// new added functions
function db_server_info() {
	return dbconnection()->getAttribute(constant("PDO::ATTR_SERVER_VERSION"));
}

function db_fieldcount($result) {
    return $result->columnCount();
}

function db_fetchfieldname($result, $field_offset) {
    $properties = $result->getColumnMeta($field_offset);
	return $properties['name'];
}

function db_fetch_row($result) {
	return $result->fetch(PDO::FETCH_NUM);
}

function db_use_result($result){
	global $pdo;
	$query = dbconnection()->prepare($result);
    $query->execute();
	return true;
}

function db_fetchrow($result){
    return $result->fetch_row();
}

// added for compatibility to older mysql commands:
if(!function_exists("mysql_field_name")) {
	function mysql_field_name($result, $field_offset) {
		return db_fetchfieldname($result, $field_offset);
	}
}
if(!function_exists("mysql_free_result")) {
	function mysql_free_result($result) {
		return $result->closeCursor();
	}
}
if(!function_exists("mysql_escape_string")) {
	function mysql_escape_string($query) {
		global $pdo;
		return $pdo->quote($unescaped_string);
	}
}
if(!function_exists("mysql_real_escape_string")) {
	function mysql_real_escape_string($query) {
		global $pdo;
		return $pdo->quote($unescaped_string);
	}
}
if(!function_exists("mysql_query")) {
	function mysql_query($query) {
		return dbquery($query);
	}
}
if(!function_exists("mysql_num_rows")) {
	function mysql_num_rows($result) {
		return dbrows($result);
	}
}
if(!function_exists("mysql_insert_id")) {
	function mysql_insert_id() {
		return db_lastid();
	}
}
if(!function_exists("mysql_connect")) {
	function mysql_connect($db_host, $db_user, $db_pass) {
    global $db_name;
        dbconnect($db_host, $db_user, $db_pass, $db_name, true);
	}
    function mysql_select_db($name) {
        return true;
    }
}
if(!function_exists("mysql_close")) {
	function mysql_close($dummy="") {
        dbclose();
        return true;
	}
}
if(!function_exists("mysql_affected_rows")) {
	function mysql_affected_rows($dummy="") {
        return db_affrows();
	}
}
if(!function_exists("mysql_field_name")) {
	function mysql_field_name($result, $field_offset) {
		return db_fetchfieldname($result, $field_offset);
	}
}

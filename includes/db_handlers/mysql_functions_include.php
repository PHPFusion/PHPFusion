<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mysql_functions_include.php
| Author: Joakim Falk (Domi)
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
function dbquery($query) {
	global $mysql_queries_count, $mysql_queries_time;
	$mysql_queries_count++;
	$query_time = microtime(TRUE);
	$result = @mysql_query($query, dbconnection());
	$query_time = round((microtime(TRUE)-$query_time), 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
	if (!$result) {
		echo mysql_error(dbconnection());
		return FALSE;
	} else {
		return $result;
	}
}

function dbcount($field, $table, $conditions = "") {
	global $mysql_queries_count, $mysql_queries_time;
	$mysql_queries_count++;
	$cond = ($conditions ? " WHERE ".$conditions : "");
	$query_time = microtime(TRUE);
	$result = @mysql_query("SELECT Count".$field." FROM ".$table.$cond, dbconnection());
	$query_time = substr((microtime(TRUE)-$query_time), 0, 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, "SELECT COUNT".$field." FROM ".$table.$cond);
	if (!$result) {
		echo mysql_error(dbconnection());
		return FALSE;
	} else {
		$rows = mysql_result($result, 0);
		return $rows;
	}
}

function dbresult($query, $row) {
	global $mysql_queries_count, $mysql_queries_time;
	$query_time = microtime(TRUE);
	$result = @mysql_result($query, $row);
	$query_time = substr((microtime(TRUE)-$query_time), 0, 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
	if (!$result) {
		echo mysql_error(dbconnection());
		return FALSE;
	} else {
		return $result;
	}
}

function dbrows($query) {
	$result = @mysql_num_rows($query);
	return $result;
}

function dbarray($query) {
	$result = @mysql_fetch_assoc($query);
	if (!$result) {
		echo mysql_error(dbconnection());
		return FALSE;
	} else {
		return $result;
	}
}

function dbarraynum($query) {
	$result = @mysql_fetch_row($query);
	if (!$result) {
		echo mysql_error(dbconnection());
		return FALSE;
	} else {
		return $result;
	}
}

function dbconnect($db_host, $db_user, $db_pass, $db_name, $halt_on_error = TRUE) {
	$db_connect = dbconnection(@mysql_connect($db_host, $db_user, $db_pass));
	mysql_set_charset('utf8', $db_connect);
	//dbquery("SET NAMES 'utf8'");
	//dbquery("SET CHARACTER SET utf8");
	//dbquery("SET COLLATION_CONNECTION = 'utf8_unicode_ci'");
	$db_select = @mysql_select_db($db_name, $db_connect);
	if ($halt_on_error) {
		if (!$db_connect) {
			die("<strong>Unable to establish connection to MySQL</strong><br />".mysql_errno()." : ".mysql_error());
		} elseif (!$db_select) {
			die("<strong>Unable to select MySQL database</strong><br />".mysql_errno()." : ".mysql_error());
		}
	}
	return array(
		'connection_success' => (bool) $db_connect,
		'dbselection_success' => (bool) $db_select
	);
}

/**
 * Get the last inserted auto increment id
 * 
 * @global resource $db_connect
 * @return int 
 */
function dblastid() {
	return (int) mysql_insert_id(dbconnection());
}

/**
 * Get and set the database connection resource
 * 
 * @sttaic resource $resource
 * @return boolean
 */
function dbconnection($resource = NULL) {
	static $_resource = NULL;
	if (!empty($resource) and is_resource($resource)) {
		$_resource = $resource;
	}
	return $_resource;
}

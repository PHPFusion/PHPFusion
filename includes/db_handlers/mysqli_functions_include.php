<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mysqli_functions_include.php
| Author: André Krell (Systemweb)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) {
	die("Access Denied");
}

// MySQLi database functions
function dbnew_result($res, $row, $field=0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

function db_lastid(){
	global $db_connect;
	return $db_connect->insert_id;
}

function db_affrows(){
	global $db_connect;
	return $db_connect->affected_rows;
}

function dbquery($query){
	global $db_connect, $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;

	$query_time = get_microtime();

	if (!$result = $db_connect->query($query)) {
		if ($db_connect->error) echo "Error: ".$db_connect->error."
		";
		return FALSE;
	} else {
		$query_time = substr((START_TIME - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
		return $result;
	}
}

function dbcount($field, $table, $conditions = "") {
	global $db_connect, $mysql_queries_count, $mysql_queries_time; $mysql_queries_count++;

	$cond = $conditions ? " WHERE ".$conditions : "";
	$query_time = get_microtime();

	if (!$result = $db_connect->query("SELECT Count".$field." FROM ".$table.$cond)) {
		if ($db_connect->error) echo "Error: ".$db_connect->error."
		";
		return FALSE;
	} else {
		$query_time = substr((get_microtime() - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, "SELECT COUNT".$field." FROM ".$table.$cond);
		$rows = dbnew_result($result, 0);
		return $rows;
	}
}

function dbresult($query, $row) {
	global $db_connect, $mysql_queries_count, $mysql_queries_time;

	$query_time = get_microtime();

	if (!$result = dbnew_result($query, $row)) {
		if ($db_connect->error) echo "Error: ".$db_connect->error."
		";
		return FALSE;
	} else {
		$query_time = substr((START_TIME - $query_time),0,7);
		$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
		return $result;
	}
}

function dbrows($result){
	return $result->num_rows;
}

function dbarray($query){
	global $db_connect;
	if (!$result = $query->fetch_assoc()) {
		if ($db_connect->error) echo "Error: ".$db_connect->error."
		";
		return FALSE;
	} else {
		return $result;
	}
}

function dbarraynum($query) {
	global $db_connect;
	if (!$result = $query->fetch_row()) {
		if ($db_connect->error) echo "Error: ".$db_connect->error."
		";
		return FALSE;
	} else {
		return $result;
	}
}

function dbconnect($db_host, $db_user, $db_pass, $db_name, $db_port=3306) {
	global $db_connect;

	// Create connection
	$db_connect = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
	unset($db_host, $db_port, $db_user, $db_pass);

	// Check connection
	if ($db_connect->connect_error) {
		die("Unable to establish connection to MySQL
		".$db_connect->connect_error);
	}
	else {
		mysqli_set_charset($db_connect, 'utf8');
		dbquery("SET NAMES 'utf8'");
	}
}

function dbclose() {
    global $db_connect;
	$db_connect->close();
}

// new added functions
function db_server_info() {
	global $db_connect;
	return $db_connect->server_info;
}

function db_fieldcount($result){
    return mysqli_num_fields($result);
}
function db_fetchfieldname($result, $field_offset) {
	$properties = mysqli_fetch_field_direct($result, $field_offset);
	return is_object($properties) ? $properties->name : null;
}

function db_fetch_row($result) {
	return mysqli_fetch_row($result);
}

function db_use_result($result) {
	global $db_connect;
	return $db_connect->query($result, MYSQLI_USE_RESULT);
}

// added for compatibility to older mysql commands:
if(!function_exists("mysql_field_name")) {
	function mysql_field_name($result, $field_offset) {
		return db_fetchfieldname($result, $field_offset);
	}
}
if(!function_exists("mysql_free_result")) {
	function mysql_free_result($result) {
		return mysqli_free_result($result);
	}
}
if(!function_exists("mysql_escape_string")) {
	function mysql_escape_string($query) {
		global $db_connect;
		$result = mysqli_real_escape_string($db_connect, $query);
		return $result;
	}
}
if(!function_exists("mysql_real_escape_string")) {
	function mysql_real_escape_string($query) {
		global $db_connect;
		$result = mysqli_real_escape_string($db_connect, $query);
		return $result;
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
        dbconnect($db_host, $db_user, $db_pass, $db_name, 3306);
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


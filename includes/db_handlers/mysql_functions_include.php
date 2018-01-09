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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
// MySQL database functions

// MYSQLI variable
$db_connect = NULL;


// MySQL database functions
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

function dbconnect($db_host, $db_user, $db_pass, $db_name, $db_port) {
	global $db_connect;

	// Create connection
	$db_connect = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
	unset($db_host, $db_port, $db_user, $db_pass);

	// Check connection
	if ($db_connect->connect_error) {
		die("Unable to establish connection to MySQL
		".$db_connect->connect_error);
	}
}

// added for compatibility to older mysql:
if(!function_exists("mysql_field_name")) {
	function mysql_field_name($result, $field_offset) {
		$properties = mysqli_fetch_field_direct($result, $field_offset);
		return is_object($properties) ? $properties->name : null;
	}
}
if(!function_exists("mysql_insert_id")) {
	function mysql_insert_id() 
	{
		global $db_connect;
		return mysqli_insert_id($db_connect);
	}
}

if(!function_exists("mysql_num_rows")) {
	function mysql_num_rows($result) 
	{
		return mysqli_num_rows($result);
	}
}

if(!function_exists("mysql_field_type")) {
/**
 * Returns a string that represents the mysql field type
 *
 * @param mysqli_resource $result The result resource that is being evaluated. This result comes from a call to mysql_query().
 * @param integer $field_offset The numerical field offset. The field_offset starts at 0. If field_offset does not exist, an error of level E_WARNING is also issued.
 */
function mysql_field_type( $result , $field_offset ) {
    static $types;

    $type_id = mysqli_fetch_field_direct($result,$field_offset)->type;

    if (!isset($types))
    {
        $types = array();
        $constants = get_defined_constants(true);
        foreach ($constants['mysqli'] as $c => $n) if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) $types[$n] = $m[1];
    }

    return array_key_exists($type_id, $types)? $types[$type_id] : NULL;
}
}

if(!function_exists("mysql_field_flags")) {
/**
 * Returns a string that represents the mysql field flags
 *
 * @param mysqli_resource $result The result resource that is being evaluated. This result comes from a call to mysql_query().
 * @param integer $field_offset The numerical field offset. The field_offset starts at 0. If field_offset does not exist, an error of level E_WARNING is also issued.
 */
function mysql_field_flags( $result , $field_offset ) {
    static $flags;

    // Get the field directly
    $flags_num = mysqli_fetch_field_direct($result,$field_offset)->flags;

    if (!isset($flags))
    {
        $flags = array();
        $constants = get_defined_constants(true);
        foreach ($constants['mysqli'] as $c => $n) if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m)) if (!array_key_exists($n, $flags)) $flags[$n] = $m[1];
    }

    $result = array();
    foreach ($flags as $n => $t) if ($flags_num & $n) $result[] = $t;

    $return = implode(' ', $result);
    $return = str_replace('PRI_KEY','PRIMARY_KEY',$return);
    $return = strtolower($return);

    return $return;
}
}



if(!function_exists("mysql_num_fields")) {
	function mysql_num_fields($result) 
	{
		return mysqli_num_fields($result);
	}
}


if(!function_exists("mysql_data_seek")) {
	function mysql_data_seek($result,$number) 
	{
		return mysqli_data_seek($result,$number);
	}
}


if(!function_exists("mysql_close")) {
	function mysql_close($db_connect) 
	{
		global $db_connect;
		return mysqli_close($db_connect);
	}
}


/*






function dbquery($query) {
	global $mysql_queries_count, $mysql_queries_time;
	$mysql_queries_count++;
	$query_time = get_microtime();
	$result = @mysql_query($query);
	$query_time = substr((get_microtime()-$query_time), 0, 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
	if (!$result) {
		echo mysql_error();
		return FALSE;
	} else {
		return $result;
	}
}

function dbcount($field, $table, $conditions = "") {
	global $mysql_queries_count, $mysql_queries_time;
	$mysql_queries_count++;
	$cond = ($conditions ? " WHERE ".$conditions : "");
	$query_time = get_microtime();
	$result = @mysql_query("SELECT Count".$field." FROM ".$table.$cond);
	$query_time = substr((get_microtime()-$query_time), 0, 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, "SELECT COUNT".$field." FROM ".$table.$cond);
	if (!$result) {
		echo mysql_error();
		return FALSE;
	} else {
		$rows = mysql_result($result, 0);
		return $rows;
	}
}

function dbresult($query, $row) {
	global $mysql_queries_count, $mysql_queries_time;
	$query_time = get_microtime();
	$result = @mysql_result($query, $row);
	$query_time = substr((get_microtime()-$query_time), 0, 7);
	$mysql_queries_time[$mysql_queries_count] = array($query_time, $query);
	if (!$result) {
		echo mysql_error();
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
		echo mysql_error();
		return FALSE;
	} else {
		return $result;
	}
}

function dbarraynum($query) {
	$result = @mysql_fetch_row($query);
	if (!$result) {
		echo mysql_error();
		return FALSE;
	} else {
		return $result;
	}
}

function dbconnect($db_host, $db_user, $db_pass, $db_name) {
	global $db_connect;
	$db_connect = @mysql_connect($db_host, $db_user, $db_pass);
	$db_select = @mysql_select_db($db_name);
	if (!$db_connect) {
		die("<strong>Unable to establish connection to MySQL</strong><br />".mysql_errno()." : ".mysql_error());
	} elseif (!$db_select) {
		die("<strong>Unable to select MySQL database</strong><br />".mysql_errno()." : ".mysql_error());
	}
} */

?>

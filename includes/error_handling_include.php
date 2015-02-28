<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: error_handling_include.php
| Author: Hans Kristian Flaatten (Starefossen)
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

include LOCALE.LOCALESET."errors.php";

// PHP Error Reporting
error_reporting(E_ALL ^ E_STRICT);
set_error_handler("setError");
$_errorHandler = array();

// PHP-Fusion Error Handler
function setError($error_level, $error_message, $error_file, $error_line, $error_context) {
	global $userdata, $_errorHandler;

	$showError = true;

	$result = dbquery(
		"SELECT error_id, error_status FROM ".DB_ERRORS."
		WHERE error_level='".intval($error_level)."' AND error_file='".stripinput($error_file)."'
		AND error_line='".intval($error_line)."' AND error_status!='1'
		ORDER BY error_timestamp DESC LIMIT 1"
	);
	if (dbrows($result) == 0) {
		$result = dbquery(
			"INSERT INTO ".DB_ERRORS." (
				error_level, error_message, error_file, error_line, error_page,
				error_user_level, error_user_ip, error_user_ip_type, error_status, error_timestamp
			) VALUES (
				'".intval($error_level)."', '".stripinput($error_message)."',
				'".stripinput($error_file)."', '".intval($error_line)."',
				'".TRUE_PHP_SELF."', '".$userdata['user_level']."', '".USER_IP."', '".USER_IP_TYPE."',
				'0', '".time()."'
			)"
		);
		$errorId = mysql_insert_id();
	} else {
		$data = dbarray($result);
		$errorId = $data['error_id'];
		if ($data['error_status'] == 2) { $showError = false; }
	}

	if ($showError) {
		$_errorHandler[] = array(
			"id" => $errorId, "level" => $error_level, "file" => $error_file,
			"line" => $error_line
		);
	}
}

// Error Levels Desciption
function getErrorLevel($level, $desc = false) {
	global $locale;

	$errorLevels = array(
		1 		=> array("E_ERROR", $locale['E_ERROR']),
		2 		=> array("E_WARNING", $locale['E_WARNING']),
		4 		=> array("E_PARSE", $locale['E_PARSE']),
		8 		=> array("E_NOTICE", $locale['E_NOTICE']),
		16 		=> array("E_CORE_ERROR", $locale['E_CORE_ERROR']),
		32 		=> array("E_CORE_WARNING", $locale['E_CORE_WARNING']),
		64 		=> array("E_COMPILE_ERROR", $locale['E_COMPILE_ERROR']),
		128 	=> array("E_COMPILE_WARNING", $locale['E_COMPILE_WARNING']),
		256 	=> array("E_USER_ERROR", $locale['E_USER_ERROR']),
		512 	=> array("E_USER_WARNING", $locale['E_USER_WARNING']),
		1024 	=> array("E_USER_NOTICE", $locale['E_USER_NOTICE']),
		2047 	=> array("E_ALL", $locale['E_ALL']),
		2048 	=> array("E_STRICT", $locale['E_STRICT'])
	);

	if (isset($errorLevels[$level])) {
		return $errorLevels[0].($desc ? " - ".$errorLevels[1] : "");
	} else {
		return $locale['err_100'];
	}
}
?>
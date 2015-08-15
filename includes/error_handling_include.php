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

use PHPFusion\Database\DatabaseFactory;

if (!defined("IN_FUSION")) {
	die("Access Denied");
}
include LOCALE.LOCALESET."errors.php";
// PHP Error Reporting
error_reporting(E_ALL ^ E_STRICT);
set_error_handler("setError");
$_errorHandler = array();
// PHP-Fusion Error Handler
function setError($error_level, $error_message, $error_file, $error_line, $error_context) {
	global $userdata, $_errorHandler;
	$showError = TRUE;
	$db = DatabaseFactory::getConnection();
	$result = $db->query(
		"SELECT error_id, error_status FROM ".DB_ERRORS."
		WHERE error_message = :message AND error_file = :file AND error_line = :line AND error_status != '1'
		ORDER BY error_timestamp DESC LIMIT 1", array(
			':message' => $error_message,
			':file' => $error_file,
			':line' => $error_line,
		));
	if ($db->countRows($result) == 0) {
		$db->query("INSERT INTO ".DB_ERRORS." (
				error_level, error_message, error_file, error_line, error_page,
				error_user_level, error_user_ip, error_user_ip_type, error_status, error_timestamp
			) VALUES (
				:level, :message, :file, :line,
				'".TRUE_PHP_SELF."', '".$userdata['user_level']."', '".USER_IP."', '".USER_IP_TYPE."',
				'0', '".time()."'
			)", array(
				':level' => $error_level,
				':message' => $error_message,
				':file' => $error_file,
				':line' => $error_line,
			));
		$errorId = $db->getLastId();
	} else {
		$data = $db->fetchAssoc($result);
		$errorId = $data['error_id'];
		if ($data['error_status'] == 2) {
			$showError = FALSE;
		}
	}
	if ($showError) {
		$_errorHandler[] = array(
			"id" => $errorId,
			"level" => $error_level,
			"file" => $error_file,
			"line" => $error_line,
		);
	}
}

// Error Levels Desciption
function getErrorLevel($level, $desc = FALSE) {
	global $locale;
	$errorLevels = array(1 => array("E_ERROR", $locale['E_ERROR']),
		2 => array("E_WARNING", $locale['E_WARNING']),
		4 => array("E_PARSE", $locale['E_PARSE']),
		8 => array("E_NOTICE", $locale['E_NOTICE']),
		16 => array("E_CORE_ERROR", $locale['E_CORE_ERROR']),
		32 => array("E_CORE_WARNING", $locale['E_CORE_WARNING']),
		64 => array("E_COMPILE_ERROR", $locale['E_COMPILE_ERROR']),
		128 => array("E_COMPILE_WARNING", $locale['E_COMPILE_WARNING']),
		256 => array("E_USER_ERROR", $locale['E_USER_ERROR']),
		512 => array("E_USER_WARNING", $locale['E_USER_WARNING']),
		1024 => array("E_USER_NOTICE", $locale['E_USER_NOTICE']),
		2047 => array("E_ALL", $locale['E_ALL']),
		2048 => array("E_STRICT", $locale['E_STRICT']));
	if (isset($errorLevels[$level])) {
		return $errorLevels[0].($desc ? " - ".$errorLevels[1] : "");
	} else {
		return $locale['err_100'];
	}
}

function fusion_turbo_debugger() {
	if (iADMIN && checkrights('ERRO') || iSUPERADMIN) {
		$error_logs = new \PHPFusion\ErrorLogs();
		$error_logs->compressed = 1;
		if (!defined('no_debugger')) {
			echo openmodal('tbody', 'Fusion Debugger', array('class' => 'modal-lg modal-center zindex-boost',
				'button_id' => 'turbo_debugger'));
			$error_logs->show_footer_logs();
			echo closemodal();
		}
	}
}



<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_birthdate_include.php
| Author: Digitanium
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
// Display user field input
if ($profile_method == "input") {
	if (isset($user_data['user_birthdate']) && $user_data['user_birthdate'] != "0000-00-00") {
		$user_birthdate = date('d-m-Y', strtotime($user_data['user_birthdate']));
	} else {
		$user_birthdate = '0000-00-00';
	}
	$options +=array('inline'=>1);
	$user_fields = form_datepicker($locale['uf_birthdate'], 'user_birthdate', 'user_birthdate', $user_birthdate, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	include LOCALE.LOCALESET."global.php";
	if ($user_data['user_birthdate'] != "0000-00-00") {
		$months = explode("|", $locale['months']);
		// need to validate this part after display.input class done.
		$user_birthdate = explode("-", $user_data['user_birthdate']);
		$user_fields = array('title'=>$locale['uf_birthdate'], 'value'=>$months[number_format($user_birthdate['1'])]." ".$user_birthdate['2']." ".$user_birthdate['0']);
	}
	// Insert and update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	$user_month = 0;
	$user_day = 0;
	$user_year = 0;
	if (isset($_POST['user_year']) && isnum($_POST['user_year']) && $_POST['user_year'] != 0) {
		$user_year = $_POST['user_year'];
	}
	if (isset($_POST['user_month']) && isnum($_POST['user_month']) && $_POST['user_month'] != 0) {
		$user_month = $_POST['user_month'];
	}
	if (isset($_POST['user_day']) && isnum($_POST['user_day']) && $_POST['user_day'] != 0) {
		$user_day = $_POST['user_day'];
	}
	if (($user_month != 0 && $user_day != 0 && $user_year != 0) || $this->_isNotRequired("user_birthdate")) {
		// Set update or insert user data
		$this->_setDBValue("user_birthdate", $user_year."-".$user_month."-".$user_day);
	} else {
		$this->_setError("user_birthdate", $locale['uf_birthdate_error'], TRUE);
	}
}
?>
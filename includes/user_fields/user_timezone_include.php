<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_timezone_include.php
| Author: Maarten Kossen (mistermartin75)
| Fixed: Chubatyj Vitalij (Rizado) Oct 20 2014
| Reason: some new timezones
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

// Display user field input
if ($profile_method == "input") {
	$user_timezone = isset($user_data['user_timezone']) ? $user_data['user_timezone'] : "Europe/London";
	if ($this->isError()) {
		$user_timezone = isset($_POST['user_timezone']) && is_numeric($_POST['user_timezone']) ? $_POST['user_timezone'] : $user_timezone;
	}

	$timezones = timezone_abbreviations_list();
	$timezoneArray = array();
	foreach ($timezones as $zones) {
		foreach ($zones as $zone) {
			if (preg_match('/^(America|Antartica|Arctic|Asia|Atlantic|Europe|Indian|Pacific)\//', $zone['timezone_id'])) {
				if (!in_array($zone['timezone_id'], $timezoneArray)) {
					$timezoneArray[$zone['timezone_id']] = $zone['timezone_id'];
				}
			}
		}
	}
//form_select($title, $input_name, $input_id, array $option_array = array(), $input_value = FALSE, array $options = array()) {
	unset($timezones);
	
	echo "<tr>\n";
	echo "<td class='tbl".$this->getErrorClass("user_timezone")."'><label for='user_timezone_input'>".$locale['uf_timezone'].$required."</label></td>\n";
	echo "<td class='tbl".$this->getErrorClass("user_timezone")."'>\n";

	echo form_select('', 'user_timezone', 'user_timezone', $timezoneArray, $user_data['user_timezone'], array('width' => '100%'));

	echo "</td>\n";
	echo "</tr>\n";
	// Display in profile
} elseif ($profile_method == "display") {
	// Insert and update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_timezone']) && $_POST['user_timezone'] != "") {
		// Set update or insert user data
		$this->_setDBValue("user_timezone", $_POST['user_timezone']);
	} else {
		$this->_setError("user_timezone", $locale['uf_timezone_error'], TRUE);
	}
}
?>

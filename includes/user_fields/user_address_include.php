<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_address_include.php
| Author: Hien (Frederick MC Chan)
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
	$user_address = isset($user_data['user_address']) ? $user_data['user_address'] : "";
	if ($this->isError()) {
		$user_address = isset($_POST['user_address']) ? form_sanitizer($_POST['user_address'], '', 'user_address') : $user_address;
	}
	echo "<tr>\n<td colspan='2'>\n";
	echo form_address('', 'user_address', 'user_address', $user_address, array('flag'=>1));
	echo "</td>\n</tr>\n";
	// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_address']) {
		$address = explode('|', $user_data['user_address']);
		$add = '';
		foreach($address as $value) {
			$add .= "$value<br/>\n";
		}
		echo "<tr>\n";
		echo "<td class='tbl1'>".$locale['uf_address']."</td>\n";
		echo "<td align='left' class='tbl1'>$add</td>\n";
		echo "</tr>\n";
	}
	// Insert and update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_address']) && ($_POST['user_address'] != "" || $this->_isNotRequired("user_address"))) {
		// Set update or insert user data
		$this->_setDBValue("user_address", form_sanitizer($_POST['user_address'], '', 'user_address'));
	}
}
?>
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
	$user_address = isset($_POST['user_address']) ? form_sanitizer($_POST['user_address'], '', 'user_address') : $user_address;
	$options += array('inline'=>1, 'flag'=>1);

	$user_fields = form_address($locale['uf_address'], 'user_address', 'user_address', $user_address, $options);

}

elseif ($profile_method == "display") {
	if ($user_data['user_address']) {
		$address = explode('|', $user_data['user_address']);
		$add = '';
		foreach($address as $value) {
			$add .= "$value<br/>\n";
		}
		$user_fields = array('title'=>$locale['uf_address'], 'value'=>$add);
	}
}

elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Insert and update
	// Get input data
	if (isset($_POST['user_address']) && ($_POST['user_address'] != "" || $this->_isNotRequired("user_address"))) {
		// Set update or insert user data
		$this->_setDBValue("user_address", form_sanitizer($_POST['user_address'], '', 'user_address'));
	}
}
?>
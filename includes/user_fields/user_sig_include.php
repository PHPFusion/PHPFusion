<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_sig_include.php
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
	require_once INCLUDES."bbcode_include.php";
	$user_sig = isset($user_data['user_sig']) ? $user_data['user_sig'] : "";
	$user_sig = isset($_POST['user_sig']) ? stripinput($_POST['user_sig']) : $user_sig;
	$options +=array('bbcode'=>1, 'inline'=>1, 'form'=>'inputform');
	$user_fields = form_textarea($locale['uf_sig'], 'user_sig', 'user_sig', $user_sig, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	// Insert and update
	if ($user_data['user_sig']) {
		$user_fields = array('title'=>$locale['uf_sig'], 'value'=>$user_data['user_sig']);
	}
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_sig']) && ($_POST['user_sig'] != "" || $this->_isNotRequired("user_sig"))) {
		// Set update or insert user data
		$this->_setDBValue("user_sig", stripinput(trim($_POST['user_sig'])));
	} else {
		$this->_setError("user_sig", $locale['uf_sig_error'], TRUE);
	}
}
?>
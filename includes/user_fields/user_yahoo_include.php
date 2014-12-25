<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_yahoo_include.php
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
	$user_yahoo = isset($user_data['user_yahoo']) ? $user_data['user_yahoo'] : "";
	$user_yahoo = isset($_POST['user_yahoo']) ? stripinput($_POST['user_yahoo']) : $user_yahoo;
	$options +=array('inline'=>1, 'max_length'=>100, 'width'=>'200px');
	$user_fields = form_text($locale['uf_yahoo'], 'user_yahoo', 'user_yahoo', $user_yahoo, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	if ($user_data['user_yahoo']) {
		$user_fields = array('title'=>$locale['uf_yahoo'], 'value'=>$user_data['user_yahoo']);
	}
	// Insert and update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_yahoo']) && ($_POST['user_yahoo'] != "" || $this->_isNotRequired("user_yahoo"))) {
		// Set update or insert user data
		$this->_setDBValue("user_yahoo", stripinput($_POST['user_yahoo']));
	} else {
		$this->_setError("user_yahoo", $locale['uf_yahoo_error'], TRUE);
	}
}
?>

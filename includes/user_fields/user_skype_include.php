<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_skype_include.php
| Author: Hans Kristian Flaatten {Starefossen}
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
//print_p($profile_method);
// Display user field input
if ($profile_method == "input") {
	$user_skype = isset($user_data['user_skype']) ? $user_data['user_skype'] : "";
	//if ($this->isError()) {
		$user_skype = isset($_POST['user_skype']) ? stripinput($_POST['user_skype']) : $user_skype;
	//}

	$options += array('inline'=>1, 'max_length'=>32, 'max_width'=>'200px');
	$user_fields = form_text($locale['uf_skype'], 'user_skype', 'user_skype', $user_skype, $options);

	// Display in profile
} elseif ($profile_method == "display") {

	if ($user_data['user_skype']) {
		$user_fields = array('title'=>$locale['uf_skype'], 'value'=>$user_data['user_skype']);
	}
	// Insert or update
} elseif ($profile_method == "validate_insert" || $profile_method == "validate_update") {
	// Get input data
	if (isset($_POST['user_skype']) && ($_POST['user_skype'] != "" || $this->_isNotRequired("user_skype"))) {
		// Set update or insert user data
		$this->_setDBValue("user_skype", stripinput(trim($_POST['user_skype'])));
	} else {
		$this->_setError("user_skype", $locale['uf_skype_error'], TRUE);
	}
}
?>
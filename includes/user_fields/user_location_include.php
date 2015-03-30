<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_location_include.php
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
	$user_location = isset($user_data['user_location']) ? stripinput($user_data['user_location']) : "";
	$user_location = isset($_POST['user_location']) ? stripinput($_POST['user_location']) : $user_location;
	$options +=array('inline'=>1, 'max_length'=>50);
	$user_fields = form_text('user_location', $locale['uf_location'], $user_location, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = array('title'=>$locale['uf_location'], 'value'=>$field_value);
	}
}
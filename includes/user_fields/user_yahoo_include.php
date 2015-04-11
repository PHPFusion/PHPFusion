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
	$options = array('inline'=>1, 'max_length'=>100, 'width'=>'200px');
	$user_fields = form_text('user_yahoo', $locale['uf_yahoo'], $field_value, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = array('title'=>$locale['uf_yahoo'], 'value'=>$field_value);
	}
	// Insert and update
}
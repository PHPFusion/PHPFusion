<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_icq_include.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

//Display user field input
if ($profile_method == "input") {
	$options = array('inline'=>1, 'number'=>1, 'max_length'=>16);
	$user_fields = form_text('user_icq', $locale['uf_icq'], $field_value, $options);

//Display in profile
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = array('title'=>$locale['uf_icq'], 'value'=>$field_value);
	}
}
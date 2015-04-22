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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if ($profile_method == "input") {
	$options = array('inline'=>1, 'max_length'=>32, 'max_width'=>'200px');
	$user_fields = form_text('user_skype', $locale['uf_skype'], $field_value, $options);
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = array('title'=>$locale['uf_skype'], 'value'=>$field_value);
	}
}
?>
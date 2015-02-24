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
	$options += array('inline'=>1);
	$user_fields = form_address($options['show_title'] ? $locale['uf_address'] : '', 'user_address', 'user_address', $field_value, $options);
}
elseif ($profile_method == "display") {

	if ($field_value) {
		$address = explode('|', $field_value);
		$field_value = '';
		foreach($address as $value) {
			$field_value .= "$value<br/>\n";
		}
	} else {
		$field_value = $locale['na'];
	}

	$user_fields = array('title'=>$locale['uf_address'], 'value'=>$field_value);

}

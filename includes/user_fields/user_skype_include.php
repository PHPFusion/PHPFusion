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

// Display user field input
if ($profile_method == "input") {
	$options = array('inline'		=> 1,
					 'max_length'	=> 32,
					 'max_width'	=> '200px',
					 // TODO: Also accept MS accounts which are email addresses
					 'regex'		=> '[a-z0-9]{5,31}',
					 // TODO: Change the error text in case a value was entered but is not valid
					 'error_text'	=> $locale['uf_skype_error']
					 );
	$user_fields = form_text('user_skype', $locale['uf_skype'], $field_value, $options);

// Display user field input
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = array('title'=>$locale['uf_skype'], 'value'=>$field_value);
	}
}
?>
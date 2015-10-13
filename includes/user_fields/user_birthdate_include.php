<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_birthdate_include.php
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

// Display user field input
if ($profile_method == "input") {
	if (isset($field_value) && $field_value != "0000-00-00") {
		$user_birthdate = date('d-m-Y', strtotime($field_value));
	} else {
		$user_birthdate = '0000-00-00';
	}
	$options += array('inline'=>true, 'type'=>'date');
	$user_fields = form_datepicker('user_birthdate', $locale['uf_birthdate'], $user_birthdate, $options);

// Display in profile
} elseif ($profile_method == "display") {
	include LOCALE.LOCALESET."global.php";
	if ($field_value != "0000-00-00") {
		$months = explode("|", $locale['months']);
		$user_birthdate = explode("-", $field_value);
		$user_fields = array('title'=>$locale['uf_birthdate'], 'value'=>"".$user_birthdate['2']." ".$months[number_format($user_birthdate['1'])]." ".$user_birthdate['0']);
	} else {
		$user_fields = array('title'=>$locale['uf_birthdate'], 'value'=>$locale['na']);
	}
}

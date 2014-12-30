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
	//$user_data['user_birthdate'] = '1981-08-11';
	if (isset($user_data['user_birthdate']) && $user_data['user_birthdate'] != "0000-00-00") {
		$user_birthdate = date('d-m-Y', strtotime($user_data['user_birthdate']));
	} else {
		$user_birthdate = '0';
	}
	$options +=array('inline'=>1, 'type'=>'date');
	$user_fields = form_datepicker($locale['uf_birthdate'], 'user_birthdate', 'user_birthdate', $user_birthdate, $options);
	// Display in profile
} elseif ($profile_method == "display") {
	include LOCALE.LOCALESET."global.php";
	if ($user_data['user_birthdate'] != "0000-00-00") {
		$months = explode("|", $locale['months']);
		// need to validate this part after display.input class done.
		$user_birthdate = explode("-", $user_data['user_birthdate']);
		$user_fields = array('title'=>$locale['uf_birthdate'], 'value'=>$months[number_format($user_birthdate['1'])]." ".$user_birthdate['2']." ".$user_birthdate['0']);
	}
}
?>
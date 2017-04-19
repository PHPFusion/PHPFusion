<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_timezone_include.php
| Author: Maarten Kossen (mistermartin75)
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
$timezones = DateTimeZone::listIdentifiers(DateTimeZone::AMERICA | DateTimeZone::AFRICA | DateTimeZone::ARCTIC | DateTimeZone::ASIA | DateTimeZone::ATLANTIC | DateTimeZone::EUROPE | DateTimeZone::INDIAN | DateTimeZone::PACIFIC); //gives both african and american time zones

foreach ($timezones as $zone) {
	$zone = explode('/', $zone);
	if (!empty($zone[1])) {
		$timezoneArray[$zone[0].'/'.$zone[1]] = str_replace('_', ' ', $zone[1]);
	}
}
    $options = [
            'inline'   => TRUE,
            'options'  => $timezoneArray,
        ] + $options;
    $user_fields = form_select('user_timezone', $locale['uf_timezone'], $field_value, $options);
    // Display in profile
} elseif ($profile_method == "display") {
    if (!empty($field_value)){
		$zone = explode('/', $field_value);
		if (!empty($zone[1])) {
			$field_value = str_replace('_', ' ', $zone[1]);
		}
    	$user_fields = array('title' => $locale['uf_timezone'], 'value' => $field_value);
    }
}

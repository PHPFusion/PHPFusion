<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_gender_include.php
| Author: PHP-Fusion Development Team
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

$gen_set = 1; //0 = Just text; 1 = Emotikum + text; 2 = text + image; 3 = Just images.
$locale['uf_kep'] = ['0' => "",
						  '1' => ["fa fa-times-circle-o", "fa fa-mars", "fa fa-venus", "fa fa-user-secret"], // Emotikum megadás
						  '2' => ["no", "male", "female", "no"],  // image name
						  '3' => ["no", "male", "female", "no"]  // image name
						  ];

for ($i = 0; $i < count($locale['uf_gender_sz']); $i++) {
$locale['uf_gender_szkep'][] = ($gen_set == 0 ? $locale['uf_gender_sz'][$i] : ($gen_set == 1 ? "<i class='".$locale['uf_kep'][$gen_set][$i]." fa-lg m-r-10'></i>".$locale['uf_gender_sz'][$i] :
($gen_set == 2 ? $locale['uf_gender_sz'][$i] : "")." <img src='".IMAGES."user_fields/".$locale['uf_kep'][$gen_set][$i].".png' width='16' title='".$locale['uf_gender_sz'][$i]."' >"));
}

if ($profile_method == "input") {
	$options = array('inline'     => TRUE,
					 'error_text' => $locale['uf_gender_error'],
                     'options'    => $locale['uf_gender_sz'],
					 'width'      => '200px',
					 ) + $options;
	(defined("ADMIN_PANEL") ? $options['tip'] = $locale['uf_gender_tipp'] : "");

	$user_fields = form_select('user_gender', $locale['uf_gender'], $field_value, $options);

// Display user field input
} elseif ($profile_method == "display") {
	if ($field_value) {
		$user_fields = [
			'title' => $locale['uf_gender'],
			'value' => $locale['uf_gender_szkep'][$field_value]
			];
	}
}
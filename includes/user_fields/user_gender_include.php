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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Variation Customization
$gen_set = 2; //0 = Just text; 1 = Icon + text; 2 = text + image; 3 = Just images.
$with_secret = FALSE; // True for additiona unspecified option
$input_type = 'form_checkbox'; // form_select or form_checkbox (up to you)

// Definitions
$locale['uf_kep'] = [
    '0' => '', // Text
    '1' => ["fa fa-user-secret", "fa fa-mars", "fa fa-venus", "fa fa-user-secret"], // Icon
    '2' => ["unspecified", "male", "female", "unspecified"],  // Image + text
    '3' => ["unspecified", "male", "female", "unspecified"]  // Image
];

for ($i = 0; $i < count($locale['uf_gender_sz']); $i++) {
    switch ($gen_set) {
        case 3:
            $value = "<img src='".IMAGES."user_fields/gender/".$locale['uf_kep'][$gen_set][$i].".png' style='width: 16px;' alt='".$locale['uf_gender_sz'][$i]."' title='".$locale['uf_gender_sz'][$i]."'/>";
            break;
        case 1:
            $value = "<i class='".$locale['uf_kep'][$gen_set][$i]." fa-fw fa-lg m-r-10'></i>".$locale['uf_gender_sz'][$i];
            break;
        case 2:
            $value = "<img src='".IMAGES."user_fields/gender/".$locale['uf_kep'][$gen_set][$i].".png' style='width: 16px;' alt='".$locale['uf_gender_sz'][$i]."' title='".$locale['uf_gender_sz'][$i]."'/> ".$locale['uf_gender_sz'][$i];
            break;
        default:
            $value = $locale['uf_gender_sz'][$i];
    }
    $locale['uf_gender_szkep'][] = $value;
}
if (!$with_secret) {
    unset($locale['uf_gender_szkep'][count($locale['uf_gender_szkep']) - 1]);
}

if ($profile_method == "input") {
    $options = [
            'type'       => 'radio',
            'inline'     => TRUE,
            'error_text' => $locale['uf_gender_error'],
            'options'    => $locale['uf_gender_szkep'],
        ] + $options;
    $user_fields = $input_type('user_gender', $locale['uf_gender'], $field_value, $options);
    // Display user field input
} elseif ($profile_method == "display") {
    if ($field_value) {
        $user_fields = [
            'title' => $locale['uf_gender'],
            'value' => $locale['uf_gender_szkep'][$field_value]
        ];
    }
}

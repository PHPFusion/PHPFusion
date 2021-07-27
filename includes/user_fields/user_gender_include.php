<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_gender_include.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

// Variation Customization
$gen_set = defined('UF_GEN_SET') ? UF_GEN_SET : 2; // 0 = Just text; 1 = Icon + text; 2 = text + image; 3 = Just images.
$with_secret = defined('UF_GEN_SECRET') ? UF_GEN_SECRET : FALSE; // Set true for additional unspecified option
$input_type = 'form_checkbox'; // form_select or form_checkbox (up to you)

/*
 * Field Value
 * 0    Unspecified
 * 1    Male
 * 2    Female
 * 3    Unspecified
 */

// Definitions
$img = [
    '0' => '', // Text
    '1' => ['fa fa-user-secret', 'fa fa-mars', 'fa fa-venus', 'fa fa-user-secret'], // Icon
    '2' => ['unspecified', 'male', 'female', 'unspecified'],  // Image + text
    '3' => ['unspecified', 'male', 'female', 'unspecified']  // Image
];

$gen_options = [
    0 => $locale['uf_gender_00'],
    1 => $locale['uf_gender_01'],
    2 => $locale['uf_gender_02'],
    3 => $locale['uf_gender_03']
];

$gen_opts = [];

for ($i = 0; $i < count($gen_options); $i++) {
    switch ($gen_set) {
        case 3:
            $value = "<img src='".IMAGES."user_fields/gender/".$img[$gen_set][$i].".png' alt='".$gen_options[$i]."' title='".$gen_options[$i]."'/>";
            break;
        case 1:
            $value = "<i class='m-l-5 ".$img[$gen_set][$i]."'></i> ".$gen_options[$i];
            break;
        case 2:
            $value = "<img class='m-l-5' style='width: 16px;' src='".IMAGES."user_fields/gender/".$img[$gen_set][$i].".png' alt='".$gen_options[$i]."' title='".$gen_options[$i]."'/> ".$gen_options[$i];
            break;
        default:
            $value = $gen_options[$i];
    }
    $gen_opts[] = $value;
}

if ($profile_method == "input") {
    if (!$with_secret) {
        unset($gen_opts[3]);
    }

    $options = [
            'type'       => 'radio',
            'inline'     => TRUE,
            'error_text' => $locale['uf_gender_error'],
            'options'    => $gen_opts,
        ] + $options;

    $user_fields = $input_type('user_gender', $locale['uf_gender'], $field_value, $options);
} else if ($profile_method == "display") {
    if ($field_value) {
        $user_fields = [
            'title' => $locale['uf_gender'],
            'value' => $gen_opts[$field_value]
        ];
    }
}

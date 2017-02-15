<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_icq_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/icq.svg'/>";
// Display user field input
if ($profile_method == "input") {
    $options = array(
            'inline'           => TRUE,
            'number'           => TRUE,
            'max_length'       => 9,
            'regex'            => '^(-*[0-9]-*){8,9}$',
            'placeholder'      => $locale['uf_icq_desc'],
            'error_text'       => $locale['uf_icq_error'],
            'regex_error_text' => $locale['uf_icq_error_1'],
            'label_icon'       => $icon,
        ) + $options;
    $user_fields = form_text('user_icq', $locale['uf_icq'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    $user_fields = array('title' => $icon.$locale['uf_icq'], 'value' => $field_value ?: "");
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_whatsapp_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/whatsapp.svg'>\n";
// Display user field input
if ($profile_method == "input") {
    $options = array(
            'inline'      => TRUE,
            'max_length'  => 16,
            'error_text'  => $locale['uf_whatsapp_error'],
            'placeholder' => $locale['uf_whatsapp'],
            'label_icon'  => $icon,
            'type'        => 'number'
        ) + $options;
    $user_fields = form_text('user_whatsapp', $locale['uf_whatsapp'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    $user_fields = array('title' => $icon.$locale['uf_whatsapp'], 'value' => $field_value ?: '');
}

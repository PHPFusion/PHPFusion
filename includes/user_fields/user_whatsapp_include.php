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
defined('IN_FUSION') || exit;

$icon = "<img src='".IMAGES."user_fields/social/whatsapp.svg' title='Whatsapp' alt='Whatsapp'/>";
// Display user field input
if ($profile_method == "input") {
    $options = [
            'inline'      => TRUE,
            'max_length'  => 16,
            'error_text'  => $locale['uf_whatsapp_error'],
            'placeholder' => $locale['uf_whatsapp'],
            'label_icon'  => $icon,
            'type'        => 'number'
        ] + $options;
    $user_fields = form_text('user_whatsapp', $locale['uf_whatsapp'], $field_value, $options);
    // Display in profile
} else if ($profile_method == "display") {
    $user_fields = [
        'icon'  => $icon,
        'title' => $locale['uf_whatsapp'],
        'value' => $field_value ?: ''
    ];
}

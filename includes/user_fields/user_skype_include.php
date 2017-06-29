<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Display user field input
$icon = "<img src='".IMAGES."user_fields/social/skype.svg'>";
if ($profile_method == 'input') {
    $options = array(
            'inline'           => TRUE,
            'max_length'       => 32,
            // TODO: Also accept MS accounts which are email addresses
            'regex'            => '[a-z.0-9]{5,31}',
            'regex_error_text' => $locale['uf_skype_error_1'],
            'error_text'       => $locale['uf_skype_error'],
            'placeholder'      => $locale['uf_skype_id'],
            'label_icon'       => $icon,
        ) + $options;
    $user_fields = form_text('user_skype', $locale['uf_skype'], $field_value, $options);
// Display user field input
} elseif ($profile_method == 'display') {
    $user_fields = array('title' => $icon.$locale['uf_skype'], 'value' => $field_value ?: "");
}

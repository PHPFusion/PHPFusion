<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_sig_include.php
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

// Display user field input
if ($profile_method == "input") {
    require_once INCLUDES."bbcode_include.php";

    $options += ["bbcode" => TRUE, "inline" => TRUE, 'form_name' => 'userfieldsform'];

    $user_fields = form_textarea('user_sig', $locale['uf_sig'], $field_value, $options);

} else if ($profile_method == "display") {
    $user_fields = [
        'title' => $locale['uf_sig'],
        'value' => $field_value ? nl2br(parseubb(parsesmileys($field_value))) : fusion_get_locale('na')
    ];
}

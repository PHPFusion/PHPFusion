<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_name_middle_include.php
| Author: Chubatyj Vitalij (Rizado)
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

if ($profile_method == "input") {
    $options += ['inline' => TRUE, 'max_length' => 20,];
    $user_fields = form_text('user_name_middle', $locale['uf_name_middle'], $field_value, $options);
} else if ($profile_method == "display") {
    $user_fields = [
        'title' => $locale['uf_name_middle'],
        'value' => $field_value ?: ''
    ];
}

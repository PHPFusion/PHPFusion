<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_phpfusion_include.php
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

$icon = "<img src='".IMAGES."php-fusion-icon.png'/>";
// Display user field input
if ($profile_method == "input") {
    $options = array(
            'inline'      => TRUE,
            'max_length'  => 20,
            'error_text'  => $locale['uf_phpfusion_error'],
            'placeholder' => $locale['uf_phpfusion_id'],
            'label_icon'  => $icon,
        ) + $options;
    $user_fields = form_text('user_phpfusion', $locale['uf_phpfusion'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    $user_fields = array('title' => $icon.$locale['uf_phpfusion'], 'value' => $field_value ?: '');
}

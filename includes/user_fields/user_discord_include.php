<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_discord_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/discord.svg'>\n";
// Display user field input
if ($profile_method == "input") {
    $options = array(
            'inline'           => TRUE,
            'error_text'       => $locale['uf_discord_error'],
            'regex_error_text' => $locale['uf_discord_error_1'],
            'placeholder'      => $locale['uf_discord'],
            'label_icon'       => $icon
        ) + $options;
    $user_fields = form_text('user_discord', $locale['uf_discord'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    $user_fields = array('title' => $icon.$locale['uf_discord'], 'value' => $field_value ?: '');
}
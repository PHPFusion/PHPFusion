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
defined('IN_FUSION') || exit;

$icon = "<img src='".IMAGES."user_fields/social/discord.svg' title='Discord' alt='Discord'/>";
// Display user field input
if ($profile_method == "input") {
    $options = [
            'inline'           => TRUE,
            'error_text'       => $locale['uf_discord_error'],
            'regex_error_text' => $locale['uf_discord_error_1'],
            'placeholder'      => $locale['uf_discord'],
            'label_icon'       => $icon
        ] + $options;
    $user_fields = form_text('user_discord', $locale['uf_discord'], $field_value, $options);
    // Display in profile
} else if ($profile_method == "display") {
    $user_fields = [
        'icon'  => $icon,
        'title' => $locale['uf_discord'],
        'value' => $field_value ?: ''
    ];
}

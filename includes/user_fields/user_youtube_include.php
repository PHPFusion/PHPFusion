<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_youtube_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/youtube.svg' title='YouTube' alt='YouTube'/>";
// Display user field input
if ($profile_method == "input") {
    $options = [
            'inline'      => TRUE,
            'max_length'  => 100,
            'error_text'  => $locale['uf_youtube_error'],
            'placeholder' => $locale['uf_youtube_id'],
            'label_icon'  => $icon,
        ] + $options;
    $user_fields = form_text('user_youtube', $locale['uf_youtube'], $field_value, $options);
    // Display in profile
} else if ($profile_method == "display") {
    $link = '';
    if ($field_value) {
        $field_value = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "https://www.youtube.com/user/".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? '' : "<!--noindex-->")."<a href='".$field_value."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? '' : "rel='nofollow noopener noreferrer' ")."target='_blank'>".$locale['uf_youtube']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }
    $user_fields = [
        'icon'  => $icon,
        'link'  => $link,
        'type'  => 'social',
        'title' => $locale['uf_youtube'],
        'value' => $field_value ?: ''
    ];
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_psn_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/psn.svg'/>";
// Display user field input
if ($profile_method == "input") {

    $options = array(
            'inline'           => TRUE,
            'error_text'       => $locale['uf_psn_error'],
            'regex_error_text' => $locale['uf_psn_error_1'],
            'placeholder'      => $locale['uf_psn_desc'],
            'label_icon'       => $icon
        ) + $options;

    $user_fields = form_text('user_psn', $locale['uf_psn'], $field_value, [
        'inline'      => TRUE,
        'placeholder' => $locale['uf_psn_placeholder'],
        'error_text'  => $locale['uf_psn_error'],
        'label_icon'  => $icon,
    ]);
// Display in profile
} elseif ($profile_method == "display") {
    if ($field_value) {
        $field_value = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "https://my.playstation.com/".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$field_value."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow' ")."target='_blank'>".$locale['uf_psn_desc']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }
    $user_fields = array(
        'title' => $icon.$locale['uf_psn'],
        'value' => $field_value ?: ''
    );
}

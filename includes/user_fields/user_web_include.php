<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_web_include.php
| Author: Core Development Team
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
    $options += [
        'type'   => 'url',
        // We only accept websites that start with http(s)
        'regex'  => 'http(s)?\:\/\/(.*?)',
        'inline' => TRUE,
        // TODO: Change the error text in case a value was entered but is not valid
    ];
    $user_fields = form_text('user_web', $locale['uf_web'], $field_value, $options);

    // Display in profile
} else if ($profile_method == "display") {
    $link = '';
    if ($field_value) {
        $link = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "http://".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$link."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow noopener noreferrer' ")."target='_blank'>".$locale['uf_web_001']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }
    $user_fields = [
        'link'  => $link,
        'title' => $locale['uf_web'],
        'value' => $field_value ?: ''
    ];
}

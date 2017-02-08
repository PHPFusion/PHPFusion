<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_linkedin_include.php
| Author: Digitanium
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
if ($profile_method == "input") {
    $options = array(
            'inline'           => TRUE,
            'max_length'       => 16,
            'regex'            => '[a-z](?=[\w.]{3,31}$)\w*\.?\w*',
            'regex_error_text' => $locale['uf_youtube_error_1'],
            'error_text'       => $locale['uf_youtube_error'],
            'placeholder'      => $locale['uf_youtube_id']
        ) + $options;
    $user_fields = form_text('user_youtube', "<img src='".IMAGES."user_fields/social/youtube.svg' class='m-r-5' style='width:32px'>".$locale['uf_youtube'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    if ($field_value) {
        $field_value = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "https://www.youtube.com/".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? '' : "<!--noindex-->")."<a href='".$field_value."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow' ")."target='_blank'><img src='".IMAGES."user_fields/social/youtube.svg' style='width:32px;'></a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }
    $user_fields = array('title' => $locale['uf_linkedin'], 'value' => $field_value ?: '');
}
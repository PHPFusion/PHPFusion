<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_behance_include.php
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

$icon = "<img src='".IMAGES."user_fields/social/behance.svg'/>";
// Display user field input
if ($profile_method == "input") {
    $options = array(
            'inline'      => TRUE,
            'max_length'  => 20,
            'error_text'  => $locale['uf_behance_error'],
            'placeholder' => $locale['uf_behance_id'],
            'label_icon'  => $icon,
        ) + $options;
    $user_fields = form_text('user_behance', $locale['uf_behance'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    if ($field_value) {
        $field_value = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "https://www.behance.net/".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$field_value."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow' ")."target='_blank'>".$locale['uf_behance_desc']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }
    $user_fields = array('title' => $icon.$locale['uf_behance'], 'value' => $field_value ?: '');
}

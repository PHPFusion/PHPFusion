<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_facebook_include.php
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

    $fb = new \PHPFusion\Infusions\Facebook_Connect\Facebook_Connect();

    $user_fields = $fb->displayField($field_value, $options);

    // Display in profile
} else if ($profile_method == "display") {

    $link = '';
    if ($field_value) {
        $link = !preg_match("@^http(s)?\:\/\/@i", $field_value) ? "https://www.facebook.com/".$field_value : $field_value;
        $field_value = (fusion_get_settings('index_url_userweb') ? "" : "<!--noindex-->")."<a href='".$link."' title='".$field_value."' ".(fusion_get_settings('index_url_userweb') ? "" : "rel='nofollow noopener noreferrer' ")."target='_blank'>".$locale['uf_facebook_desc']."</a>".(fusion_get_settings('index_url_userweb') ? "" : "<!--/noindex-->");
    }

    $user_fields = [
        'icon'  => $icon,
        'link'  => $link,
        'type'  => 'social',
        'title' => $locale['uf_facebook'],
        'value' => $field_value ?: ''
    ];

}

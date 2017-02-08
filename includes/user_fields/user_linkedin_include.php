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
            'error_text'       => $locale['uf_linkedin_error'],
            'regex_error_text' => $locale['uf_linkedin_error_1'],
            'placeholder'      => $locale['uf_linkedin']
        ) + $options;
    $user_fields = form_text('user_linkedin', "<img src='".IMAGES."user_fields/social/linkedin.svg' class='m-r-5' style='width:32px'>".$locale['uf_linkedin'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
    $user_fields = array('title' => $locale['uf_linkedin'], 'value' => $field_value ?: "");
}
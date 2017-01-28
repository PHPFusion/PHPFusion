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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Display user field input
if ($profile_method == "input") {
    $options = array(
        'inline' => TRUE,
        'placeholder' => $locale['uf_facebook_palceholder']
    );

    $user_fields = form_text('user_facebook', $locale['uf_facebook'], $field_value, $options);
// Display in profile
} elseif ($profile_method == "display") {
     if ($field_value) {
        $field_value = "<a href='".$field_value."' target='_blank'>".$locale['uf_facebook_link']."</a>";
    }

    $user_fields = array(
        'title' => $locale['uf_facebook'],
        'value' => $field_value ? : ''
    );
}

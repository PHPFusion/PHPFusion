<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_geo_include.php
| Author: Chan (Frederick MC Chan)
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
    $options += array('inline' => TRUE);
    $user_fields = form_geo('user_geo', $locale['uf_geo'], $field_value, $options);
} elseif ($profile_method == "display") {
    if ($field_value) {
        $address = explode('|', $field_value);
        $field_value = '';
        foreach ($address as $value) {
            $field_value .= "$value<br/>\n";
        }
    } else {
        $field_value = $locale['na'];
    }
    $user_fields = array('title' => $locale['uf_geo'], 'value' => $field_value);
}

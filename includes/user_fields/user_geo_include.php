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
defined('IN_FUSION') || exit;

// Display user field input
if ($profile_method == "input") {
    $options += ['inline' => TRUE];
    $user_fields = form_geo('user_geo', $locale['uf_geo'], $field_value, $options);
} else if ($profile_method == "display") {
    if ($field_value) {
        $address = explode('|', $field_value);
        !empty($address[2]) ? $address[2] = translate_country_names($address[2]) : "";
        $field_value = implode("<br>", $address);
    } else {
        $field_value = $locale['na'];
    }
    $user_fields = [
        'title' => $locale['uf_geo'],
        'value' => $field_value]
    ;
}

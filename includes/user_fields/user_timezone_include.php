<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_timezone_include.php
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
defined( 'IN_FUSION' ) || exit;

// Display user field input
if ($profile_method == "input") {
    $json_file = @file_get_contents( INCLUDES . 'geomap/timezones.json', FALSE );
    $timezones_json = json_decode( $json_file, TRUE );
    $timezone_array = [];
    foreach ($timezones_json as $zone => $zone_city) {
        $date = new DateTime( 'now', new DateTimeZone( $zone ) );
        $offset = $date->getOffset() / 3600;
        $timezone_array[$zone] = '(GMT' . ($offset < 0 ? $offset : '+' . $offset) . ') ' . $zone_city;
    }

    $options = [
            'options'     => $timezone_array,
            'inner_width' => '100%',
            'width'       => '100%'
        ] + $options;
    $user_fields = form_select( 'user_timezone', $locale['uf_timezone'], $field_value, $options );
    // Display in profile
} else if ($profile_method == "display") {
    if (!empty( $field_value )) {
        $date = new DateTime( 'now', new DateTimeZone( $field_value ) );
        $offset = $date->getOffset() / 3600;
        $field_value = 'GMT' . ($offset < 0 ? $offset : '+' . $offset);

        $user_fields = [
            'title' => $locale['uf_timezone'],
            'value' => $field_value
        ];
    }
}

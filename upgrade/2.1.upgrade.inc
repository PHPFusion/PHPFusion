<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 2.0.upgrade.inc
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
$inf_version = '2.1';

$array_check = [
    'download_auto_approve'    => 1
];

$settings = get_settings( 'downloads' );
foreach ( $array_check as $key => $value ) {
    // Clear old settings from core if they are there regardless of current state
    $inf_deldbrow[] = DB_SETTINGS." WHERE settings_name='$key'";
    // Insert new and old settings tables to Infusions table
    if ( !isset( $settings[ $key ] ) ) {
        $inf_insertdbrow[] = DB_SETTINGS_INF." (settings_name, settings_value, settings_inf) VALUES ('$key', '$value', 'downloads')";
    }
}

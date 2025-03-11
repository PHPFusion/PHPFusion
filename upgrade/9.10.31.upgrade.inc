<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: 9.10.31.upgrade.inc
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
$locale = fusion_get_locale( '', LOCALE.LOCALESET.'setup.php' );
$settings = fusion_get_settings();

$change_submit = [
    'submit_type' => " CHANGE submit_type submit_type CHAR(4) NOT NULL"
];

foreach ( $change_submit as $key => $value ) {
    if ( column_exists( DB_SUBMISSIONS, $key, FALSE ) ) {
        $inf_altertable[] = DB_SUBMISSIONS . $value;
    }
}

$update_settings_tbl = [
    'version' => '9.10.31',
];

$insert_settings_tbl = [
    'blaclist_site' => "http://www.google.com/"
];

foreach ( $insert_settings_tbl as $key => $value ) {
    if ( !isset( $settings[$key] ) ) {
        $inf_insertdbrow[] = DB_SETTINGS . " (settings_name, settings_value) VALUES ('" . $key . "', '" . $value . "')";
    }
}

foreach ( $update_settings_tbl as $key => $value ) {
    if ( isset( $settings[$key] ) ) {
        $inf_updatedbrow[] = DB_SETTINGS . " SET settings_value='" . $value . "' WHERE settings_name='" . $key . "'";
    }
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: 9.1.upgrade.inc
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

$update_settings_tbl = [
    'entypo'  => 0,
    'version' => '9.1',
];

foreach ($update_settings_tbl as $key => $value) {
    if (isset($settings[$key])) {
        $inf_updatedbrow[] = DB_SETTINGS." SET settings_value='$value' WHERE settings_name='$key'";;
    }
}

$insert_settings_tbl = [
    'user_name_ban' => '',
];

foreach ($insert_settings_tbl as $key => $value) {
    if (!isset($settings[$key])) {
        $inf_insertdbrow[] = DB_SETTINGS." (settings_name, settings_value) VALUES ('$key', '$value')";
    }
}

if (db_exists(DB_PREFIX.'custom_pages')) {
    if (!column_exists(DB_CUSTOM_PAGES, 'page_breaks', FALSE)) {
        $inf_altertable[] = DB_CUSTOM_PAGES." ADD page_breaks CHAR(1) NOT NULL DEFAULT '' AFTER page_status";
    }
}

if (db_exists(DB_PREFIX.'faqs')) {
    $new_icon_array = ['FQ' => '../infusions/faq/faq.svg'];
}

if (db_exists(DB_PREFIX.'polls')) {
    $new_icon_array = ['PO' => '../infusions/member_poll_panel/polls.svg'];
}

if (db_exists(DB_PREFIX.'shoutbox')) {
    $new_icon_array = ['S' => '../infusions/shoutbox/shouts.svg'];
}

foreach ($new_icon_array as $admin_rights => $icon_file) {
    $inf_updatedbrow[] = DB_ADMIN." SET admin_image='".$icon_file."' WHERE admin_rights='".$admin_rights."'";
}

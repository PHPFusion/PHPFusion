<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_downloads_panel.php
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

include_once INFUSIONS."latest_downloads_panel/templates.php";

$download_result = "SELECT td.download_id, td.download_title, tu.user_id, tu.user_name, tu.user_status
    FROM ".DB_DOWNLOADS." td
    INNER JOIN ".DB_DOWNLOAD_CATS." tc ON td.download_cat=tc.download_cat_id
    LEFT JOIN ".DB_USERS." tu ON tu.user_id = td.download_user
    ".(multilang_table("DL") ? "WHERE download_cat_language='".LANGUAGE."' AND " : "WHERE ").groupaccess('download_visibility')."
    ORDER BY download_datestamp DESC LIMIT 0,5
";

$result = dbquery($download_result);

$info = [];

$info['title'] = $locale['global_032'];
$info['theme_bullet'] = THEME_BULLET;

if (dbrows($result)) {
    while ($data = dbarray($result)) {
        $item = [
            'download_url'   => INFUSIONS."downloads/downloads.php?download_id=".$data['download_id'],
            'download_title' => $data['download_title'],
            'profile_link'   => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
        ];

        $info['item'][] = $item;
    }
} else {
    $info['no_item'] = $locale['global_033'];
}

render_latest_downloads($info);


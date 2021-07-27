<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: latest_downloads_panel.php
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
defined('IN_FUSION') || exit;

$locale = fusion_get_locale();

if (defined('DOWNLOADS_EXISTS')) {
    include_once INFUSIONS."latest_downloads_panel/templates/latest_downloads.tpl.php";

    $result = dbquery("SELECT d.download_id, d.download_title, u.user_id, u.user_name, u.user_status, u.user_avatar
        FROM ".DB_DOWNLOADS." d
        INNER JOIN ".DB_DOWNLOAD_CATS." dc ON d.download_cat=dc.download_cat_id
        LEFT JOIN ".DB_USERS." u ON u.user_id = d.download_user
        ".(multilang_table("DL") ? "WHERE ".in_group('download_cat_language', LANGUAGE)." AND " : "WHERE ").groupaccess('download_visibility')."
        ORDER BY download_datestamp DESC
        LIMIT 5
    ");

    $info = [];

    $info['title'] = $locale['global_032'];

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $item = [
                'download_url'   => INFUSIONS."downloads/downloads.php?download_id=".$data['download_id'],
                'download_title' => $data['download_title'],
                'userdata'       => [
                    'user_id'     => $data['user_id'],
                    'user_name'   => $data['user_name'],
                    'user_status' => $data['user_status'],
                    'user_avatar' => $data['user_avatar']
                ],
                'profile_link'   => profile_link($data['user_id'], $data['user_name'], $data['user_status'])
            ];

            $info['item'][] = $item;
        }
    } else {
        $info['no_item'] = $locale['global_033'];
    }

    render_latest_downloads($info);
}

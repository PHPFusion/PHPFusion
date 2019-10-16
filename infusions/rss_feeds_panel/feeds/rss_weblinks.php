<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_weblinks.php
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
require_once __DIR__.'../../../../maincore.php';

if (file_exists(INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/English/rss.php');
}

$settings = fusion_get_settings();

require_once INFUSIONS.'rss_feeds_panel/RSS.php';

if (defined('WEBLINKS_EXIST')) {
    $result = dbquery("SELECT tbl1.*, tbl2.* FROM ".DB_WEBLINK_CATS." tbl1
        RIGHT JOIN ".DB_WEBLINKS." tbl2 ON tbl1.weblink_cat_id=tbl2.weblink_cat
        WHERE ".groupaccess('weblink_visibility').(multilang_table('WL') ? " AND ".in_group('weblink_cat_language', LANGUAGE) : '')."
        ORDER BY tbl2.weblink_count DESC LIMIT 0,10
    ");

    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $rss = new RSS('weblinks', $settings['sitename'].' - '.$locale['rss_weblinks'].(multilang_table('WL') ? $locale['rss_in'].LANGUAGE : ''));

    if (dbrows($result) != 0) {
        while ($data = dbarray($result)) {
            $rss->addItem($data['weblink_name'], $settings['siteurl'].'infusions/weblinks/weblinks.php?weblink_id='.$data['weblink_id'], $data['weblink_description']);
        }
    } else {
        $rss->addItem($settings['sitename'].' - '.$locale['rss_weblinks'], $settings['siteurl'], $locale['rss_nodata']);
    }

    $rss->write();
}

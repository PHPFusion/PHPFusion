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
require_once dirname(__FILE__).'../../../../maincore.php';

if (file_exists(INFUSIONS.'rss_feeds_panel/locale/'.LANGUAGE.'.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/'.LANGUAGE.'.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/English.php');
}

$settings = fusion_get_settings();

require_once INFUSIONS.'rss_feeds_panel/RSS.php';

if (db_exists(DB_WEBLINKS) && db_exists(DB_WEBLINK_CATS)) {
    $result = dbquery("SELECT tbl1.*, tbl2.* FROM ".DB_WEBLINK_CATS." tbl1
        RIGHT JOIN ".DB_WEBLINKS." tbl2 ON tbl1.weblink_cat_id=tbl2.weblink_cat
        WHERE ".groupaccess('weblink_visibility').(multilang_table('WL') ? " AND weblink_cat_language='".LANGUAGE."'" : '')."
        ORDER BY tbl2.weblink_count DESC LIMIT 0,10
    ");

    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $rss = new RSS('weblinks', $settings['sitename'].' - '.$locale['rss_weblinks'].(multilang_table('WL') ? $locale['rss_in'].LANGUAGE : ''));

    if (dbrows($result) != 0) {
        while ($data = dbarray($result)) {
            $rss->AddItem($data['weblink_name'], $settings['siteurl'].'iinfusions/weblinks/weblinks.php?weblink_id='.$data['weblink_id'], $data['weblink_description']);
        }
    } else {
        $rss->AddItem($settings['sitename'].' - '.$locale['rss_weblinks'], $settings['siteurl'], $locale['rss_nodata']);
    }

    $rss->Write();
}

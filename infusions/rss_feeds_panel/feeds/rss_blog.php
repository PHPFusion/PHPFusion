<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_blog.php
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

if (db_exists(DB_BLOG)) {
    $result = dbquery("SELECT * FROM ".DB_BLOG."
        WHERE ".groupaccess('blog_visibility').(multilang_table('BL') ? " AND blog_language='".LANGUAGE."'" : '')."
        ORDER BY blog_datestamp DESC LIMIT 0,10
    ");

    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $rss = new RSS('blog', $settings['sitename'].' - '.$locale['rss_blog'].(multilang_table('BL') ? $locale['rss_in'].LANGUAGE : ''));

    if (dbrows($result) != 0) {
        while ($data = dbarray($result)) {
            $rss->AddItem($data['blog_subject'], $settings['siteurl'].'infusions/blog/blog.php?readmore='.$data['blog_id'], $data['blog_blog']);
        }
    } else {
        $rss->AddItem($settings['sitename'].' - '.$locale['rss_blog'], $settings['siteurl'], $locale['rss_nodata']);
    }

    $rss->Write();
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_forums.php
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

if (defined('FORUM_EXIST')) {
    $result = dbquery("SELECT f.forum_id, f.forum_name, f.forum_lastpost, f.forum_postcount,
        f.forum_threadcount, f.forum_lastuser, f.forum_access,
        t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll,
        p.post_message
        FROM ".DB_FORUMS." f
        LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id
        LEFT JOIN ".DB_FORUM_POSTS." p ON t.thread_id = p.thread_id
        ".(multilang_table('FO') ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND" : "WHERE")." f.forum_access=0 AND f.forum_type!='1' AND f.forum_type!='3' AND t.thread_hidden='0'
        GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC LIMIT 0,10
    ");

    header('Content-Type: application/rss+xml; charset='.$locale['charset']);

    $rss = new RSS('forums', $settings['sitename'].' - '.$locale['rss_forums'].(multilang_table('FO') ? $locale['rss_in'].LANGUAGE : ''));

    if (dbrows($result) != 0) {
        while ($data = dbarray($result)) {
            $rss->addItem($data['thread_subject'].' ['.$data['forum_name'].']', $settings['siteurl'].'infusions/forum/viewthread.php?forum_id='.$data['forum_id'].'&thread_id='.$data['thread_id'], $data['post_message']);
        }
    } else {
        $rss->addItem($settings['sitename'].' - '.$locale['rss_forums'], $settings['siteurl'], $locale['rss_nodata']);
    }

    $rss->write();
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_threads_panel.php
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
defined('IN_FUSION') || exit;

if (defined('FORUM_EXIST')) {
    include_once INCLUDES."infusions_include.php";
    include_once INFUSIONS."forum_threads_panel/templates.php";

    $inf_settings = get_settings('forum');
    $locale = fusion_get_locale("", FORUM_LOCALE);

    $result = dbquery("SELECT f.forum_id, f.forum_access, t.thread_id, t.thread_subject
        FROM ".DB_FORUMS." f
        LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id
        ".(multilang_table("FO") ? "WHERE ".in_group('f.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('f.forum_access')." AND f.forum_type!='1' AND f.forum_type!='3' AND t.thread_hidden='0'
        GROUP BY t.thread_id
        ORDER BY t.thread_lastpost DESC
        LIMIT 0,".$inf_settings['numofthreads']."
    ");

    $info = [];

    $info['title'] = $locale['global_020'];
    $info['latest']['label'] = $locale['global_021'];

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $item = [
                'link_url'   => FORUM."viewthread.php?thread_id=".$data['thread_id'],
                'link_title' => $data['thread_subject'],
            ];

            $info['latest']['item'][] = $item;
        }
    } else {
        $info['latest']['no_rows'] = $locale['global_023'];
    }

    $info['hottest']['label'] = $locale['global_022'];

    $timeframe = ($inf_settings['popular_threads_timeframe'] != 0 ? "thread_lastpost >= ".(time() - $inf_settings['popular_threads_timeframe']) : "");
    list($min_posts) = dbarraynum(dbquery("SELECT thread_postcount FROM ".DB_FORUM_THREADS.($timeframe ? " WHERE ".$timeframe : "")." ORDER BY thread_postcount DESC LIMIT 4,1"));
    $timeframe = ($timeframe ? " AND t.".$timeframe : "");

    $result = dbquery("SELECT tf.forum_id, t.thread_id, t.thread_subject, t.thread_postcount
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_THREADS." t USING(forum_id)
        ".(multilang_table("FO") ? "WHERE ".in_group('tf.forum_language', LANGUAGE)." AND " : "WHERE ").groupaccess('tf.forum_access')." AND tf.forum_type!='1' AND tf.forum_type!='3' AND t.thread_hidden='0' AND t.thread_postcount >= '".$min_posts."'".$timeframe."
        ORDER BY t.thread_postcount DESC, t.thread_lastpost DESC
        LIMIT 0,".$inf_settings['numofthreads']."
    ");

    if (dbrows($result)) {
        while ($data = dbarray($result)) {
            $item = [
                'link_url'   => FORUM."viewthread.php?thread_id=".$data['thread_id'],
                'link_title' => $data['thread_subject'],
                'badge'      => badge($data['thread_postcount'] - 1, ['class' => 'pull-right']),
            ];

            $info['hottest']['item'][] = $item;
        }
    } else {
        $info['hottest']['no_rows'] = $locale['global_023'];
    }
    render_threads_panel($info);
}

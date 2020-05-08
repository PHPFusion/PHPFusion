<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/participated.php
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

use PHPFusion\Infusions\Forum\Classes\ForumServer;

if (!iMEMBER) {
    redirect(FORUM.'index.php');
}
$userdata = fusion_get_userdata();
$locale = fusion_get_locale();

$this->forum_info['title'] = $locale['global_024'];
$this->forum_info['description'] = "The threads that you have participated.";
$this->forum_info['link'] = FORUM;
$this->forum_info['filter'] = ForumServer::filter()->get_FilterInfo();

$filter = ForumServer::filter()->get_filterSQL();
$base_condition = (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." p.post_author='".$userdata['user_id']."' AND t.thread_locked='0' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access');

$threads = ForumServer::thread(FALSE)->getThreadInfo(0,
    [
        "count_query" => "SELECT t.thread_id
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id
        INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id
        ".$filter['join']." WHERE $base_condition ".$filter['condition']." GROUP BY t.thread_id",

        "query" => "SELECT p.forum_id, p.thread_id, p.post_id, p.thread_id 'thread_id', p.forum_id 'forum_id', p.post_author,
        t.*, tf.* ".$filter['select']."
        FROM ".DB_FORUMS." tf
        INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id
        INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id
        ".$filter['join']."
        WHERE $base_condition ".$filter['condition']."
        GROUP BY t.thread_id ".$filter['order'],

        "debug" => FALSE,
    ] + $filter + [
        'custom_condition' => ($base_condition ? "AND $base_condition" : ""),
        'custom_join'      => 'INNER JOIN '.DB_FORUM_POSTS.' p ON p.thread_id=t.thread_id'
    ]
);
$this->forum_info = array_merge_recursive($this->forum_info, $threads);
//print_P($this->forum_info);
//showBenchmark(TRUE, 1, true);

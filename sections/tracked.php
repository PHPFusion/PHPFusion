<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/tracked.php
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
if (!iMEMBER) {
    redirect(FORUM.'index.php');
}
$userdata = fusion_get_userdata();
$locale = fusion_get_locale();

if (isset($_GET['delete_track']) && isnum($_GET['delete_track']) && dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$_GET['delete_track']."' AND notify_user='".$userdata['user_id']."'")) {
    $result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=".$_GET['delete_track']." AND notify_user=".$userdata['user_id']);
    redirect(FORUM.'index.php?section=tracked');
}

$this->forum_info['title'] = $locale['global_056'];
$this->forum_info['description'] = "The threads that you are currently tracking.";
$this->forum_info['link'] = FORUM;
$this->forum_info['filter'] = \PHPFusion\Infusions\Forum\Classes\Forum_Server::filter()->get_FilterInfo();

$filter = \PHPFusion\Infusions\Forum\Classes\Forum_Server::filter()->get_filterSQL();

$base_condition = "tn.notify_user='".$userdata['user_id']."' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access');

$threads = \PHPFusion\Infusions\Forum\Classes\Forum_Server::thread(FALSE)->get_forum_thread(0,
    [
        'count_query' => "SELECT tn.thread_id
        FROM ".DB_FORUM_THREAD_NOTIFY." tn
        INNER JOIN ".DB_FORUM_THREADS." t ON tn.thread_id = t.thread_id
        INNER JOIN ".DB_FORUMS." tf ON t.forum_id = tf.forum_id
        ".$filter['join']."
        WHERE $base_condition ".$filter['condition']." GROUP BY t.thread_id",

        'query' => "SELECT
        tn.thread_id, tn.notify_datestamp, tn.notify_user, tn.thread_id 'track_button',
        ttc.forum_id AS forum_cat_id, ttc.forum_name AS forum_cat_name,
        t.*, tf.* ".$filter['select']."
        FROM ".DB_FORUM_THREAD_NOTIFY." tn
        INNER JOIN ".DB_FORUM_THREADS." t ON tn.thread_id = t.thread_id
        INNER JOIN ".DB_FORUMS." tf ON t.forum_id = tf.forum_id
        LEFT JOIN ".DB_FORUMS." ttc ON ttc.forum_id = tf.forum_cat
        ".$filter['join']."
        WHERE $base_condition ".$filter['condition']."  
        GROUP BY tn.thread_id
        ".$filter['order'],

        "debug" => FALSE,
    ] + $filter + [
        'custom_condition' => ($base_condition ? "AND $base_condition" : ""),
        'custom_join'      => "INNER JOIN ".DB_FORUM_THREAD_NOTIFY." tn ON tn.thread_id=t.thread_id"
    ]
);

$this->forum_info = array_merge_recursive($this->forum_info, $threads);
//showBenchmark(TRUE, '1', TRUE);

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum/sections/unsolved.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

$locale = fusion_get_locale();

$this->forum_info['title'] = $locale['global_028'];

$this->forum_info['link'] = FORUM;

$this->forum_info['description'] = "The questions in the community forum that has no answers.";

$this->forum_info['filter'] = ForumServer::filter()->get_FilterInfo();

$filter = ForumServer::filter()->get_filterSQL();

$base_condition = (multilang_table("FO") ? in_group('tf.forum_language', LANGUAGE)." AND " : "")." tf.forum_type='4' AND t.thread_answered='0' AND t.thread_locked='0' AND t.thread_hidden='0' AND ".groupaccess('tf.forum_access');

$threads = ForumServer::thread(FALSE)->getThreadInfo(0,
    [
        "count_query" => "SELECT t.thread_id  FROM ".DB_FORUM_THREADS." t   INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        ".$filter['join']." WHERE $base_condition ".$filter['condition']." GROUP BY t.thread_id",
        "query" => "SELECT t.*, tf.* ".$filter['select']."  FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
        ".$filter['join']."
        WHERE $base_condition ".$filter['condition']."
        GROUP BY t.thread_id ".$filter['order'],
        "debug" => FALSE,
    ] + $filter + [
        'custom_condition' => ($base_condition ? "AND $base_condition" : ""),
    ]
);

$this->forum_info = array_merge_recursive($this->forum_info, $threads);

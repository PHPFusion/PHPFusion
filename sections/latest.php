<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/latest.php
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
$userdata = fusion_get_userdata();
$locale = fusion_get_locale();
$this->forum_info['title'] = $locale['global_021'];
$this->forum_info['description'] = "The latest threads in the community forum.";
$this->forum_info['link'] = FORUM;
$this->forum_info['filter'] = \PHPFusion\Infusions\Forum\Classes\Forum_Server::filter()->get_FilterInfo();
$filter = \PHPFusion\Infusions\Forum\Classes\Forum_Server::filter()->get_filterSQL();

$threads = \PHPFusion\Infusions\Forum\Classes\Forum_Server::thread(FALSE)->getThreadInfo(0,
    [
        "count_query" => "SELECT t.thread_id
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        ".$filter['join']."
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." t.thread_hidden=0 AND ".groupaccess('tf.forum_access')." ".$filter['condition']." GROUP BY t.thread_id",

        "query" => "SELECT 
        t.*, tf.* ".$filter['select']."
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
        ".$filter['join']."      
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ")." ".groupaccess('tf.forum_access')." ".$filter['condition']."
        GROUP BY t.thread_id ".$filter['order'],

        "debug" => FALSE
    ] + $filter
);

$this->forum_info = array_merge_recursive($this->forum_info, $threads);

//showBenchmark(TRUE, '1', TRUE);

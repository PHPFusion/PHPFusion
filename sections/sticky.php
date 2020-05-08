<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/sections/sticky.php
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

$userdata = fusion_get_userdata();
$locale = fusion_get_locale();
$this->forum_info['title'] = 'Sticky Threads';
$this->forum_info['description'] = "The latest sticky threads in the community forum.";
$this->forum_info['link'] = FORUM;
$this->forum_info['filter'] = ForumServer::filter()->get_FilterInfo();
$filter = ForumServer::filter()->get_filterSQL();

$threads = ForumServer::thread( FALSE )->getThreadInfo( 0,
    [
        "count_query" => "SELECT t.thread_id
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id = t.forum_id
        ".$filter['join']."
        ".( multilang_table( "FO" ) ? "WHERE thread_sticky=1 AND ".in_group( 'tf.forum_language', LANGUAGE )." AND " : "WHERE " )." t.thread_hidden=0 AND ".groupaccess( 'tf.forum_access' )." ".$filter['condition']." GROUP BY t.thread_id",

        "query" => "SELECT
        t.*, tf.* ".$filter['select']."
        FROM ".DB_FORUM_THREADS." t
        INNER JOIN ".DB_FORUMS." tf ON tf.forum_id=t.forum_id
        ".$filter['join']."
        ".( multilang_table( "FO" ) ? "WHERE thread_sticky=1 AND ".in_group( 'tf.forum_language', LANGUAGE )." AND " : "WHERE " )." ".groupaccess( 'tf.forum_access' )." ".$filter['condition']."
        GROUP BY t.thread_id ".$filter['order'],

        "debug" => FALSE
    ] + $filter
);

$this->forum_info = array_merge_recursive( $this->forum_info, $threads );

//showBenchmark(TRUE, '1', TRUE);

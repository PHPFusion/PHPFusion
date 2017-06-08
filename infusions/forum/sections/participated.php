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

if (!iMEMBER) {
    redirect(FORUM.'index.php');
}

$userdata = fusion_get_userdata();
$locale = fusion_get_locale();
$time_sql = '';
if (!empty($_POST['filter_date'])) {
    $time_filter = form_sanitizer($_POST['filter_date'], '', 'filter_date');
    $time_filter = (TIME - ($time_filter * 24 * 3600));
    $time_sql = "t.thread_lastpost < '$time_filter' AND ";
}
$opts = array(
    '0'   => $locale['forum_p999'],
    '1'   => $locale['forum_p001'],
    '7'   => $locale['forum_p007'],
    '14'  => $locale['forum_p014'],
    '30'  => $locale['forum_p030'],
    '90'  => $locale['forum_p090'],
    '180' => $locale['forum_p180'],
    '365' => $locale['forum_3015']
);
$this->forum_info['threads_time_filter'] = openform('filter_form', 'post', INFUSIONS."forum/index.php?section=participated").
    form_select('filter_date', $locale['forum_0009'], (isset($_POST['filter_date']) && $_POST['filter_date'] ? $_POST['filter_date'] : 0), array(
        'options' => $opts,
        'width'   => '300px',
        'class'   => 'pull-left m-r-10',
        'stacked' => form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default')),
    )).closeform();

$threads = \PHPFusion\Forums\ForumServer::thread(FALSE)->get_forum_thread(0,
    array(
        "count_query" => "SELECT t.thread_id
        FROM ".DB_FORUMS." tf        
        INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id 
        INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id                       
        ".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").$time_sql." p.post_author='".$userdata['user_id']."' AND ".groupaccess('tf.forum_access')." GROUP BY t.thread_id",

        "query" => "SELECT p.forum_id, p.thread_id, p.post_id, p.thread_id 'thread_id', p.forum_id 'forum_id',
        t.thread_subject, t.thread_author, t.thread_lastuser, t.thread_lastpost, t.thread_lastpostid, t.thread_postcount,
        t.thread_locked, t.thread_sticky, t.thread_poll, t.thread_postcount, t.thread_views,             
		tf.forum_name, tf.forum_access, tf.forum_type
		FROM ".DB_FORUMS." tf
		INNER JOIN ".DB_FORUM_POSTS." p ON p.forum_id=tf.forum_id
		INNER JOIN ".DB_FORUM_THREADS." t ON p.thread_id=t.thread_id AND t.forum_id=tf.forum_id   		
		".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND " : "WHERE ").$time_sql." p.post_author='".$userdata['user_id']."' AND ".groupaccess('tf.forum_access')."
		GROUP BY p.thread_id ORDER BY t.thread_sticky DESC, t.thread_lastpost DESC"
    )
);
$this->forum_info = array_merge_recursive($this->forum_info, $threads);
//showBenchmark(TRUE);
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
$this->forum_info['threads_time_filter'] = openform('filter_form', 'post', INFUSIONS."forum/index.php?section=tracked").
    form_select('filter_date', $locale['forum_0009'], (isset($_POST['filter_date']) && $_POST['filter_date'] ? $_POST['filter_date'] : 0), array(
        'options' => $opts,
        'width'   => '300px',
        'class'   => 'pull-left m-r-10',
        'stacked' => form_button('go', $locale['go'], $locale['go'], array('class' => 'btn-default')),
    )).closeform();

$threads = \PHPFusion\Forums\ForumServer::thread(FALSE)->get_forum_thread(0,
    array(
        'count_query' => "SELECT tn.thread_id 
        FROM ".DB_FORUM_THREAD_NOTIFY." tn
        INNER JOIN ".DB_FORUM_THREADS." t ON tn.thread_id = t.thread_id
        INNER JOIN ".DB_FORUMS." tf ON t.forum_id = tf.forum_id
        WHERE ".$time_sql." tn.notify_user='".$userdata['user_id']."' AND ".groupaccess('forum_access')." AND t.thread_hidden='0'
        ",
        'query'       => "SELECT tf.forum_id, tf.forum_name, tf.forum_access, tf.forum_type, tf.forum_mods, 
        tn.thread_id, tn.notify_datestamp, tn.notify_user, tn.thread_id 'track_button',
        ttc.forum_id AS forum_cat_id, ttc.forum_name AS forum_cat_name,                
        t.thread_subject, t.forum_id, t.thread_lastpost, t.thread_lastpostid, t.thread_lastuser, t.thread_postcount, t.thread_views, t.thread_locked,
        t.thread_author, t.thread_poll, t.thread_sticky                                
        FROM ".DB_FORUM_THREAD_NOTIFY." tn
        INNER JOIN ".DB_FORUM_THREADS." t ON tn.thread_id = t.thread_id
        INNER JOIN ".DB_FORUMS." tf ON t.forum_id = tf.forum_id
        LEFT JOIN ".DB_FORUMS." ttc ON ttc.forum_id = tf.forum_cat
        WHERE tn.notify_user='".$userdata['user_id']."' AND t.thread_hidden='0' AND $time_sql ".groupaccess('tf.forum_access')."
        GROUP BY tn.thread_id
        ORDER BY tn.notify_datestamp DESC"
    )
);

$this->forum_info = array_merge_recursive($this->forum_info, $threads);
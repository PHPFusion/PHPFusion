<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: tracked.php
| Author: Hien (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!iMEMBER) {	redirect(FORUM.'index.php'); }

if (isset($_GET['delete']) && isnum($_GET['delete']) && dbcount("(thread_id)", DB_FORUM_THREAD_NOTIFY, "thread_id='".$_GET['delete']."' AND notify_user='".$userdata['user_id']."'")) {
	$result = dbquery("DELETE FROM ".DB_FORUM_THREAD_NOTIFY." WHERE thread_id=".$_GET['delete']." AND notify_user=".$userdata['user_id']);
	redirect(FUSION_SELF);
}

// xss injection
$result = dbquery("SELECT tn.thread_id FROM ".DB_FORUM_THREAD_NOTIFY." tn
            INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
            INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
            WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'");
$rows = dbrows($result);
if (!isset($_GET['rowstart']) or !isnum($_GET['rowstart']) or $_GET['rowstart'] > $rows) {
	$_GET['rowstart'] = 0;
}
$info['post_rows'] = $rows;

if ($rows) {
	$info['page_nav'] = makePageNav($_GET['rowstart'], 10, $rows, 3, FORUM."?");
	$result = dbquery("
                SELECT tf.forum_id, tf.forum_name, tf.forum_access, tf.forum_type, tn.thread_id, tn.notify_datestamp, tn.notify_user,
                ttc.forum_id AS forum_cat_id, ttc.forum_name AS forum_cat_name, tp.post_datestamp,
                tt.thread_subject, tt.forum_id, tt.thread_lastpost, tt.thread_lastpostid, tt.thread_lastuser, tt.thread_postcount, tt.thread_views, tt.thread_locked,
                tt.thread_author, tt.thread_poll, tt.thread_sticky,
                uc.user_id AS s_user_id, uc.user_name AS author_name, uc.user_status AS author_status, uc.user_avatar AS author_avatar,
                u.user_id, u.user_name as last_user_name, u.user_status as last_user_status, u.user_avatar as last_user_avatar,
                count(v.post_id) AS vote_count
                FROM ".DB_FORUM_THREAD_NOTIFY." tn
                INNER JOIN ".DB_FORUM_THREADS." tt ON tn.thread_id = tt.thread_id
                INNER JOIN ".DB_FORUMS." tf ON tt.forum_id = tf.forum_id
                LEFT JOIN ".DB_FORUMS." ttc ON ttc.forum_id = tf.forum_cat
                LEFT JOIN ".DB_USERS." uc ON tt.thread_author = uc.user_id
                LEFT JOIN ".DB_USERS." u ON tt.thread_lastuser = u.user_id
                LEFT JOIN ".DB_FORUM_POSTS." tp ON tt.thread_id = tp.thread_id
                LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = tt.thread_id AND tp.post_id = v.post_id
                WHERE tn.notify_user=".$userdata['user_id']." AND ".groupaccess('forum_access')." AND tt.thread_hidden='0'
                GROUP BY tn.thread_id
                ORDER BY tn.notify_datestamp DESC
                LIMIT ".$_GET['rowstart'].",10
            ");
	$i = 0;
	while ($data = dbarray($result)) {
		// stop tracking button
		$data['track_button'] = array('link'=>FORUM."index.php?section=tracked&amp;delete=".$data['thread_id'], 'name'=>$locale['global_058']);
		// push
		$info['item'][$data['thread_id']] = $data;
	}
}

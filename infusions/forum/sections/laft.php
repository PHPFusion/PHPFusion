<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: laft.php
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
add_to_title($locale['global_200'].$locale['global_042']);

$_GET['rowstart'] = 0;
$result = dbquery("SELECT tt.*, tf.*, tp.post_id, tp.post_datestamp,
			u.user_id, u.user_name as last_user_name, u.user_status as last_user_status, u.user_avatar as last_user_avatar,
			uc.user_id AS s_user_id, uc.user_name AS author_name, uc.user_status AS author_status, uc.user_avatar AS author_avatar,
			count(v.post_id) AS vote_count
			FROM ".DB_FORUM_THREADS." tt
			INNER JOIN ".DB_FORUMS." tf ON (tt.forum_id=tf.forum_id)
			LEFT JOIN ".DB_FORUM_POSTS." tp on (tt.thread_lastpostid = tp.post_id)
			LEFT JOIN ".DB_USERS." u ON u.user_id=tt.thread_lastuser
			LEFT JOIN ".DB_USERS." uc ON uc.user_id=tt.thread_author
			LEFT JOIN ".DB_FORUM_VOTES." v ON v.thread_id = tt.thread_id AND tp.post_id = v.post_id
			".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")."
			".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
			".(isset($_POST['filter']) && $_POST['filter'] ? "AND tt.thread_lastpost < '".(time()-($_POST['filter']*24*3600))."'" : '')."
			GROUP BY thread_id ORDER BY tt.thread_lastpost LIMIT ".$_GET['rowstart'].", ".$settings['threads_per_page']);
// link also need to change
$info['post_rows'] = dbrows($result);
if (dbrows($result) > 0) {
	while ($data = dbarray($result)) {
		// opt for moderators.
		/* Show moderators */
		$moderators = '';
		if ($data['forum_mods']) {
			$_mgroup = explode('.', $data['forum_mods']);
			if (!empty($_mgroup)) {
				foreach ($_mgroup as $mod_group) {
					if ($moderators) $moderators .= ", ";
					$moderators .= $mod_group < 101 ? "<a href='".BASEDIR."profile.php?group_id=".$mod_group."'>".getgroupname($mod_group)."</a>" : getgroupname($mod_group);
				}
			}
		}
		$data['moderators'] = $moderators;
		// push
		$info['item'][$data['thread_id']] = $data;
	}
}



<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_threads_list_panel.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

global $lastvisited;

if (!isset($lastvisited) || !isnum($lastvisited)) { $lastvisited = time(); }

$data = dbarray(dbquery(
	"SELECT tt.thread_lastpost
	FROM ".DB_FORUMS." tf
	INNER JOIN ".DB_THREADS." tt ON tf.forum_id = tt.forum_id
	WHERE ".groupaccess('tf.forum_access')." AND thread_hidden='0'
	ORDER BY tt.thread_lastpost DESC LIMIT ".($settings['numofthreads']-1).", ".$settings['numofthreads']
));

$timeframe = empty($data['thread_lastpost']) ? 0 : $data['thread_lastpost'];

$result = dbquery(
	"SELECT tt.thread_id, tt.thread_subject, tt.thread_views, tt.thread_lastuser, tt.thread_lastpost,
	tt.thread_poll, tf.forum_id, tf.forum_name, tf.forum_access, tt.thread_lastpostid, tt.thread_postcount, tu.user_id, tu.user_name,
	tu.user_status
	FROM ".DB_THREADS." tt
	INNER JOIN ".DB_FORUMS." tf ON tt.forum_id=tf.forum_id
	INNER JOIN ".DB_USERS." tu ON tt.thread_lastuser=tu.user_id
	WHERE ".groupaccess('tf.forum_access')." AND tt.thread_lastpost >= ".$timeframe." AND tt.thread_hidden='0'
	ORDER BY tt.thread_lastpost DESC LIMIT 0,".$settings['numofthreads']
);

if (dbrows($result)) {
	$i = 0;
	opentable($locale['global_040']);
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	echo "<td class='tbl2'>&nbsp;</td>\n";
	echo "<td width='100%' class='tbl2'><strong>".$locale['global_044']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong>".$locale['global_045']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong>".$locale['global_046']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong>".$locale['global_047']."</strong></td>\n";
	echo "</tr>\n";
	while ($data = dbarray($result)) {
		$row_color = ($i % 2 == 0 ? "tbl1" : "tbl2");
		echo "<tr>\n<td class='".$row_color."'>";
		if ($data['thread_lastpost'] > $lastvisited) {
			$thread_match = $data['thread_id']."\|".$data['thread_lastpost']."\|".$data['forum_id'];
			if (iMEMBER && ($data['thread_lastuser'] == $userdata['user_id'] || preg_match("(^\.{$thread_match}$|\.{$thread_match}\.|\.{$thread_match}$)", $userdata['user_threads']))) {
				echo "<img src='".get_image("folder")."' alt='' />";
			} else {
				echo "<img src='".get_image("foldernew")."' alt='' />";
			}
		} else {
			echo "<img src='".get_image("folder")."' alt='' />";
		}
		if ($data['thread_poll']) {
			$thread_poll = "<span class='small' style='font-weight:bold'>[".$locale['global_051']."]</span> ";
		} else {
			$thread_poll = "";
		}
		echo "</td>\n";
		echo "<td width='100%' class='".$row_color."'>".$thread_poll."<a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['thread_lastpostid']."#post_".$data['thread_lastpostid']."' title='".$data['thread_subject']."'>".trimlink($data['thread_subject'], 30)."</a><br />\n".$data['forum_name']."</td>\n";
		echo "<td width='1%' class='".$row_color."' style='text-align:center;white-space:nowrap'>".$data['thread_views']."</td>\n";
		echo "<td width='1%' class='".$row_color."' style='text-align:center;white-space:nowrap'>".($data['thread_postcount']-1)."</td>\n";
		echo "<td width='1%' class='".$row_color."' style='text-align:center;white-space:nowrap'>".profile_link($data['thread_lastuser'], $data['user_name'], $data['user_status'])."<br />\n".showdate("forumdate", $data['thread_lastpost'])."</td>\n";
		echo "</tr>\n";
		$i++;
	}
	echo "</table>\n";
	if (iMEMBER) {
		echo "<div class='tbl1' style='text-align:center'><a href='".INFUSIONS."forum_threads_list_panel/my_threads.php'>".$locale['global_041']."</a> ::\n";
		echo "<a href='".INFUSIONS."forum_threads_list_panel/my_posts.php'>".$locale['global_042']."</a> ::\n";
		echo "<a href='".INFUSIONS."forum_threads_list_panel/new_posts.php'>".$locale['global_043']."</a>";
		if($settings['thread_notify']) {
			echo " ::\n<a href='".INFUSIONS."forum_threads_list_panel/my_tracked_threads.php'>".$locale['global_056']."</a>";
		}
		echo "</div>\n";
	}
	closetable();
}
?>

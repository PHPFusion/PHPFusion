<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: new_posts.php
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
require_once "../../maincore.php";
require_once THEMES."templates/header.php";

if (!iMEMBER) { redirect("../../index.php"); }

if (!isset($lastvisited) || !isnum($lastvisited)) $lastvisited = time();

add_to_title($locale['global_200'].$locale['global_043']);

opentable($locale['global_043']);
$result = dbquery(
	"SELECT tp.post_id FROM ".DB_POSTS." tp
	LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
	LEFT JOIN ".DB_THREADS." tt ON tp.thread_id = tt.thread_id
	WHERE ".groupaccess('tf.forum_access')." AND tp.post_hidden='0' AND tt.thread_hidden='0' AND (tp.post_datestamp > ".$lastvisited." OR tp.post_edittime > ".$lastvisited.")"
);
$rows = dbrows($result);
$threads = 0;
if ($rows) {
	if (!isset($_GET['rowstart']) || !isnum($_GET['rowstart'])) { $_GET['rowstart'] = 0; }
	$result = dbquery(
		"SELECT tp.forum_id, tp.thread_id, tp.post_id, tp.post_author, IF(tp.post_datestamp>tp.post_edittime, tp.post_datestamp, tp.post_edittime) AS post_timestamp,
		tf.forum_name, tf.forum_access, tt.thread_subject, tu.user_id, tu.user_name, tu.user_status
		FROM ".DB_POSTS." tp
		LEFT JOIN ".DB_FORUMS." tf ON tp.forum_id = tf.forum_id
		LEFT JOIN ".DB_THREADS." tt ON tp.thread_id = tt.thread_id
		LEFT JOIN ".DB_USERS." tu ON tp.post_author = tu.user_id
		WHERE ".groupaccess('tf.forum_access')." AND tp.post_hidden='0' AND tt.thread_hidden='0' AND (tp.post_datestamp > '".$lastvisited."' OR tp.post_edittime > '".$lastvisited."')
		GROUP BY tp.thread_id
		ORDER BY post_timestamp DESC LIMIT ".$_GET['rowstart'].",20"
	);
	$i = 0;
	echo "<table cellpadding='0' cellspacing='1' width='100%' class='tbl-border'>\n<tr>\n";
	echo "<td width='1%' class='tbl2' style='white-space:nowrap'><strong>".$locale['global_048']."</strong></td>\n";
	echo "<td class='tbl2'><strong>".$locale['global_044']."</strong></td>\n";
	echo "<td width='1%' class='tbl2' style='text-align:center;white-space:nowrap'><strong>".$locale['global_050']."</strong></td>\n";
	echo "</tr>\n";
	$threads = dbrows($result);
	while ($data = dbarray($result)) {
		if ($i % 2 == 0) { $row_color = "tbl1"; } else { $row_color = "tbl2"; }
		echo "<tr>\n";
		echo "<td width='1%' class='".$row_color."' style='white-space:nowrap'>".$data['forum_name']."</td>\n";
		echo "<td class='".$row_color."'><a href='".BASEDIR."forum/viewthread.php?thread_id=".$data['thread_id']."&amp;pid=".$data['post_id']."#post_".$data['post_id']."'>".$data['thread_subject']."</a></td>\n";
		echo "<td width='1%' class='".$row_color."' style='text-align:center;white-space:nowrap'>".profile_link($data['post_author'], $data['user_name'], $data['user_status'])."<br />\n".showdate("forumdate",$data['post_timestamp'])."</td>\n";
		echo "</tr>\n";
		$i++;
	}
	echo "<tr>\n<td align='center' colspan='4' class='tbl1'>".sprintf($locale['global_055'], $rows, $threads)."</td>\n</tr>\n</table>\n";
} else {
	echo "<div style='text-align:center'><br />".sprintf($locale['global_055'], 0, 0)."<br /><br /></div>\n";
}
closetable();
if ($threads > 20) { echo "<div align='center' style='margin-top:5px;'>\n".makepagenav($_GET['rowstart'], 20, $threads, 3)."\n</div>\n"; }

require_once THEMES."templates/footer.php";
?>
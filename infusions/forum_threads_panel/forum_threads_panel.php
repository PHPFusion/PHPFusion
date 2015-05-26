<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_threads_panel.php
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

openside($locale['global_020']);
echo "<div class='side-label'><strong>".$locale['global_021']."</strong></div>\n";

$result = dbquery("SELECT f.forum_id, f.forum_access, t.thread_id, t.thread_subject
	FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id 
	".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND f.forum_type!='1' AND f.forum_type!='3' AND t.thread_hidden='0' 
	GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC LIMIT ".$settings['numofthreads']."");


if (dbrows($result)) {
	while ($data = dbarray($result)) {
			echo "<a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".trimlink($data['thread_subject'], 18)."' class='side'>".trimlink($data['thread_subject'], 18)." <i class='fa fa-external-link-square'></i></a><br />\n";
	}
} else {
	echo "<div style='text-align:center'>".$locale['global_023']."</div>\n";
}
echo "<div class='side-label'><strong>".$locale['global_022']."</strong></div>\n";
$timeframe = ($settings['popular_threads_timeframe'] != 0 ? "thread_lastpost >= ".(time()-$settings['popular_threads_timeframe']) : "");
list($min_posts) = dbarraynum(dbquery("SELECT thread_postcount FROM ".DB_FORUM_THREADS.($timeframe ? " WHERE ".$timeframe : "")." ORDER BY thread_postcount DESC LIMIT 4,1"));
$timeframe = ($timeframe ? " AND t.".$timeframe : "");

	$result = dbquery("
	SELECT tf.forum_id, t.thread_id, t.thread_subject, t.thread_postcount
	FROM ".DB_FORUMS." tf
	INNER JOIN ".DB_FORUM_THREADS." t USING(forum_id)
	".(multilang_table("FO") ? "WHERE tf.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('tf.forum_access')." AND tf.forum_type!='1' AND tf.forum_type!='3' AND t.thread_hidden='0' AND t.thread_postcount >= '".$min_posts."'".$timeframe."
	ORDER BY t.thread_postcount DESC, t.thread_lastpost DESC LIMIT ".$settings['numofthreads']."");

if (dbrows($result) != 0) {
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
	while ($data = dbarray($result)) {
		echo "<tr>\n<td><a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."' class='side'>".trimlink($data['thread_subject'], 18)."<i class='fa fa-external-link-square'></i></a></td>\n";
		echo "<td align='right' class='side'>[".($data['thread_postcount']-1)."]</td>\n</tr>\n";
	}
	echo "</table>\n";
} else {
	echo "<div style='text-align:center'>".$locale['global_023']."</div>\n";
}
closeside();

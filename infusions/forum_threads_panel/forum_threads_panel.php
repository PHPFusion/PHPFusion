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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
openside($locale['global_020']);
echo "<div class='side-label'><strong>".$locale['global_021']."</strong></div>\n";
$result = dbquery("SELECT	f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_mods, f.forum_lastpost, f.forum_postcount,
	f.forum_threadcount, f.forum_lastuser, f.forum_access, f2.forum_name AS forum_cat_name, f2.forum_description AS forum_cat_description,
	t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll
	FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
	LEFT JOIN ".DB_THREADS." t ON f.forum_id = t.forum_id AND f.forum_lastpostid=t.thread_lastpostid
	".(multilang_table("FO") ? "WHERE f2.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND f.forum_type !='1' AND f.forum_type !='3' AND t.thread_hidden='0'
	GROUP BY thread_id ORDER BY t.thread_lastpost LIMIT ".$settings['numofthreads']."");
if (dbrows($result)) {
	while ($data = dbarray($result)) {
		$itemsubject = trimlink($data['thread_subject'], 23);
		echo THEME_BULLET." <a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."' class='side'>$itemsubject</a><br />\n";
	}
} else {
	echo "<div style='text-align:center'>".$locale['global_023']."</div>\n";
}
echo "<div class='side-label'><strong>".$locale['global_022']."</strong></div>\n";
$timeframe = ($settings['popular_threads_timeframe'] != 0 ? "thread_lastpost >= ".(time()-$settings['popular_threads_timeframe']) : "");
list($min_posts) = dbarraynum(dbquery("SELECT thread_postcount FROM ".DB_THREADS.($timeframe ? " WHERE ".$timeframe : "")." ORDER BY thread_postcount DESC LIMIT 4,1"));
$timeframe = ($timeframe ? " AND tt.".$timeframe : "");
$result = dbquery("SELECT	f.forum_id, f.forum_cat, f.forum_name, f.forum_description, f.forum_mods, f.forum_lastpost, f.forum_postcount,
	f.forum_threadcount, f.forum_lastuser, f.forum_access, f2.forum_name AS forum_cat_name, f2.forum_description AS forum_cat_description,
	t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll
	FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUMS." f2 ON f.forum_cat = f2.forum_id
	LEFT JOIN ".DB_THREADS." t ON f.forum_id = t.forum_id AND f.forum_lastpostid=t.thread_lastpostid
	".(multilang_table("FO") ? "WHERE f2.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND t.thread_postcount >= '".$min_posts."'".$timeframe." AND f.forum_type!='1' AND f.forum_type !='3' AND t.thread_hidden='0'
	GROUP BY thread_id ORDER BY t.thread_lastpost LIMIT ".$settings['numofthreads']."");
if (dbrows($result) != 0) {
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
	while ($data = dbarray($result)) {
		$itemsubject = trimlink($data['thread_subject'], 20);
		echo "<tr>\n<td class='side-small'>".THEME_BULLET." <a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."' class='side'>$itemsubject</a></td>\n";
		echo "<td align='right' class='side-small'>[".($data['thread_postcount']-1)."]</td>\n</tr>\n";
	}
	echo "</table>\n";
} else {
	echo "<div style='text-align:center'>".$locale['global_023']."</div>\n";
}
closeside();
?>
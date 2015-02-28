<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
$result = dbquery("
	SELECT tt.forum_id, tt.thread_id, tt.thread_subject, tt.thread_lastpost FROM ".DB_THREADS." tt
	INNER JOIN ".DB_FORUMS." tf ON tt.forum_id=tf.forum_id
	WHERE ".groupaccess('tf.forum_access')." AND tt.thread_hidden='0'
	ORDER BY thread_lastpost DESC LIMIT 5
");
if (dbrows($result)) {
	while($data = dbarray($result)) {
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

$result = dbquery("
	SELECT tf.forum_id, tt.thread_id, tt.thread_subject, tt.thread_postcount
	FROM ".DB_FORUMS." tf
	INNER JOIN ".DB_THREADS." tt USING(forum_id)
	WHERE ".groupaccess('tf.forum_access')." AND tt.thread_postcount >= '".$min_posts."'".$timeframe." AND tt.thread_hidden='0'
	ORDER BY thread_postcount DESC, thread_lastpost DESC LIMIT 5
");
if (dbrows($result) != 0) {
	echo "<table cellpadding='0' cellspacing='0' width='100%'>\n";
	while($data = dbarray($result)) {
		$itemsubject = trimlink($data['thread_subject'], 20);
		echo "<tr>\n<td class='side-small'>".THEME_BULLET." <a href='".FORUM."viewthread.php?thread_id=".$data['thread_id']."' title='".$data['thread_subject']."' class='side'>$itemsubject</a></td>\n";
		echo "<td align='right' class='side-small'>[".($data['thread_postcount'] - 1)."]</td>\n</tr>\n";
	}
	echo "</table>\n";
} else {
	echo "<div style='text-align:center'>".$locale['global_023']."</div>\n";
}
closeside();
?>
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_forum.php
| Author: Robert Gaudyn (Wooya)
| Co-Author: Joakim Falk (Domi)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."../../../../maincore.php";

header('Content-Type: application/rss+xml; charset='.$locale['charset'].'');

if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
	include INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php";
} else {
	include INFUSIONS."rss_feeds_panel/locale/English.php";
}

$result = dbquery("SELECT tf.*, tt.* FROM ".DB_FORUMS." tf
INNER JOIN ".DB_POSTS." tt USING(forum_id)
WHERE ".groupaccess('forum_access').(multilang_table("FO") ? " AND tf.forum_language='".LANGUAGE."'" : "")."
ORDER BY post_datestamp DESC LIMIT 0,10");

	echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n\n";
	echo "<rss version=\"2.0\">\n\n <channel>\n";

if (dbrows($result) != 0) {

echo "<title>".$settings['sitename'].$locale['rss001'].(multilang_table("FO") ? " ".$locale['rss007']." ".LANGUAGE:"")."</title>\n<link>".$settings['siteurl']."</link>\n";
echo "<description>".$settings['description']."</description>\n";

while ($row=dbarray($result)) {
	$rsid = intval($row['post_id']);
	$rtitle = $row['post_subject'];
	$description = stripslashes(nl2br($row['post_message']));
	$description = strip_tags($description, "<a><p><br /><br /><hr />");
   echo "<item>\n";
   echo "<title>".htmlspecialchars($rtitle)." [ ".$row['forum_name']." ] </title>\n";
   echo "<link>".$settings['siteurl']."forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$rsid."</link>\n";
   echo "<description>".htmlspecialchars($description)."</description>\n";
   echo "</item>\n";
}
} else {
	echo "<title>".$settings['sitename'].$locale['rss004']."</title>\n
	<link>".$settings['siteurl']."</link>\n
	<description>".$locale['rss008']."</description>\n";
}
echo "</channel></rss>";
?>
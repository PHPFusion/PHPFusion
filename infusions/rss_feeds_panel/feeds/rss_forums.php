<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_forum.php
| Author: Robert Gaudyn (Wooya)
| Co-Author: Joakim Falk (Falk)
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

$settings = fusion_get_settings();
if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
    $locale += fusion_get_locale("", INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php");
} else {
    $locale += fusion_get_locale("", INFUSIONS."rss_feeds_panel/locale/English.php");
}
header('Content-Type: application/rss+xml; charset='.$locale['charset'].'');


if (db_exists(DB_FORUM_POSTS) && db_exists(DB_FORUMS)) {
    $result = dbquery("SELECT f.forum_id, f.forum_name, f.forum_lastpost, f.forum_postcount,
	f.forum_threadcount, f.forum_lastuser, f.forum_access,
	t.thread_id, t.thread_lastpost, t.thread_lastpostid, t.thread_subject, t.thread_postcount, t.thread_views, t.thread_lastuser, t.thread_poll,
	p.post_message
	FROM ".DB_FORUMS." f
	LEFT JOIN ".DB_FORUM_THREADS." t ON f.forum_id = t.forum_id
	LEFT JOIN ".DB_FORUM_POSTS." p ON t.thread_id = p.post_id
	".(multilang_table("FO") ? "WHERE f.forum_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('f.forum_access')." AND f.forum_type!='1' AND f.forum_type!='3' AND t.thread_hidden='0'
	GROUP BY t.thread_id ORDER BY t.thread_lastpost DESC LIMIT 0,10");

    echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n";
    echo "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n";

    if (dbrows($result) != 0) {

        echo "<title>".$settings['sitename'].' - '.$locale['rss_forums'].(multilang_table("FO") ? $locale['rss_in'].LANGUAGE : "")."</title>\n<link>".$settings['siteurl']."</link>\n";
        echo "<description>".$settings['description']."</description>\n";

        while ($row = dbarray($result)) {
            $rsid = intval($row['thread_id']);
            $rtitle = $row['thread_subject'];
            $description = stripslashes(nl2br($row['post_message']));
            $description = strip_tags($description, "<a><p><br /><hr />");
            echo "<item>\n";
            echo "<title>".htmlspecialchars($rtitle)." [ ".$row['forum_name']." ] </title>\n";
            echo "<link>".$settings['siteurl']."infusions/forum/viewthread.php?forum_id=".$row['forum_id']."&amp;thread_id=".$rsid."</link>\n";
            echo "<description><![CDATA[".html_entity_decode($description)."]]></description>\n";
            echo "</item>\n";
        }
    } else {
        echo "<title>".$settings['sitename'].' - '.$locale['rss_forums']."</title>\n
		<link>".$settings['siteurl']."</link>\n
		<description>".$locale['rss_nodata']."</description>\n";
    }
    echo "</channel>\n</rss>";
}

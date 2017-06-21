<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_articles.php
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


if (db_exists(DB_ARTICLES) && db_exists(DB_ARTICLE_CATS)) {
    $result = dbquery("SELECT ta.*,tac.* FROM ".DB_ARTICLES." ta
	INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
	WHERE ".groupaccess('article_visibility').(multilang_table("AR") ? " AND article_cat_language='".LANGUAGE."'" : "")."
	ORDER BY article_datestamp DESC LIMIT 0,10");

    echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n";
    echo "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n";

    if (dbrows($result) != 0) {

        echo "<title>".$settings['sitename'].' - '.$locale['rss_articles'].(multilang_table("AR") ? $locale['rss_in'].LANGUAGE : "")."</title>\n<link>".$settings['siteurl']."</link>\n";
        echo "<description>".$settings['description']."</description>\n";

        while ($row = dbarray($result)) {
            $rsid = intval($row['article_id']);
            $rtitle = $row['article_subject'];
            $description = stripslashes(nl2br($row['article_snippet']));
            $description = strip_tags(htmlspecialchars_decode($description), "<a><p><br /><hr />");
            echo "<item>\n";
            echo "<title>".htmlspecialchars($rtitle).(multilang_table("AR") ? " - ".$locale['rss_in'].$row['article_cat_language'] : "")."</title>\n";
            echo "<link>".$settings['siteurl']."infusions/articles/articles.php?article_id=".$rsid."</link>\n";
            echo "<description><![CDATA[".html_entity_decode($description)."]]></description>\n";
            echo "</item>\n";
        }
    } else {
        echo "<title>".$settings['sitename'].' - '.$locale['rss_articles']."</title>\n
		<link>".$settings['siteurl']."</link>\n
		<description>".$locale['rss_nodata']."</description>\n";
    }
    echo "</channel>\n</rss>";
}

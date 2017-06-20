<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_downloads.php
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


if (db_exists(DB_DOWNLOADS) && db_exists(DB_DOWNLOAD_CATS)) {
    $result = dbquery("SELECT tbl1.*, tbl2.* FROM ".DB_DOWNLOAD_CATS." tbl1
	RIGHT JOIN ".DB_DOWNLOADS." tbl2 ON tbl1.download_cat_id=tbl2.download_cat
	WHERE ".groupaccess('download_visibility').(multilang_table("DL") ? " AND download_cat_language='".LANGUAGE."'" : "")."
	ORDER BY tbl2.download_count DESC LIMIT 0,10");

    echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n";
    echo "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n";

    if (dbrows($result) != 0) {

        echo "<title>".$settings['sitename'].' - '.$locale['rss_downloads'].(multilang_table("DL") ? $locale['rss_in'].LANGUAGE : "")."</title>\n<link>".$settings['siteurl']."</link>\n";
        echo "<description>".$settings['description']."</description>\n";

        while ($row = dbarray($result)) {
            $rsid = intval($row['download_id']);
            $rtitle = $row['download_title'];
            $description = stripslashes(nl2br($row['download_description']));
            $description = strip_tags($description, "<a><p><br /><hr />");
            echo "<item>\n<title>".htmlspecialchars($rtitle)."</title>\n";
            echo "<link>".$settings['siteurl']."infusions/downloads/downloads.php?download_id=".$rsid."</link>\n";
            echo "<description><![CDATA[".html_entity_decode($description)."]]></description>\n";
            echo "</item>\n\n";
        }
    } else {
        echo "<title>".$settings['sitename'].' - '.$locale['rss_downloads']."</title>\n
		<link>".$settings['siteurl']."</link>\n
		<description>".$locale['rss_nodata']."</description>\n";
    }
    echo "</channel>\n</rss>";
}

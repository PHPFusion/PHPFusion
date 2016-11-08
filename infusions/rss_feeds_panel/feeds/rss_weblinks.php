<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_weblinks.php
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

header('Content-Type: application/rss+xml; charset='.$locale['charset'].'');

if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
    include INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php";
} else {
    include INFUSIONS."rss_feeds_panel/locale/English.php";
}

if (db_exists(DB_WEBLINKS) && db_exists(DB_WEBLINK_CATS)) {
    $result = dbquery("
	SELECT tbl1.*, tbl2.* FROM ".DB_WEBLINK_CATS." tbl1
	RIGHT JOIN ".DB_WEBLINKS." tbl2 ON tbl1.weblink_cat_id=tbl2.weblink_cat
	WHERE ".groupaccess('weblink_visibility').(multilang_table("WL") ? " AND weblink_cat_language='".LANGUAGE."'" : "")."
	ORDER BY tbl2.weblink_count DESC LIMIT 0,10");

    echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n";
    echo "<rss version=\"2.0\">\n<channel>\n";

    if (dbrows($result) != 0) {

        echo "<title>".$settings['sitename'].' - '.$locale['rss_weblinks'].(multilang_table("WL") ? $locale['rss_in']..LANGUAGE : "")."</title>\n";
        echo "<link>".$settings['siteurl']."</link>\n<description>".$settings['description']."</description>\n";

        while ($row = dbarray($result)) {
            $rsid = intval($row['weblink_id']);
            $rtitle = $row['weblink_name'];
            $description = stripslashes(nl2br($row['weblink_description']));
            $description = strip_tags($description, "<a><p><br /><hr />");
            echo "<item>\n<title>".htmlspecialchars($rtitle)."</title>\n";
            echo "<link>".$settings['siteurl']."infusions/weblinks/weblinks.php?weblink_id=".$rsid."</link>\n";
            echo "<description>".htmlspecialchars($description)."</description>\n";
            echo "</item>\n";
        }
    } else {
        echo "<title>".$settings['sitename'].' - '.$locale['rss_weblinks']."</title>\n
		<link>".$settings['siteurl']."</link>\n
		<description>".$locale['rss_nodata']."</description>\n";
    }
    echo "</channel>\n</rss>";
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_articles_panel.php
| Author: PHP-Fusion Development Team
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

openside($locale['global_030']);
$result = dbquery("
	SELECT 
		a.article_id, a.article_subject
	FROM ".DB_ARTICLES." AS a
	INNER JOIN ".DB_ARTICLE_CATS." AS ac ON a.article_cat=ac.article_cat_id
	WHERE a.article_draft='0' AND ac.article_cat_status='1' AND ".groupaccess("a.article_visibility")." AND ".groupaccess("ac.article_cat_visibility")."
	".(multilang_table("AR") ? "AND a.article_language='".LANGUAGE."' AND ac.article_cat_language='".LANGUAGE."'" : "")."
	ORDER BY a.article_datestamp DESC
	LIMIT 0,5
");

if (dbrows($result)) {
    while ($data = dbarray($result)) {
        echo THEME_BULLET." <a href='".INFUSIONS."articles/articles.php?article_id=".$data['article_id']."' title='".$data['article_subject']."' class='side'>".trimlink($data['article_subject'], 21)."</a><br />\n";
    }
} else {
    echo "<div style='text-align:center'>".$locale['global_031']."</div>\n";
}

closeside();

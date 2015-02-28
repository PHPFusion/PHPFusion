<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_articles_panel.php
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

openside($locale['global_030']);
$result = dbquery(
	"SELECT ta.article_id, ta.article_subject, tac.article_cat_id, tac.article_cat_access FROM ".DB_ARTICLES." ta
	INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
	".(iSUPERADMIN ? "" : "WHERE ".groupaccess('article_cat_access'))." AND article_draft='0' ORDER BY article_datestamp DESC LIMIT 0,5"
);
if (dbrows($result)) {
	while($data = dbarray($result)) {
		$itemsubject = trimlink($data['article_subject'], 23);
		echo THEME_BULLET." <a href='".BASEDIR."articles.php?article_id=".$data['article_id']."' title='".$data['article_subject']."' class='side'>$itemsubject</a><br />\n";
	}
} else {
	echo "<div style='text-align:center'>".$locale['global_031']."</div>\n";
}
closeside();
?>
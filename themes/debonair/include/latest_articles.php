<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: debonair/include/latest_articles.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
echo "<h3 class='icon2 margin'>".$locale['debonair_0413']."</h3>\n";
if (db_exists(DB_ARTICLES)) {
	$result = dbquery("SELECT
		ta.article_id, ta.article_subject, ta.article_article, ta.article_keywords, ta.article_breaks,
		ta.article_datestamp, ta.article_reads, ta.article_allow_comments, ta.article_allow_ratings,
		tac.article_cat_id, tac.article_cat_name,
		tu.user_id, tu.user_name, tu.user_status, tu.user_avatar, tu.user_joined, tu.user_level
		FROM ".DB_ARTICLES." ta
		INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
		".(multilang_table("AR") ?  "WHERE tac.article_cat_language='".LANGUAGE."' AND " : "WHERE ")." ".groupaccess('article_visibility')."
		group by ta.article_id
		order by ta.article_datestamp DESC");
	if (dbrows($result)>0) {
		echo "<ul>\n";
		while ($data = dbarray($result)) {
			echo "<li><a href='".INFUSIONS."articles/articles.php?article_id=".$data['article_id']."'>".$data['article_subject']."</a></li>\n";
		}
		echo "</ul>\n";
	} else {
		echo $locale['debonair_0414'];
	}
} else {
	echo $locale['debonair_0415'];
}
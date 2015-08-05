<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: debonair/include/latest_news.php
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

echo "<h3 class='icon2 margin'>".$locale['debonair_0406']."</h3>\n";
if (db_exists(DB_NEWS)) {
	$result = dbquery("select news_id, news_subject from ".DB_NEWS." where news_language='".LANGUAGE."' and news_start <='".time()."' and news_end >='".time()."' ORDER BY news_start DESC");
	if (dbrows($result)>0) {
		echo "<ul>\n";
		while ($data = dbarray($result)) {
			echo "<li><a href='".INFUSIONS."news/news.php?readmore=".$data['news_id']."'>".$data['news_subject']."</a></li>\n";
		}
		echo "</ul>\n";
	} else {
		echo $locale['debonair_0407'];
	}
} else {
	echo $locale['debonair_0408'];
}
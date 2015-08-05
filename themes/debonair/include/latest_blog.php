<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: debonair/include/latest_blog.php
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

echo "<h3 class='icon2 margin'>".$locale['debonair_0403']."</h3>\n";
if (db_exists(DB_BLOG)) {
	$result = dbquery("select blog_id, blog_subject from ".DB_BLOG."
	 ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." AND (blog_start='0'||blog_start<=".time().")
	 AND (blog_end='0'||blog_end>=".time().") AND blog_draft='0'
	 ORDER BY blog_start DESC");
	if (dbrows($result)>0) {
		echo "<ul>\n";

		while ($data = dbarray($result)) {
			echo "<li><a href='".INFUSIONS."blog/blog.php?readmore=".$data['blog_id']."'>".$data['blog_subject']."</a></li>\n";
		}
		echo "</ul>\n";
	} else {
		echo $locale['debonair_0404'];
	}
} else {
	echo $locale['debonair_0405'];
}
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: blog_archive_panel.php
| Author: J.Falk (Domi)
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
include INFUSIONS."blog/locale/".LOCALESET."blog.php";

openside($locale['blog_1004']);
$result = dbquery("SELECT blog_id,blog_subject,blog_datestamp FROM ".DB_BLOG." ".(multilang_table("BL") ? "WHERE blog_language='".LANGUAGE."' AND" : "WHERE")." ".groupaccess('blog_visibility')." ORDER BY blog_datestamp DESC");
if (dbrows($result)) {
$data = array();
   
while ($row = dbarray($result)) {
	$year = date('Y', $row['blog_datestamp']);
	$month = date('F', $row['blog_datestamp']);
	$data[$year][$month][] = $row;
}
   
foreach($data as $blog_year => $blog_months) {
	echo "<b>".$blog_year."</b><br />";
		foreach($blog_months as $blog_month => $blog_entries) {
			echo "&nbsp;&nbsp;<strong>".$blog_month."</strong><br />";
				foreach($blog_entries as $blog_entry) {
					echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href='".INFUSIONS."blog/blog.php?readmore=".$blog_entry['blog_id']."'>".$blog_entry['blog_subject']."</a><br />";
				}
		}
}
} else {
	echo $locale['blog_3000'];
}
closeside();

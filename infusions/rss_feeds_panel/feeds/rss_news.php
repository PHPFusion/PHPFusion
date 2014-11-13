<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_news.php
| Author: Robert Gaudyn (Wooya)
| Co-Author: Joakim Falk (Domi)
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

$result = dbquery("
SELECT * FROM ".DB_NEWS."
WHERE ".groupaccess('news_visibility').(multilang_table("NS")?" AND news_language='".LANGUAGE."'":"")."
ORDER BY news_datestamp DESC LIMIT 0,10");

if (dbrows($result) != 0) {

	$rssimage = $settings['siteurl'].$settings['sitebanner']; 
	echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n\n";
	echo "<rss version=\"2.0\">\n\n
	<image>\n
	<url>$rssimage</url>\n
	</image>\n
	<channel>\n";
	
   echo "<title>".$settings['sitename'].$locale['rss004'].(multilang_table("NS")?" ".$locale['rss007']." ".LANGUAGE:"")."</title>\n";
   echo "<link>".$settings['siteurl']."</link>\n<description>".$settings['description']."</description>\n";

	while ($row=dbarray($result)) {
	   $rsid = intval($row['news_id']);
	   $rtitle = $row['news_subject'];
	   $description = stripslashes(nl2br($row['news_news']));
	   $description = strip_tags($description, "<a><p><br /><br /><hr />");
      echo "<item>\n";
      echo "<title>".htmlspecialchars($rtitle)."</title>\n";
      echo "<link>".$settings['siteurl']."news.php?readmore=".$rsid."</link>\n";
      echo "<description>".htmlspecialchars($description)."</description>\n";
      echo "</item>\n";
	}
} else {
	echo "<title>".$settings['sitename'].$locale['rss004']."</title>\n
	<link>".$settings['siteurl']."</link>\n
	<description>".$locale['rss008']."</description>\n";
}
echo "</channel></rss>";
?>
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_articles.php
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
require_once "../../../maincore.php";

header('Content-Type: application/rss+xml; charset='.$locale['charset'].'');

if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
	include INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php";
} else {
	include INFUSIONS."rss_feeds_panel/locale/English.php";
}

$result = dbquery("SELECT ta.*,tac.* FROM ".DB_ARTICLES." ta
INNER JOIN ".DB_ARTICLE_CATS." tac ON ta.article_cat=tac.article_cat_id
WHERE ".groupaccess('article_cat_access').(multilang_table("AR")?" AND article_cat_language='".LANGUAGE."'":"")."
ORDER BY article_datestamp DESC LIMIT 0,10");

	echo "<?xml version=\"1.0\" encoding=\"".$locale['charset']."\"?>\n\n";
	echo "<rss version=\"2.0\">\n\n <channel>\n";

if (dbrows($result) != 0) {

	echo "<title>".$settings['sitename'].$locale['rss002'].(multilang_table("AR")?" ".$locale['rss007']." ".LANGUAGE:"")."</title>\n<link>".$settings['siteurl']."</link>\n";
	echo "<description>".$settings['description']."</description>\n";

	while ($row=dbarray($result)) {
	$rsid = intval($row['article_id']);
	$rtitle = $row['article_subject'];
	$description = stripslashes(nl2br($row['article_snippet']));
	$description = strip_tags($description, "<a><p><br /><br /><hr />");
   echo "<item>\n";
   echo "<title>".htmlspecialchars($rtitle).(multilang_table("AR")?" - ".$locale['rss007'].$row['article_cat_language']:"")."</title>\n";
   echo "<link>".$settings['siteurl']."readarticle.php?article_id=".$rsid."</link>\n";
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
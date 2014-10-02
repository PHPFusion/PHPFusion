<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_feeds_panel.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
	include INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php";
} else {
	include INFUSIONS."rss_feeds_panel/locale/English.php";
}

openside($locale['rss009']);
echo "<p style='text-align: center;'>\n";
echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_news.php'><img class='img-responsive' src='".INFUSIONS."rss_feeds_panel/images/rss_news.gif' alt='".$locale['rss004']."'></a>\n";
echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_articles.php'><img class='img-responsive' src='".INFUSIONS."rss_feeds_panel/images/rss_articles.gif' alt='".$locale['rss002']."'></a>\n";
echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_downloads.php'><img class='img-responsive' src='".INFUSIONS."rss_feeds_panel/images/rss_downloads.gif' alt='".$locale['rss005']."'></a>\n";
echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_weblinks.php'><img class='img-responsive' src='".INFUSIONS."rss_feeds_panel/images/rss_weblinks.gif' alt='".$locale['rss006']."'></a>\n";
echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_forums.php'><img class='img-responsive' src='".INFUSIONS."rss_feeds_panel/images/rss_forums.gif' alt='".$locale['rss001']."'></a>\n";
echo "</p>\n";
closeside();
?>
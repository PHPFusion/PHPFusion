<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: rss_feeds_panel.php
| Author: Robert Gaudyn (Wooya)
| Co-Author: Joakim Falk (Falk)
| Co-Author: Tomasz Jankowski (jantom)
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

include INCLUDES."infusions_include.php";

if (file_exists(INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php")) {
    include INFUSIONS."rss_feeds_panel/locale/".LANGUAGE.".php";
} else {
    include INFUSIONS."rss_feeds_panel/locale/English.php";
}

if (file_exists(INFUSIONS."rss_feeds_panel/images/".LANGUAGE)) {
    set_image("rss_article", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_articles.gif");
    set_image("rss_blog", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_blog.gif");
    set_image("rss_downloads", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_downloads.gif");
    set_image("rss_forums", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_forums.gif");
    set_image("rss_news", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_news.gif");
    set_image("rss_weblinks", INFUSIONS."rss_feeds_panel/images/".LANGUAGE."/rss_weblinks.gif");

}

openside($locale['rss009']);
echo "<p style='text-align: center;'>\n";
if (db_exists(DB_NEWS)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_news.php'>".get_image("rss_news", $locale['rss004'], "", "", "class='img-responsive'")."</a>\n";
}
if (db_exists(DB_BLOG)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_blog.php'>".get_image("rss_blog", $locale['rss000'], "", "", "class='img-responsive'")."</a>\n";
}
if (db_exists(DB_ARTICLES) && db_exists(DB_ARTICLE_CATS)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_articles.php'>".get_image("rss_article", $locale['rss002'], "", "", "class='img-responsive'")."</a>\n";
}
if (db_exists(DB_DOWNLOADS) && db_exists(DB_DOWNLOAD_CATS)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_downloads.php'>".get_image("rss_downloads", $locale['rss003'], "", "", "class='img-responsive'")."</a>\n";
}
if (db_exists(DB_WEBLINKS) && db_exists(DB_WEBLINK_CATS)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_weblinks.php'>".get_image("rss_weblinks", $locale['rss005'], "", "", "class='img-responsive'")."</a>\n";
}
if (db_exists(DB_FORUM_POSTS) && db_exists(DB_FORUMS)) {
    echo "<a href='".INFUSIONS."rss_feeds_panel/feeds/rss_forums.php'>".get_image("rss_forums", $locale['rss001'], "", "", "class='img-responsive'")."</a>\n";
}
echo "</p>\n";
closeside();
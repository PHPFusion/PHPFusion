<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: rss_rewrite_include.php
| Author: Rizado (Chubatyj Vitalij)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

$pattern = [
    "rss/articles"  => "infusions/rss_feeds_panel/feeds/rss_articles.php",
    "rss/blog"      => "infusions/rss_feeds_panel/feeds/rss_blog.php",
    "rss/downloads" => "infusions/rss_feeds_panel/feeds/rss_downloads.php",
    "rss/forums"    => "infusions/rss_feeds_panel/feeds/rss_forums.php",
    "rss/news"      => "infusions/rss_feeds_panel/feeds/rss_news.php",
    "rss/weblinks"  => "infusions/rss_feeds_panel/feeds/rss_weblinks.php"
];

<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: rss_feeds_panel.php
| Author: Core Development Team
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

include_once INCLUDES."infusions_include.php";

if (file_exists(INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php')) {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/'.LOCALESET.'rss.php');
} else {
    $locale = fusion_get_locale('', INFUSIONS.'rss_feeds_panel/locale/English/rss.php');
}

add_to_css('
.rss-button {
    background: #FF9800;
    padding: 1px 15px;
    color: #fff!important;
    -webkit-border-radius: 4px;
    border-radius: 4px;
    margin: 3px 0;
    display: block;
}
.rss-button:hover,
.rss-button:focus {
    background: #F57C00;
    color: #fff;
    text-decoration: none;
}
.rss-button .fa {
    padding-right: 5px;
}
');

openside($locale['rss_title']);
if (defined('ARTICLES_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_articles.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_articles'].'</a>';
}

if (defined('BLOG_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_blog.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_blog'].'</a>';
}

if (defined('DOWNLOADS_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_downloads.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_downloads'].'</a>';
}

if (defined('FORUM_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_forums.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_forums'].'</a>';
}

if (defined('NEWS_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_news.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_news'].'</a>';
}

if (defined('WEBLINKS_EXISTS')) {
    echo '<a href="'.INFUSIONS.'rss_feeds_panel/feeds/rss_weblinks.php" target="_blank" class="rss-button"><i class="fa fa-rss"></i> '.$locale['rss_weblinks'].'</a>';
}

closeside();

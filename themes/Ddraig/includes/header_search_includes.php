<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: header_search_include.php
| Author: JoiNNN
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

$page = FUSION_SELF;
$stype = "";

//Articles
if ($page == "articles.php") {
    include LOCALE.LOCALESET."search/articles.php";
    $text = $locale['a400'];
    $stype = "articles";
    //Downloads
} else if ($page == "downloads.php") {
    include LOCALE.LOCALESET."search/downloads.php";
    $text = $locale['d400'];
    $stype = "downloads";
    //FAQs
} else if ($page == "faq.php") {
    include LOCALE.LOCALESET."search/faqs.php";
    $text = $locale['fq400'];
    $stype = "faqs";
    //Forums
} else if (strpos(TRUE_PHP_SELF, '/forum/') !== FALSE) {
    include LOCALE.LOCALESET."search/forums.php";
    $text = $locale['f400'];
    $stype = "forums";
    //Members
} else if ($page == "members.php") {
    include LOCALE.LOCALESET."search/members.php";
    $text = $locale['m400'];
    $stype = "members";
    //News
} else if ($page == "news.php") {
    include LOCALE.LOCALESET."search/news.php";
    $text = $locale['n400'];
    $stype = "news";
    //Photos
} else if ($page == "photogallery.php") {
    include LOCALE.LOCALESET."search/photos.php";
    $text = $locale['p400'];
    $stype = "photos";
    //Weblinks
} else if ($page == "weblinks.php") {
    include LOCALE.LOCALESET."search/weblinks.php";
    $text = $locale['w400'];
    $stype = "weblinks";
}

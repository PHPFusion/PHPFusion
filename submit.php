<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: submit.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "maincore.php";
if (!iMEMBER) {
    redirect("index.php");
}

require_once THEMES."templates/header.php";
include_once INCLUDES."bbcode_include.php";
include_once INCLUDES."infusions_include.php";


$submit_info = array();

$stype = "";

$modules = array(
    'n' => db_exists(DB_NEWS),
    'p' => db_exists(DB_PHOTO_ALBUMS),
    'a' => db_exists(DB_ARTICLES),
    'd' => db_exists(DB_DOWNLOADS),
    'l' => db_exists(DB_WEBLINKS),
    'b' => db_exists(DB_BLOG)
);

$sum = array_sum($modules);

if ($sum or isset($_GET['stype']) && !empty($modules[$_GET['stype']])) {

    $stype = $_GET['stype'];

    if ($stype === "l") {
        include INFUSIONS."weblinks/weblink_submit.php";
    } elseif ($stype === "n") {
        include INFUSIONS."news/news_submit.php";
    } elseif ($stype === "b") {
        include INFUSIONS."blog/blog_submit.php";
    } elseif ($stype === "a") {
        include INFUSIONS."articles/article_submit.php";
    } elseif ($stype === "p") {
        include INFUSIONS."gallery/photo_submit.php";
    } elseif ($stype === "d") {
        include INFUSIONS."downloads/download_submit.php";
    } else {
        redirect("index.php");
    }
} else {
    redirect("index.php");
}
require_once THEMES."templates/footer.php";
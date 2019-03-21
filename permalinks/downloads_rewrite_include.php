<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: downloads_rewrite_include.php
| Author: Chan (Frederick MC Chan)
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

$regex = [
    "%download_id%"       => "([0-9]+)",
    "%cat_id%"            => "([0-9]+)",
    "%author_id%"         => "([0-9]+)",
    "%download_title%"    => "([0-9a-zA-Z._\W]+)",
    "%file_id%"           => "([0-9]+)",
    "%download_cat_id%"   => "([0-9]+)",
    "%author_name%"       => "([0-9a-zA-Z._\W]+)",
    "%download_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%rowstart%"          => "([0-9]+)",
    "%filter_type%"       => "([0-9a-zA-Z]+)",
    "%stype%"             => "(d)"
];

$pattern = [
    "submit-%stype%/files"                                                     => "submit.php?stype=%stype%",
    "submit-%stype%/files/submitted-and-thank-you"                             => "submit.php?stype=%stype%&amp;submitted=d",
    "downloads/author/%author_id%/%author_name%"                               => "infusions/downloads/downloads.php?author=%author_id%",
    "downloads/filter/%filter_type%"                                           => "infusions/downloads/downloads.php?type=%filter_type%",
    "downloads/filter/%filter_type%/rowstart/%rowstart%"                       => "infusions/downloads/downloads.php?type=%filter_type%&amp;rowstart=%rowstart%",
    "downloads/filter/%filter_type%/%download_cat_id%-category"                => "infusions/downloads/downloads.php?cat_id=%download_cat_id%&amp;type=%filter_type%",
    "downloads/%download_cat_id%-category/%download_cat_name%"                 => "infusions/downloads/downloads.php?cat_id=%download_cat_id%",
    "downloads/%download_cat_id%-category/file/%download_id%/%download_title%" => "infusions/downloads/downloads.php?cat_id=%download_cat_id%&amp;file_id=%download_id%",
    "downloads/%download_cat_id%-category/%download_id%/%download_title%"      => "infusions/downloads/downloads.php?cat_id=%download_cat_id%&amp;download_id=%download_id%",
    "downloads/file/%download_id%/%download_title%"                            => "infusions/downloads/downloads.php?file_id=%download_id%",
    "downloads/%download_id%/%download_title%"                                 => "infusions/downloads/downloads.php?download_id=%download_id%",
    "downloads/rowstart/%rowstart%"                                            => "infusions/downloads/downloads.php?rowstart=%rowstart%",
    "downloads"                                                                => "infusions/downloads/downloads.php"
];

$pattern_tables["%download_id%"] = [
    "table"       => DB_DOWNLOADS,
    "primary_key" => "download_id",
    "id"          => ["%download_id%" => "download_id"],
    "columns"     => [
        "%download_title%" => "download_title"
    ]
];

$pattern_tables["%download_cat_id%"] = [
    "table"       => DB_DOWNLOAD_CATS,
    "primary_key" => "download_cat_id",
    "id"          => ["%download_cat_id%" => "download_cat_id"],
    "columns"     => [
        "%download_cat_name%" => "download_cat_name"
    ]
];

$pattern_tables["%author_id%"] = [
    "table"       => DB_USERS,
    "primary_key" => "user_id",
    "id"          => ["%author_id%" => "user_id"],
    "columns"     => [
        "%author_name%" => "user_name"
    ]
];

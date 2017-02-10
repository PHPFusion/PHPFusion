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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$regex = array(
    "%download_id%" => "([0-9]+)",
    "%cat_id%" => "([0-9]+)",
    "%download_title%" => "([0-9a-zA-Z._\W]+)",
    "%file_id%" => "([0-9]+)",
    "%download_cat_id%" => "([0-9]+)",
    "%download_cat_name%" => "([0-9a-zA-Z._\W]+)",
    "%stype%" => "(d)",
    "%filter_type%" => "([0-9a-zA-Z]+)"
);

$pattern = array(
    "submit/%stype%/files" => "submit.php?stype=%stype%",
    "submit/%stype%/files/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=d",
    "downloads" => "infusions/downloads/downloads.php",
    "downloads/most-%filter_type%" => "infusions/downloads/downloads.php?type=%filter_type%",
    "downloads/%cat_id%/%download_id%/%download_title%" => "infusions/downloads/downloads.php?cat_id=%cat_id%&amp;download_id=%download_id%",
    "downloads/%download_id%/%download_title%" => "infusions/downloads/downloads.php?download_id=%download_id%",
    "downloads/file/%download_id%/%download_title%" => "infusions/downloads/downloads.php?cat_id=%cat_id%&amp;file_id=%download_id%",
    "downloads/category/%download_cat_id%/%download_cat_name%" => "infusions/downloads/downloads.php?cat_id=%download_cat_id%",
);

$pattern_tables["%download_id%"] = array(
    "table" => DB_DOWNLOADS,
    "primary_key" => "download_id",
    "id" => array("%download_id%" => "download_id"),
    "columns" => array(
        "%download_title%" => "download_title",
    )
);

$pattern_tables["%download_cat_id%"] = array(
    "table" => DB_DOWNLOAD_CATS,
    "primary_key" => "download_cat_id",
    "id" => array("%download_cat_id%" => "download_cat_id"),
    "columns" => array(
        "%download_cat_name%" => "download_cat_name",
    )
);

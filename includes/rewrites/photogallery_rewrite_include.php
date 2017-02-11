<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Filename: photogallery_rewrite_include.php
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
    "%album_id%" => "([0-9]+)",
    "%album_title%" => "([0-9a-zA-Z._\W]+)",
    "%photo_id%" => "([0-9]+)",
    "%photo_title%" => "([0-9a-zA-Z._\W]+)",
    "%rowstart%" => "([0-9]+)",
    "%c_start%" => "([0-9]+)",
    "%stype%" => "(p)",
    "%comment_id%" => "([0-9]+)",
    "%hash_stop%" => "\#(?=\s*|)",
);

$pattern = array(
    "submit/%stype%/photos" => "submit.php?stype=%stype%",
    "submit/%stype%/photos/submitted-and-thank-you" => "submit.php?stype=%stype%&amp;submitted=p",
    "gallery/browse/%rowstart%" => "infusions/gallery/gallery.php?rowstart=%rowstart%",
    "gallery/browse/%album_id%/%rowstart%" => "infusions/gallery/gallery.php?album_id=%album_id%&amp;rowstart=%rowstart%",
    "gallery/photo/comments-%c_start%/%photo_id%/%photo_title%" => "infusions/gallery/gallery.php?photo_id=%photo_id%&amp;c_start=%c_start%",
    "gallery/photo/%photo_id%/%photo_title%" => "infusions/gallery/gallery.php?photo_id=%photo_id%",
    "gallery/%album_id%/%album_title%" => "infusions/gallery/gallery.php?album_id=%album_id%",
    "gallery" => "infusions/gallery/gallery.php",
);


$pattern_tables["%album_id%"] = array(
    "table" => DB_PHOTO_ALBUMS,
    "primary_key" => "album_id",
    "id" => array("%album_id%" => "album_id"),
    "columns" => array(
        "%album_title%" => "album_title"
    )
);

$pattern_tables["%photo_id%"] = array(
    "table" => DB_PHOTOS,
    "primary_key" => "photo_id",
    "id" => array("%photo_id%" => "photo_id"),
    "columns" => array(
        "%photo_title%" => "photo_title"
    )
);
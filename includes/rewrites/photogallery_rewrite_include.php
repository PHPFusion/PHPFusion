<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
| Author: Hien (Frederick MC Chan)
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

$regex = array("%album_id%" => "([0-9]+)","%album_title%" => "([0-9a-zA-Z._\W]+)");

$pattern = array("gallery" => "photogallery.php", "gallery/%album_id%/%album_title%" => "photogallery.php?album_id=%album_id%");

$dbname = DB_PHOTO_ALBUMS;
$dbid = array("%album_id%" => "album_id");
$dbinfo = array("%album_title%" => "album_title");
?>
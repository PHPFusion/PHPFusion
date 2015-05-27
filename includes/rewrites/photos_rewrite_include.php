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

$regex = array(
	"%photo_id%" => "([0-9]+)",
	"%photo_title%" => "([0-9a-zA-Z._\W]+)",
);

$pattern = array("photo/%photo_id%/%photo_title%" => "photogallery.php?photo_id=%photo_id%");

$dbname = DB_PHOTOS;
$dbid = array("%photo_id%" => "photo_id");
$dbinfo = array("%photo_title%" => "photo_title");

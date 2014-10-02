<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules for 7.03
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
$regex = array(
	"%album_id%" => "([0-9]+)",
	"%photo_id%" => "([0-9]+)",
	"%rowstart%" => "([0-9]+)",
	"%c_start%" => "([0-9]+)",
);

$pattern = array(
	"gallery/browse/%rowstart%" => "photogallery.php?rowstart=%rowstart%",
	"gallery/browse/%album_id%/%rowstart%" => "photogallery.php?album_id=%album_id%&amp;rowstart=%rowstart%",
	"photo/browse/%photo_id%/%c_start%" => "photogallery.php?photo_id=%album_id%&amp;c_start=%c_start%",
);

?>
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
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
);

$pattern = array(
	"viewphoto/%photo_id%" => "photogallery.php?photo_id=%photo_id%",
	"gallery/browse/%album_id%/%rowstart%" => "photogallery.php?album_id=%album_id%&amp;rowstart=%rowstart%",
	);

?>
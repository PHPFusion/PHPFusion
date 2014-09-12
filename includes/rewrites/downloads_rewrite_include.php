<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: downloads_rewrite_include.php
| Author: Ankur Thakur
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
	"%download_id%" => "([0-9]+)",
	"%cat_id%" => "([0-9]+)",
	"%download_title%" => "([a-zA-Z0-9-]+)"
);
$pattern = array(
	"download/%download_id%/%download_title%" => "downloads.php?cat_id=%cat_id%&amp;download_id=%download_id%",
	"download/%download_id%/%download_title%" => "downloads.php?download_id=%download_id%"
);
$dbname = DB_DOWNLOADS;
$dbid = array("%download_id%" => "download_id");
$dbinfo = array(
	"%download_title%" => "download_title"
);

?>
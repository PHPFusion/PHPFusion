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
$regex = array("%download_id%" => "([0-9]+)", "%cat_id%" => "([0-9]+)", "%download_title%" => "([a-zA-Z0-9-]+)",
			   "%file_id%" => "([0-9]+)",);
$pattern = array("download" => "downloads.php",
				 "download/%cat_id%/%download_id%/%download_title%" => "downloads.php?cat_id=%cat_id%&amp;download_id=%download_id%",
				 "download/%download_id%/%download_title%" => "downloads.php?download_id=%download_id%",
				 "download/file/%download_id%/%download_title%" => "downloads.php?cat_id=%cat_id%&amp;file_id=%download_id%");
$dir_path = BASEDIR;
$dbname = DB_DOWNLOADS;
$dbid = array("%download_id%" => "download_id");
$dbinfo = array("%download_title%" => "download_title");

?>
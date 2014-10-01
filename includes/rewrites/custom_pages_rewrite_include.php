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
	"%cp_id%" => "([0-9]+)",
	"%cp_title%" => "([a-zA-Z0-9-_]+)"
);
$pattern = array("pages/%cp_id%/%cp_title%" => "viewpage.php?page_id=%cp_id%");
$dbname = DB_CUSTOM_PAGES;
$dbid = array("%cp_id%" => "page_id");
$dbinfo = array("%cp_title%" => "page_title");

?>
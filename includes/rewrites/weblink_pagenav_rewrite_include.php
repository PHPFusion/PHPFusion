<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2011 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| File Category: Core Rewrite Modules
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

$regex = array("%weblink_id%" => "([0-9]+)",
			   "%rowstart%" => "([0-9]+)",
			   "%weblink_cat_id%" => "([0-9]+)",
			   "%weblink_name%" => "([0-9a-zA-Z._\W]+)");

$pattern = array("links/%weblink_id%/browse/%weblink_cat_id%/%rowstart%" => "weblinks.php?cat_id=%weblink_cat_id%&amp;rowstart=%rowstart%");

$dir_path = BASEDIR;
$dbname = DB_WEBLINKS;
?>
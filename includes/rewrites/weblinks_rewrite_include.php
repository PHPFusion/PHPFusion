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

$regex = array("%weblink_id%" => "([0-9]+)",
  			   "%weblink_name%" => "([0-9a-zA-Z._\W]+)",
			   "%weblink_cat_id%" => "([0-9]+)");

$pattern = array("weblink/%weblink_id%/%weblink_name%" => "weblinks.php?cat_id=%weblink_cat_id%&amp;weblink_id=%weblink_id%");

$dbname = DB_WEBLINKS;
$dbid = array("%weblink_id%" => "weblink_id");
$dbinfo = array("%weblink_name%" => "weblink_name", "%weblink_cat_id%" => "weblink_cat");

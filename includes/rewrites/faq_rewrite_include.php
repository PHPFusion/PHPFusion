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
	"%faq_cat_id%" => "([0-9]+)", 
	"%faq_cat_name%" => "([0-9a-zA-Z._\W]+)",
);

$pattern = array(
	"Frequently-asked-Questions" => "infusions/faq/faq.php",
	"faq-category/%faq_cat_id%/%faq_cat_name%" => "infusions/faq/faq.php?cat_id=%faq_cat_id%",
);

$dir_path = ROOT;
$dbname = DB_FAQ_CATS;
$dbid = array("%faq_cat_id%" => "faq_cat_id");
$dbinfo = array("%faq_cat_name%" => "faq_cat_name");

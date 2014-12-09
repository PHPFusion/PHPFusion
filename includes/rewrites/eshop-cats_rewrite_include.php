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
if (!defined("IN_FUSION")) { die("Access Denied"); }

$regex = array("%category%" => "([0-9]+)", 
				  "%title%" => "([0-9a-zA-Z._\W]+)");
				  
$pattern = array("shop-category/%category%/%title%" => "eshop.php?category=%category%");

$dir_path = ROOT;

$dbname = DB_ESHOP_CATS;
$dbid = array("%category%" => "cid");
$dbinfo = array("%title%" => "title");
?>
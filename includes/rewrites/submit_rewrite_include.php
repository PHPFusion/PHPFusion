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

$pattern = array(
	"submit" => "submit.php",
	"submit/weblinks" => "submit.php?stype=l",
	"submit/files" => "submit.php?stype=d",
	"submit/articles" => "submit.php?stype=a",
	"submit/news" => "submit.php?stype=n",
	"submit/photos" => "submit.php?stype=p",
);

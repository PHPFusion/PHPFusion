<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: yahoo_bbcode_include_var.php
| Author: Wooya
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

$__BBCODE__[] = array(
    "description" => $locale['bb_yahoo_description'], "value" => "yahoo", "bbcode_start" => "[yahoo]",
    "bbcode_end" => "[/yahoo]", "usage" => "[yahoo]".$locale['bb_yahoo_usage']."[/yahoo]"
);

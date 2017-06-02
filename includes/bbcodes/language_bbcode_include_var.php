<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: language_bbcode_include_var.php
| Author: Falk (J.Falk)
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
    "description" => $locale['bb_language_description'], "value" => "language",
    "bbcode_start" => "[language]", "bbcode_end" => "[/language]",
    "usage" => "[language]".$locale['bb_language_usage']."[/language]"
);

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: hide_bbcode_include_var.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (iADMIN) {
    $__BBCODE__[] = [
        "description"  => $locale['bb_hide_description'],
        "value"        => "hide",
        "bbcode_start" => "[hide]", "bbcode_end" => "[/hide]",
        "usage"        => "[hide]".$locale['bb_hide_usage']."[/hide]"
    ];
} else {
    $__BBCODE_NOT_QUOTABLE__[] = "hide";
}

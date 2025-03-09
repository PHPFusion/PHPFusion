<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: big_bbcode_include_var.php
| Author: Core Development Team
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

$__BBCODE__[] = [
    "description"  => $locale['bb_big_description'],
    "value"        => "big",
    "bbcode_start" => "[big]",
    "bbcode_end"   => "[/big]",
    "usage"        => "[big]".$locale['bb_big_usage']."[/big]",
    "svg"          => "<i class='fa fa-text-height fa-lg'></i>"
];

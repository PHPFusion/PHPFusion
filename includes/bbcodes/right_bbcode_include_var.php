<?php

/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: right_bbcode_include_var.php
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
    "description"  => $locale['bb_right_description'],
    "value"        => "right",
    "bbcode_start" => "[right]",
    "bbcode_end"   => "[/right]",
    "usage"        => "[right]".$locale['bb_right_usage']."[/right]",
    'svg'          => '<i class="fas fa-align-right fa-lg"></i>'
];

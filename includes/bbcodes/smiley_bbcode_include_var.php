<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: smiley_bbcode_include_var.php
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
defined('IN_FUSION') || exit;

$__BBCODE__[] = [
    "description"    => $locale['bb_smiley_description'],
    "value"          => "smiley",
    "usage"          => $locale['bb_smiley_usage'],
    'id'             => 'bbcode_smileys_list_'.$textarea_name,
    "phpfunction"    => "echo displaysmileys('$textarea_name', '$inputform_name');",
    'dropdown'       => TRUE,
    'dropdown_class' => 'p-5'
];

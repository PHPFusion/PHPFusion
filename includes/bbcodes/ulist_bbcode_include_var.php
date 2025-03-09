<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ulist_bbcode_include_var.php
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
    "description"    => $locale['bb_ulist_description'],
    "value"          => "ulist", "bbcode_start" => "[ulist=TYPE]", "bbcode_end" => "[/ulist]",
    "usage"          => "[ulist=(disc|circle|square)]".$locale['bb_ulist_usage']."[/ulist]",
    "onclick"        => "return false;",
    "id"             => 'bbcode_ulist_'.$textarea_name,
    'dropdown_items' => [
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[ulist=disc]', '[/ulist]', '".$inputform_name."');return false;\"><span>".$locale['bb_ulist_1']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[ulist=circle]', '[/ulist]', '".$inputform_name."');return false;\"><span>".$locale['bb_ulist_2']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[ulist=square]', '[/ulist]', '".$inputform_name."');return false;\"><span>".$locale['bb_ulist_3']."</span></a>",
    ],
    'dropdown'       => TRUE,
    'dropdown_style' => 'min-width: 50px;',
    'svg'            => '<i class="fas fa-list-ul fa-lg"></i>'
];

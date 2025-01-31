<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: olist_bbcode_include_var.php
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
    'description'    => $locale['bb_olist_description'],
    'value'          => "olist", 'bbcode_start' => "[olist=TYPE]", 'bbcode_end' => '[/olist]',
    'usage'          => "[olist=(1|a|A|i|I)]".$locale['bb_olist_usage']."[/olist]",
    'onclick'        => "return false",
    'id'             => 'bbcode_olist_'.$textarea_name,
    'dropdown_items' => [
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[olist=1]', '[/olist]', '".$inputform_name."');return false;\"><span>1. - ".$locale['bb_olist_0100']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[olist=a]', '[/olist]', '".$inputform_name."');return false;\"><span>a. - ".$locale['bb_olist_0101']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[olist=A]', '[/olist]', '".$inputform_name."');return false;\"><span>A. - ".$locale['bb_olist_0102']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[olist=i]', '[/olist]', '".$inputform_name."');return false;\"><span>i. - ".$locale['bb_olist_0103']."</span></a>",
        "<a class='bbcode-link' href='#' onclick=\"addText('".$textarea_name."', '[olist=I]', '[/olist]', '".$inputform_name."');return false;\"><span>I. - ".$locale['bb_olist_0104']."</span></a>",
    ],
    'dropdown'       => TRUE,
    'dropdown_style' => 'min-width: 50px;',
    'svg'            => '<i class="fas fa-list-ol fa-lg"></i>'
];

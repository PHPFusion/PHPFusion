<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: img_bbcode_include.php
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

if (!function_exists("img_bbcode_callback")) {
    function img_bbcode_callback($matches) {
        if (substr($matches[3], -1, 1) != "/") {
            $url = $matches[1].str_replace(["?", "&amp;", "&", "="], "", $matches[3]).$matches[4];
            return "<a class='forum-img-wrapper' href='".$url."' target='_blabk'><img src='".$url."' alt='".$matches[3].$matches[4]."' style='border:0px' class='img-responsive forum-img' referrerpolicy='no-referrer'></a>";
        } else {
            return $matches[0];
        }
    }
}

$text = preg_replace_callback("#\[img\]((http|ftp|https|ftps)://|/)(.*?)(\.(jpg|jpeg|gif|png|svg|webp|JPG|JPEG|GIF|PNG|SVG|WEBP))\[/img\]#si", "img_bbcode_callback", $text);

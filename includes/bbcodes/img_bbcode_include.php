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
    function img_bbcode_callback($m) {
        if (substr($m[5], -1, 1) != "/") {
            $url = $m[3].str_replace(["?", "&amp;", "&", "="], "", $m[5]).$m[6];
            $html = '<div class="forum-img-wrapper">';
            $html .= '<img src="'.$url.'" alt="'.$m[5].$m[6].'" class="img-responsive forum-img" referrerpolicy="no-referrer">';
            $html .= !empty($m['title']) ? '<span class="image-description">'.$m['title'].'</span>' : '';
            $html .= '</div>';

            return $html;
        } else {
            return $m[0];
        }
    }
}

$text = preg_replace_callback("#\[img(=(?P<title>.*?))?\]((http|ftp|https|ftps)://|/)(.*?)(\.(jpg|jpeg|gif|png|svg|webp|JPG|JPEG|GIF|PNG|SVG|WEBP))\[/img\]#si", "img_bbcode_callback", $text);

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: preview.ajax.php
| Author: Frederick MC CHan (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once __DIR__."../../../../../maincore.php";
require_once THEMES."templates/render_functions.php";
$locale = fusion_get_locale();
// filter to relative path conversion
echo "<div class='preview-response clearfix p-20'>\n";
// Set get_image paths based on URI. This is ajax request file. It doesn't return a standard BASEDIR.
$prefix_ = '';
$text = post('text');
$text = stripinput($text);
$editor = post('editor');
$url = post('url');
$mode = post('mode');
$attach = post('post_attach');

if ($attach) {
    require_once INFUSIONS.'forum/forum_include.php';
}

if (!fusion_get_settings("site_seo") && $url) {
    $uri = pathinfo($url);
    $count = substr($url, -1) == "/" ? substr_count($uri['dirname'], "/") : substr_count($uri['dirname'], "/") - 1;
    $prefix_ = str_repeat("../", ($count >= 0) ? $count : 0);
    foreach (cache_smileys() as $smiley) {
        $smiley_path = fusion_get_settings('siteurl')."images/smiley/".$smiley['smiley_image'];
        \PHPFusion\ImageRepo::setImage("smiley_".$smiley['smiley_text'], $smiley_path);
    }
}

if ($editor == 'html') {
    if ($attach) {
        $text = parse_attach($text);
    }
    $text = htmlspecialchars($text);
    $text = parsesmileys(nl2br(html_entity_decode(stripslashes($text))));

    if ($mode == 'admin') {
        $images = str_replace('../../../', '', IMAGES);
        $text = str_replace(IMAGES, $images, $text);
        if (defined('IMAGES_N')) {
            $text = str_replace(IMAGES_N, $images, $text);
        }
        $text = parse_imageDir($text, $prefix_."images/");
    }


    echo nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset'])) ?: "<p class='text-center'>".$locale['nopreview']."</p>\n";

} else if ($editor == 'bbcode') {
    if ($attach) {
        $text = parse_attach($text);
    }
    $text = htmlspecialchars($text);
    $text = parseubb(parsesmileys($text));
    if ($mode == 'admin') {
        $images = str_replace('../../../', '', IMAGES);
        $text = str_replace(IMAGES, $images, $text);
        if (defined('IMAGES_N')) {
            $text = str_replace(IMAGES_N, $images, $text);
        }
        $text = parse_imageDir($text, $prefix_."images/");
    }


    echo nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset'])) ?: "<p class='text-center'>".$locale['nopreview']."</p>\n";

} else {
    if ($attach) {
        $text = parse_attach($text);
    }
    $text = htmlspecialchars($text);
    $text = parsesmileys($text);
    if ($mode == 'admin') {
        $images = str_replace('../../../', '', IMAGES);
        $text = str_replace(IMAGES, $images, $text);
        if (defined('IMAGES_N')) {
            $text = str_replace(IMAGES_N, $images, $text);
        }
    }


    echo parse_imageDir(nl2br(html_entity_decode($text, ENT_QUOTES, $locale['charset']))) ?: "<p class='text-center'>".$locale['nopreview']."</p>\n";
}

echo "</div>\n";

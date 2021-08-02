<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: submissions.tpl.php
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
if (!function_exists('display_news_no_submissions')) {
    function display_news_no_submissions($info) {
        return fusion_get_function('opentable', $info['title'])."<div class='well text-center'>".$info['text']."</div>".fusion_get_function('closetable');
    }
}

if (!function_exists('display_news_confirm_submissions')) {
    function display_news_confirm_submissions($info) {
        return fusion_get_function('opentable', $info['title'])."
        <div class='well text-center'>
        <p class='strong'>".$info['message']."</p>
        <p class='strong'>".$info['submit_link']."</p>
        <p class='strong'>".$info['index_link']."</p>
        </div>
        ".fusion_get_function('closetable');
    }
}

if (!function_exists('display_news_submissions_preview')) {
    function display_news_submissions_preview($info) {
        return fusion_get_function('opentable', $info['title'])."
            <p>".$info['snippet']."</p>
            <p>".$info['full_text']."</p>
        ".fusion_get_function('closetable');
    }
}

if (!function_exists('display_news_submissions_form')) {
    function display_news_submissions_form(array $info = []) {
        return fusion_get_function('opentable', $info['title'])."<div class='well spacer-xs'>".$info['guidelines']."</div>
        ".$info['news_subject_field']."
        ".$info['news_language_field']."
        ".$info['news_keywords_field']."
        ".$info['news_cat_field']."
        ".$info['news_image_field']."
        ".$info['news_image_align_field']."
        ".$info['news_news_field']."
        ".$info['news_body_field']."
        ".$info['news_submit']."
        ".$info['preview_news']."
        ".fusion_get_function('closetable');
    }
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: /news/templates/submissions.php
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
if (!function_exists('display_news_no_submissions')) {
    function display_news_no_submissions() {
        return open_table('{%title%}')."<div class='well text-center'>{%text%}</div>".close_table();
    }
}
if (!function_exists('display_news_confirm_submissions')) {
    function display_news_confirm_submissions() {
        return open_table('{%title%}')."        
        <div class='well text-center'>
        <p class='strong'>{%message%}</p>
        <p class='strong'>{%submit_link%}</p>
        <p class='strong'>{%index_link%}</p>
        </div>
        ".close_table();
    }
}

if (!function_exists('display_news_submissions_preview')) {
    function display_news_submissions_preview() {
        return open_table('{%title%}')."
            <p>{%snippet%}</p>
            <p>{%full_text%}</p>
        ".close_table();
    }
}

if (!function_exists('display_news_submissions_form')) {
    function display_news_submissions_form(array $info = array()) {
        return open_table('{%title%}')."<div class='well spacer-xs'>{%guidelines%}</div>
        {%news_subject_field%}
        {%news_language_field%}
        {%news_keywords_field%}
        {%news_cat_field%}
        {%news_image_field%}
        {%news_image_align_field%}
        {%news_news_field%}
        {%news_body_field%}
        {%news_submit%}
        {%preview_news%}
        ".close_table();
    }
}

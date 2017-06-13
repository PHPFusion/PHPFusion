<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum/templates/forum_tags.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

if (!function_exists("display_forum_tags")) {
    function display_forum_tags($info) {
        add_to_head("<link rel='stylesheet' type='text/css' href='".INFUSIONS."forum/templates/css/forum.css'>");
        $locale = fusion_get_locale();

        if (isset($_GET['tag_id'])) {
            $html = \PHPFusion\Template::getInstance('tags');
            $html->set_template(FORUM.'templates/tags/tag_threads.html');
            $html->set_tag('title', $locale['forum_0002']);
            $html->set_tag('filter', fusion_get_function('forum_filter', $info));
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['threads']['pagenav'])) {
                $html->set_block('pagenav', ['pagenav' => $info['threads']['pagenav']]);
                $html->set_block('pagenav_a', ['pagenav_a' => $info['threads']['pagenav']]);
            }
            if (!empty($info['threads'])) {
                $content = '';
                if (!empty($info['threads']['sticky'])) {
                    foreach ($info['threads']['sticky'] as $cdata) {
                        $content .= fusion_get_function('render_thread_item', $cdata);
                    }
                }
                if (!empty($info['threads']['item'])) {
                    foreach ($info['threads']['item'] as $cdata) {
                        $content .= fusion_get_function('render_thread_item', $cdata);
                    }
                }
                $html->set_block('threads', ['content' => $content]);
            } else {
                $html->set_block('no_threads', ['message' => $locale['forum_0269']]);
            }
            if (!empty($info['threads']['pagenav2'])) {
                $html->set_block('pagenav2', ['pagenav2' => $info['threads']['pagenav2']]);
            }
            echo $html->get_output();

        } else {
            $html = \PHPFusion\Template::getInstance('tags');
            $html->set_template(FORUM.'templates/tags/tag.html');
            $html->set_tag('breadcrumb', render_breadcrumbs());
            if (!empty($info['tags'])) {
                unset($info['tags'][0]);
                foreach ($info['tags'] as $tag_id => $tag_data) {
                    $html->set_block('tag_block', [
                        'tag_color'       => $tag_data['tag_color'],
                        'tag_link'        => $tag_data['tag_link'],
                        'tag_title'       => $tag_data['tag_title'],
                        'tag_description' => $tag_data['tag_description'],
                        'thread_subject'  => (!empty($tag_data['threads']) ? trim_text($tag_data['threads']['thread_subject'], 100) : ''),
                        'thread_activity' => (!empty($tag_data['threads']) ? ttimer($tag_data['threads']['thread_lastpost']) : ''),
                    ]);
                }
            } else {
                $html->set_block('no_tag', ['message' => 'There are no tags defined']);
            }
            echo $html->get_output();
        }
    }
}
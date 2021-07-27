<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_threads.tpl.php
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

if (!function_exists('render_threads_panel')) {
    function render_threads_panel($info) {
        add_to_jquery("$('[data-trim-text]').trim_text();");

        openside($info['title']);
        if (!empty($info['latest'])) {
            echo '<div class="side-label"><strong>'.$info['latest']['label'].'</strong></div>';
            echo '<ul class="side">';
            if (!empty($info['latest']['item'])) {
                foreach ($info['latest']['item'] as $data) {
                    echo '<li><a href="'.$data['link_url'].'"><span data-trim-text="18">'.$data['link_title'].'</span></a></li>';
                }
            } else {
                echo '<li><span class="text-center">'.$info['latest']['no_rows'].'</span></li>';
            }
            echo '</ul>';
        }

        if (!empty($info['hottest'])) {
            echo '<div class="side-label"><strong>'.$info['hottest']['label'].'</strong></div>';
            echo '<ul class="side">';
            if (!empty($info['hottest']['item'])) {
                foreach ($info['hottest']['item'] as $data) {
                    echo '<li><a href="'.$data['link_url'].'"><span data-trim-text="18">'.$data['link_title'].'</span> '.$data['badge'].'</a></li>';
                }
            } else {
                echo '<li><span class="text-center">'.$info['hottest']['no_rows'].'</span></li>';
            }
            echo '</ul>';
        }
        closeside();
    }
}

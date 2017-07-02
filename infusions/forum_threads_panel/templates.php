<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: forum_threads_panel/templates.php
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

if (!function_exists('render_threads_panel')) {
    function render_threads_panel($info) {

        $html = \PHPFusion\Template::getInstance('threads');
        $html->set_template(INFUSIONS."forum_threads_panel/templates/threads.html");

        $html->set_tag('openside', fusion_get_function('openside', $info['title']));
        $html->set_tag('closeside', fusion_get_function('closeside'));
        $html->set_tag('label', $info['latest']['label']);
        $html->set_tag('label2', $info['hottest']['label']);

        add_to_jquery("$('[data-trim-text]').trim_text();");

        if (!empty($info['latest'])) {
        	if (!empty($info['latest']['item'])) {
                foreach ($info['latest']['item'] as $data) {
                    $html->set_block('latest', [
                        'link_url'    => $data['link_url'],
                        'link_title'  => '<div data-trim-text="18">'.$data['link_title'].'</div>',
                    ]);
                }
        	}
        } else {
            $html->set_block('latest_no_item', ['message' => $info['latest']['no_rows']]);
        }
        if (!empty($info['hottest'])) {
        	if (!empty($info['hottest']['item'])) {
                foreach ($info['hottest']['item'] as $data) {
                    $html->set_block('hottest', [
                        'link_url'    => $data['link_url'],
                        'link_title'  => '<div data-trim-text="18">'.$data['link_title'].'</div>',
                        'badge'       => $data['badge'],
                    ]);
                }
        	}
        } else {
            $html->set_block('hottest_no_item', ['message' => $info['hottest']['no_rows']]);
        }
        echo $html->get_output();
    }
}
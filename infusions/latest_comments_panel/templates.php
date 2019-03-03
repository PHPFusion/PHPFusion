<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: latest_comments_panel/templates.php
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
defined('IN_FUSION') || exit;

if (!function_exists('render_latest_comments')) {
    function render_latest_comments($info) {
        $html = \PHPFusion\Template::getInstance('latest_comments');
        $html->set_template(__DIR__.'/templates/latest_comments.html');
        $html->set_tag('openside', fusion_get_function('openside', $info['title']));
        $html->set_tag('closeside', fusion_get_function('closeside'));

        add_to_jquery("$('[data-trim-text]').trim_text();");

        if (!empty($info['item'])) {
            foreach ($info['item'] as $id => $data) {
                $link = !empty($data['data']['user_id']) ? TRUE : FALSE;
                $html->set_block('comment', [
                    'comment_user_avatar' => display_avatar($data['data'], '35px', '', $link, 'img-circle m-r-10 m-t-5'),
                    'comment_subject'     => trim_text($data['title'], 40),
                    'comment_subject_url' => $data['url'],
                    'comment_url'         => $data['c_url'],
                    'comment_message'     => trim_text(strip_tags(parse_textarea($data['data']['comment_message'], FALSE, TRUE)), 35),
                    'comment_bullet'      => $info['theme_bullet']
                ]);
            }
        } else {
            $html->set_block('no_item', ['message' => $info['no_rows']]);
        }

        echo $html->get_output();
    }
}

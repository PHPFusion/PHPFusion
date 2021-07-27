<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: latest_comments.tpl.php
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

if (!function_exists('render_latest_comments')) {
    function render_latest_comments($info) {
        add_to_jquery("$('[data-trim-text]').trim_text();");

        openside($info['title']);
        if (!empty($info['item'])) {
            echo '<ul class="list-style-none">';
            foreach ($info['item'] as $data) {
                $link = !empty($data['data']['user_id']);
                $avatar = display_avatar($data['data'], '35px', '', $link, 'img-circle m-r-10 m-t-5');
                $message = trim_text(strip_tags(parse_text($data['data']['comment_message'], ['parse_smileys' => FALSE])), 35);

                echo '<li>
                    <div class="pull-left">'.$avatar.'</div>
                    <div class="overflow-hide">
                        <strong><a href="'.$data['url'].'">'.trim_text($data['title'], 40).'</a></strong>
                        <div class="clearfix"><a href="'.$data['c_url'].'">'.$message.'</a></div>
                    </div>
                </li>';
            }
            echo '</ul>';
        } else {
            echo $info['no_rows'];
        }
        closeside();
    }
}

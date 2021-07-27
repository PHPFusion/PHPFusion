<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: poll.tpl.php
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

if (!function_exists('render_poll')) {
    function render_poll($info) {
        if (!empty($info['poll_table'])) {
            openside($info['poll_tablename']);

            foreach ($info['poll_table'] as $poll) {
                if (!empty($poll['poll_option'])) {
                    $poll['poll_option'] = implode('', $poll['poll_option']);
                }

                echo !empty($poll['openform']) ? $poll['openform'] : '';
                echo '<div class="panel panel-default">
                    <div class="panel-heading text-center">'.$poll['poll_title'].'</div>
                    <div class="panel-body">'.$poll['poll_option'].'</div>';
                    if (!empty($poll['button'])) {
                        echo '<div class="panel-footer">
                            <div class="text-center">'.$poll['button'].'</div>
                        </div>';
                    }
                echo '</div>';
                echo !empty($poll['closeform']) ? $poll['closeform'] : '';
            }

            if (!empty($info['poll_arch'])) {
                echo '<div class="text-center">'.$info['poll_arch'].'</div>';
            }
            closeside();
        }
    }
}

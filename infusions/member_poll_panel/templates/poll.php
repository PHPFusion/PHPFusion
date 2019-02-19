<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: poll.php
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

if (!function_exists('render_poll')) {
    function render_poll($info) {
        if (!empty($info['poll_table'])) {
            $tpl = \PHPFusion\Template::getInstance('member_poll');
            $tpl->set_template(__DIR__.'/poll.html');
            $tpl->set_locale(fusion_get_locale());
            $tpl->set_tag('openside', fusion_get_function('openside', $info['poll_tablename']));
            $tpl->set_tag('closeside', fusion_get_function('closeside'));
            if (!empty($info['poll_arch'])) {
                $tpl->set_block('poll_arch', [
                    'content' => $info['poll_arch']
                ]);
            }
            foreach ($info['poll_table'] as $poll) {
                if (!empty($poll['poll_option'])) {
                    $poll['poll_option'] = implode('', $poll['poll_option']);
                }
                if (!empty($poll['poll_foot'])) {
                    $poll['poll_foot'] = "<div class='text-center'>\n".implode("</div>\n<div class='text-center'>\n", $poll['poll_foot'])."</div>\n";
                }
                $tpl->set_block('polls', $poll);
            }

            echo $tpl->get_output();

        }
    }
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_mods_online_panel/templates.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

if (!function_exists('render_forum_mods')) {
    function render_forum_mods($info) {
        $html = \PHPFusion\Template::getInstance('render_forum_mods');
        $html->set_template(__DIR__.'/templates/forum_mods.html');

        if (!empty($info['admin']['item'])) {
            $html->set_tag('adm_openside', fusion_get_function('openside', $info['admin']['openside']));
            $html->set_tag('adm_closeside', fusion_get_function('closeside'));

            foreach ($info['admin']['item'] as $cdatm) {
                $html->set_block('forum_admin', [
                    'user_title'  => $cdatm['user_title'],
                    'user_avatar' => $cdatm['user_avatar'],
                    'user_profil' => $cdatm['user_profil'],
                ]);
            }
        }
        if (!empty($info['member']['item'])) {
            $html->set_tag('memb_openside', fusion_get_function('openside', $info['member']['openside']));
            $html->set_tag('memb_closeside', fusion_get_function('closeside'));

            foreach ($info['member']['item'] as $cdatm) {
                $html->set_block('forum_member', [
                    'user_title'  => $cdatm['user_title'],
                    'user_avatar' => $cdatm['user_avatar'],
                    'user_profil' => $cdatm['user_profil'],
                ]);
            }
        }

        echo $html->get_output();
    }
}

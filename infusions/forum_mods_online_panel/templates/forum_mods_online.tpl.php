<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: forum_mods_online.tpl.php
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

if (!function_exists('render_forum_mods')) {
    function render_forum_mods($info) {
        if (!empty($info['admin']['item'])) {
            openside($info['admin']['openside']);
            foreach ($info['admin']['item'] as $cdatm) {
                echo '<div>
                    <div class="pull-left m-t-5">'.$cdatm['user_avatar'].'</div>
                    <div class="overflow-hide">
                        <div class="display-block strong">'.$cdatm['user_profil'].'</div>
                        <span class="text-lighter">'.$cdatm['user_title'].'</span>
                    </div>
                </div>';
            }
            closeside();
        }

        if (!empty($info['member']['item'])) {
            openside($info['member']['openside']);
            foreach ($info['member']['item'] as $cdatm) {
                echo '<div>
                    <div class="pull-left m-t-5">'.$cdatm['user_avatar'].'</div>
                    <div class="overflow-hide">
                        <div class="display-block strong">'.$cdatm['user_profil'].'</div>
                        <span class="text-lighter">'.$cdatm['user_title'].'</span>
                    </div>
                </div>';
            }
            closeside();
        }
    }
}

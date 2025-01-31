<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: online_users.tpl.php
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

if (!function_exists('online_users_panel')) {
    function online_users_panel($info) {
        $locale = fusion_get_locale();

        openside($locale['global_010']);
        echo '<strong>'.$locale['global_011'].'</strong> '.$info['guests'].'<br>';
        echo '<strong>'.$locale['global_012'].'</strong> '.$info['members'];
        echo '<div class="users">'.$info['online_members'].'</div>';
        echo '<hr>';

        echo '<div>';
        echo '<i class="fas fa-users m-r-10"></i><strong>'.$locale['global_014'].':</strong> '.$info['total_members'].'<br>';
        if (!empty($info['unactivated_members'])) {
            echo '<i class="fa-fw fas fa-user-circle m-r-10"></i><a href="'.$info['unactivated_members']['admin_link'].'" class="strong">'.$locale['global_015'].'</a>: '.$info['unactivated_members']['total_members'].'<br>';
        }
        echo '<i class="fa-fw fas fa-user m-r-10"></i><strong>'.$locale['global_016'].':</strong> '.$info['newest_member'];
        echo '</div>';

        closeside();
    }
}

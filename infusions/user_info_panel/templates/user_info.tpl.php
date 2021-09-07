<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_info.tpl.php
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

if (!function_exists('display_user_info_panel')) {
    /**
     * Default User Info Panel Template
     *
     * @param array $info
     */
    function display_user_info_panel($info = []) {
        $locale = fusion_get_locale();

        if (iMEMBER) {
            openside($locale['UM096'].$info['user_name']);

            echo '<div class="clearfix m-b-10">';
                echo '<div class="uip_avatar pull-left m-r-10">'.$info['user_avatar'].'</div>';
                echo $info['user_level'];

                if ($info['forum_exists'] && $info['show_reputation']) {
                    echo '<span class="display-block" title="'.fusion_get_locale('forum_0014', INFUSIONS.'forum/locale/'.LOCALESET.'forum.php').'"><i class="fa fa-dot-circle-o"></i> '.$info['userdata']['user_reputation'].'</span>';
                }
            echo '</div>';

            if ($info['pm_msg_count'] > 0) {
                echo '<div class="user_pm_notice"><a href="'.$info['user_pm_link'].'"><i class="fa fa-envelope-o"></i> '.$info['user_pm_title'].'</a></div>';
            }

            if (!empty($info['pm_progress'])) {
                echo '<div class="user_pm_progressbar">'.$info['pm_progress'].'</div>';
            }

            echo '<div id="navigation-user">';
                echo '<strong>'.$locale['UM097'].'</strong>';

                echo '<ul class="block">';
                    echo '<li><a href="'.BASEDIR.'edit_profile.php">'.$locale['UM080'].' <i class="fa fa-user-circle-o fa-pull-right"></i></a></li>';
                    echo '<li><a href="'.BASEDIR.'messages.php">'.$locale['UM081'].' <i class="fa fa-envelope-o  fa-pull-right"></i></a></li>';

                    if ($info['forum_exists'] && file_exists(INFUSIONS.'forum_threads_list_panel/my_tracked_threads.php')) {
                        echo '<li><a href="'.INFUSIONS.'forum_threads_list_panel/my_tracked_threads.php">'.$locale['UM088'].' <i class="fa fa-commenting-o fa-pull-right"></i></a></li>';
                    }

                    echo '<li><a href="'.BASEDIR.'members.php">'.$locale['UM082'].' <i class="fa fa-users fa-pull-right"></i></a></li>';

                    if (iADMIN) {
                        echo '<li><a href="'.ADMIN.'index.php'.fusion_get_aidlink().'&pagenum=0">'.$locale['UM083'].' <i class="fa fa-dashboard fa-pull-right"></i></a></li>';
                    }

                    if ($info['login_session']) {
                        echo '<li><a href="'.BASEDIR.'index.php?logoff='.$info['userdata']['user_id'].'">'.$locale['UM103'].' <i class="fa fa-sign-out fa-pull-right"></i></a></li>';
                    }

                    if (!empty($info['submissions'])) {
                        echo '<li>';
                            echo '<a data-toggle="collapse" data-parent="#navigation-user" href="#uipcollapse" aria-expanded="false" aria-controls="#uipcollapse">'.$locale['UM089'].' <i class="fa fa-cloud-upload fa-pull-right"></i></a>';

                            echo '<ul id="uipcollapse" class="panel-collapse collapse block">';
                                foreach ($info['submissions'] as $modules) {
                                    echo '<li><a class="side p-l-15" href="'.$modules['link'].'">'.$modules['title'].'</a></li>';
                                }
                            echo '</ul>';
                        echo '</li>';
                    }
                echo '</ul>';
            echo '</div>';

            echo '<a class="btn btn-primary btn-block" href="'.BASEDIR.'index.php?logout=yes">'.$locale['UM084'].'</a>';
            closeside();
        } else {
            openside($locale['global_100']);
            echo $info['openform'];
            echo $info['login_name_field'];
            echo $info['login_pass_field'];
            echo $info['login_remember_field'];

            echo $info['login_submit'];
            echo $info['registration'];
            echo '<br>';
            echo $info['lostpassword'];
            echo $info['closeform'];
            closeside();
        }
    }
}

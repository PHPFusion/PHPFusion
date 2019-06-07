<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_info_panel/templates/template.php
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
/**
 * Default template for User Info Panel
 */
if (!function_exists('display_user_info_panel')) {
    /**
     * Default User Info Panel Template
     *
     * @param array $info
     */
    function display_user_info_panel(array $info = []) {
        // Code styling need to change.
        // Do the whole template parsing here.
        $locale = fusion_get_locale();
        $userdata = fusion_get_userdata();

        $tpl = \PHPFusion\Template::getInstance('uip');
        $tpl->set_locale($locale);
        $tpl_path = __DIR__.'/uip-guest.html';

        if (iMEMBER) {
            $tpl_path = __DIR__.'/uip-member.html';
            $tpl->set_tag('user_avatar', $info['user_avatar']);
            $tpl->set_tag('user_name', $info['user_name']);
            $tpl->set_tag('user_level', $info['user_level']);
            $tpl->set_tag('user_pm_notice', $info['user_pm_notice']);
            $tpl->set_tag('user_pm_progressbar', $info['user_pm_progress']);
            $tpl->set_tag('user_pm_link', BASEDIR."messages.php");
            if ($info['show_reputation'] == 1) {
                $tpl->set_block('user_reputation', [
                    'user_reputation_icon' => $info['user_reputation_icon'],
                    'user_reputation'      => $info['user_reputation']
                ]);
            }
            $tpl->set_tag('edit_profile_link', BASEDIR."edit_profile.php");
            $tpl->set_tag('edit_account_link', BASEDIR."edit_profile.php?ref=se_profile");
            $tpl->set_tag('member_link', BASEDIR."members.php");
            $tpl->set_tag('logout_link', BASEDIR."index.php?logout=yes");
            if ($info['forum_exists']) {
                $tpl->set_block('user_forum', [
                    'forum_track_link' => INFUSIONS."forum_threads_list_panel/my_tracked_threads.php",
                ]);
            }
            if (iADMIN) {
                $tpl->set_block('user_acp', [
                    'user_acp_link' => ADMIN."index.php".fusion_get_aidlink()."&amp;pagenum=0"
                ]);
            }
            if (!empty($info['submissions'])) {
                foreach ($info['submissions'] as $modules) {
                    $tpl->set_block('submit_modules', [
                        'module_link'  => $modules['link'],
                        'module_title' => $modules['title'],
                    ]);
                }
            }
            $tpl->set_tag('openside', fusion_get_function('openside', $locale['UM096'].$userdata['user_name']));
            $tpl->set_tag('closeside', fusion_get_function('closeside'));
        } else {
            if (!preg_match('/login.php/i', FUSION_SELF)) {
                $tpl->set_tag('login_openform', $info['login_openform']);
                $tpl->set_tag('login_closeform', $info['login_closeform']);
                $tpl->set_tag('login_name_input', $info['login_name_input']);
                $tpl->set_tag('login_pass_input', $info['login_pass_input']);
                $tpl->set_tag('login_remember_input', $info['login_remember_input']);
                $tpl->set_tag('openside', fusion_get_function('openside', $locale['global_100']));
                $tpl->set_tag('closeside', fusion_get_function('closeside'));
                $tpl->set_tag('login_submit', $info['login_submit']);
                $tpl->set_tag('registration_link', $info['registration_link']);
                $tpl->set_tag('lostpassword_link', $info['lostpassword_link']);
            }
        }

        $tpl->set_template($tpl_path);

        echo $tpl->get_output();
    }

}

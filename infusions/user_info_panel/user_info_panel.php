<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: user_info_panel.php
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
if (!defined("IN_FUSION")) die("Access Denied");
require_once dirname(__FILE__).'/templates/default.php';
$userdata = fusion_get_userdata();
$aidlink = fusion_get_aidlink();
$locale = fusion_get_locale();
$modules = \PHPFusion\Admins::getInstance()->getSubmitData();

if (iMEMBER) {
    $messages_count = dbquery("SELECT
    SUM(message_folder=0) AS inbox_count,
    SUM(message_folder=1) AS outbox_count,
    SUM(message_folder=2) AS archive_count,
    SUM(message_read=0 AND message_folder=0) AS unread_count
    FROM ".DB_MESSAGES."
    WHERE message_to=:user_id", array(':user_id' => $userdata['user_id']));

    $messages_count = dbarray($messages_count);
    $inbox_count = (int)$messages_count['inbox_count'];
    $outbox_count = (int)$messages_count['outbox_count'];
    $archive_count = (int)$messages_count['archive_count'];
    $msg_count = (int)$messages_count['unread_count'];
    $forum_exists = infusion_exists('forum');

    $pm_progress = '';
    if (!iSUPERADMIN) {
        $inbox_cfg = user_pm_settings($userdata['user_id'], "user_inbox");
        $inbox_percent = $inbox_cfg > 1 ? number_format(($inbox_count / $inbox_cfg) * 99, 0) : number_format(0 * 99, 0);
        $pm_progress = progress_bar($inbox_percent, $locale['UM098'],
            FALSE, // class
            FALSE,  // height
            FALSE,  // reverse
            TRUE,  // as percent
            ($inbox_cfg == 0 ? TRUE : FALSE)
        );

        $outbox_cfg = user_pm_settings($userdata['user_id'], "user_outbox");
        $outbox_percent = $outbox_cfg > 1 ? number_format(($outbox_count / $outbox_cfg) * 99, 0) : number_format(0 * 99, 0);
        $pm_progress .= progress_bar($outbox_percent, $locale['UM099'],
            FALSE, // class
            FALSE,  // height
            FALSE,  // reverse
            TRUE,  // as percent
            ($inbox_cfg == 0 ? TRUE : FALSE)
        );

        $archive_cfg = user_pm_settings($userdata['user_id'], "user_archive");
        $archive_percent = $archive_cfg > 1 ? number_format(($archive_count / $archive_cfg) * 99, 0) : number_format(0 * 99, 0);
        $pm_progress .= progress_bar($archive_percent, $locale['UM100'],
            FALSE, // class
            FALSE,  // height
            FALSE,  // reverse
            TRUE,  // as percent
            ($inbox_cfg == 0 ? TRUE : FALSE)
        );
    }

    $submissions_link_arr = [];
    $submissions_link = '';
    if (!empty($modules)) {
        foreach ($modules as $stype => $title) {
            $submissions_link_arr[] = [
                'link'  => $title['submit_link'],
                'title' => sprintf($title['title'], str_replace('...', '', fusion_get_locale('UM089', LOCALE.LOCALESET."global.php"))),
            ];
        }
    }

    $submit_link = '';
    if (iADMIN && checkrights("SU")) {
        $subm_count = dbcount("(submit_id)", DB_SUBMISSIONS);
        if ($subm_count) {
                $submit_link = "<a href='".ADMIN."index.php".fusion_get_aidlink()."&amp;pagenum=0' class='side'>".
                    sprintf($locale['global_125'], $subm_count).($subm_count == 1 ? $locale['global_128'] : $locale['global_129'])."</a>";
        }
    }

    $info = [
        'forum_exists'         => $forum_exists,
        'user_avatar'          => display_avatar($userdata, '90px', '', FALSE, ''),
        'user_name'            => profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status']),
        'user_level'           => $userdata['user_level'],
        'user_reputation'      => $forum_exists ? fusion_get_userdata('user_reputation') ?: 0 : '',
        'user_reputation_icon' => $forum_exists ? "<i class='fa fa-dot-circle-o' title='".fusion_get_locale('forum_0014', INFUSIONS.'forum/locale/'.LOCALESET.'forum.php')."'></i>\n" : '',
        'user_pm_link'         => BASEDIR."messages.php?folder=inbox",
        'user_pm_title'        => sprintf($locale['UM085'], $msg_count).($msg_count == 1 ? $locale['UM086'] : $locale['UM087']),
        'submissions'          => $submissions_link_arr,
        'submit'               => $submit_link,
    ] + $userdata;

    ob_start();
    display_user_info_panel($info);
    echo strtr(ob_get_clean(), [
        '{%openside%}'             => open_side($locale['UM096'].$userdata['user_name']),
        '{%closeside%}'            => close_side(),
        '{%user_avatar%}'          => $info['user_avatar'],
        '{%user_name%}'            => $info['user_name'],
        '{%user_level%}'           => getuserlevel($info['user_level']),
        '{%user_reputation_icon%}' => $info['user_reputation_icon'],
        '{%user_reputation%}'      => $info['user_reputation'],
        '{%user_pm_notice%}'       => ($msg_count ? "<a href='".$info['user_pm_link']."' title='".$info['user_pm_title']."'><i class='fa fa-envelope-o'></i> $msg_count</a>" : ''),
        '{%user_pm_progressbar%}'  => $pm_progress,
        '{%user_nav_title%}'       => $locale['UM097'],
        '{%edit_profile_link%}'    => BASEDIR."edit_profile.php",
        '{%edit_profile_title%}'   => $locale['UM080'],
        '{%pm_link%}'              => BASEDIR."messages.php",
        '{%pm_title%}'             => $locale['UM081'],
        '{%track_link%}'           => $forum_exists ? INFUSIONS."forum_threads_list_panel/my_tracked_threads.php" : '',
        '{%track_title%}'          => $forum_exists ? $locale['UM088'] : '',
        '{%member_link%}'          => BASEDIR."members.php",
        '{%member_title%}'         => $locale['UM082'],
        '{%acp_link%}'             => (iADMIN ? ADMIN."index.php".fusion_get_aidlink()."&amp;pagenum=0" : ''),
        '{%acp_title%}'            => (iADMIN ? $locale['UM083'] : ''),
        '{%logout_link%}'          => BASEDIR."index.php?logout=yes",
        '{%logout_title%}'         => $locale['UM084'],
        '{%submit%}'               => $info['submit'],
    ]);

} else {
    if (!preg_match('/login.php/i', FUSION_SELF)) {

        $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        if (isset($_GET['redirect']) && strstr($_GET['redirect'], "/")) {
            $action_url = cleanurl(urldecode($_GET['redirect']));
        }
        switch (fusion_get_settings("login_method")) {
            case 2 :
                $placeholder = $locale['global_101c'];
                break;
            case 1 :
                $placeholder = $locale['global_101b'];
                break;
            default:
                $placeholder = $locale['global_101a'];
        }

        $info = [
            'title'                => $locale['global_100'],
            'open_side'            => open_side($locale['global_100']),
            'close_side'           => close_side(),
            'login_openform'       => openform('loginform', 'post', $action_url),
            'login_closeform'      => closeform(),
            'login_name_field'     => form_text('user_name', $locale['global_101'], '', array(
                'placeholder' => $placeholder,
                'required'    => 1
            )),
            'login_pass_field'     => form_text('user_pass', $locale['global_102'], '', array(
                'placeholder' => $locale['global_102'],
                'type'        => 'password',
                'required'    => 1
            )),
            'login_remember_field' => form_checkbox('remember_me', $locale['global_103'], '', ['value' => 'y']),
            'login_submit'         => form_button('login', $locale['global_104'], '', array('class' => 'm-t-20 m-b-20 btn-block btn-primary')),
            'registration_'        => (fusion_get_settings('enable_registration') ? strtr($locale['global_105'], ['[LINK]' => "<a href='".BASEDIR."register.php'>", '[/LINK]' => "</a>\n"]) : ''),
            'lostpassword_'        => strtr($locale['global_106'], ['[LINK]' => "<a href='".BASEDIR."lostpassword.php'>", '[/LINK]' => "</a>"])
        ];

        ob_start();

        echo $info['login_openform'];
        display_user_info_panel($info);
        echo $info['login_closeform'];

        echo strtr(ob_get_clean(),
            [
                '{%openside%}'             => $info['open_side'],
                '{%closeside%}'            => $info['close_side'],
                '{%login_name_field%}'     => $info['login_name_field'],
                '{%login_pass_field%}'     => $info['login_pass_field'],
                '{%login_remember_field%}' => $info['login_remember_field'],
                '{%login_submit%}'         => $info['login_submit'],
                '{%registration_%}'        => $info['registration_'],
                '{%lostpassword_%}'        => $info['lostpassword_'],
            ]
        );
    }
}

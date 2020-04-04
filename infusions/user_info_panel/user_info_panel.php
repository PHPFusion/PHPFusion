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
namespace PHPFusion\Infusions\User_Info_Panel;

defined('IN_FUSION') || exit;

class User_Info {

    private $locale = [];
    private $aidlink = '';

    public function __construct() {
        require_once __DIR__.'/templates/template.php';
        $this->locale = fusion_get_locale();
        $this->aidlink = fusion_get_aidlink();
    }

    public function getInfo() {
        if (iMEMBER) {
            return (array)$this->getMemberInfo();
        }
        return (array)$this->getGuestInfo();
    }

    private function getMemberInfo() {
        $userdata = fusion_get_userdata();
        $modules = \PHPFusion\Admins::getInstance()->getSubmitData();

        $messages_count = dbquery("SELECT
            SUM(message_folder=0) AS inbox_count,
            SUM(message_folder=1) AS outbox_count,
            SUM(message_folder=2) AS archive_count,
            SUM(message_read=0 AND message_folder=0) AS unread_count
            FROM ".DB_MESSAGES."
            WHERE message_to=:user_id", [':user_id' => $userdata['user_id']]);
        $messages_count = dbarray($messages_count);
        $inbox_count = (int)$messages_count['inbox_count'];
        $outbox_count = (int)$messages_count['outbox_count'];
        $archive_count = (int)$messages_count['archive_count'];
        $msg_count = (int)$messages_count['unread_count'];
        $forum_exists = defined('FORUM_EXIST');
        $forum_settings = get_settings('forum');

        $pm_progress = '';
        if (!iSUPERADMIN) {
            $inbox_cfg = user_pm_settings($userdata['user_id'], "user_inbox");
            if ($inbox_cfg !== 0) {
                $inbox_percent = $inbox_cfg > 1 ? number_format(($inbox_count / $inbox_cfg) * 99, 0) : number_format(0 * 99, 0);
                $pm_progress .= progress_bar($inbox_percent, $this->locale['UM098'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0 ? TRUE : FALSE)]);
            }

            $outbox_cfg = user_pm_settings($userdata['user_id'], "user_outbox");
            if ($outbox_cfg !== 0) {
                $outbox_percent = $outbox_cfg > 1 ? number_format(($outbox_count / $outbox_cfg) * 99, 0) : number_format(0 * 99, 0);
                $pm_progress .= progress_bar($outbox_percent, $this->locale['UM099'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0 ? TRUE : FALSE)]);
            }

            $archive_cfg = user_pm_settings($userdata['user_id'], "user_archive");
            if ($archive_cfg !== 0) {
                $archive_percent = $archive_cfg > 1 ? number_format(($archive_count / $archive_cfg) * 99, 0) : number_format(0 * 99, 0);
                $pm_progress .= progress_bar($archive_percent, $this->locale['UM100'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0 ? TRUE : FALSE)]);
            }
        }

        $submissions_link_arr = [];
        if (!empty($modules)) {
            foreach ($modules as $stype => $title) {
                $submissions_link_arr[] = [
                    'link'  => BASEDIR.$title['submit_link'],
                    'title' => sprintf($title['title'], str_replace('...', '', fusion_get_locale('UM089', LOCALE.LOCALESET."global.php"))),
                ];
            }
        }

        $pm_title = sprintf($this->locale['UM085'], $msg_count).($msg_count == 1 ? $this->locale['UM086'] : $this->locale['UM087']);

        return [
                'forum_exists'         => $forum_exists,
                'show_reputation'      => !empty($forum_settings['forum_show_reputation']) && $forum_settings['forum_show_reputation'] ? 1 : 0,
                'user_avatar'          => display_avatar($userdata, '90px', '', FALSE, ''),
                'user_name'            => profile_link($userdata['user_id'], $userdata['user_name'], $userdata['user_status']),
                'user_level'           => getuserlevel($userdata['user_level']),
                'user_reputation'      => $forum_exists ? fusion_get_userdata('user_reputation') ?: 0 : '',
                'user_reputation_icon' => $forum_exists ? "<i class='fa fa-dot-circle-o' title='".fusion_get_locale('forum_0014', INFUSIONS.'forum/locale/'.LOCALESET.'forum.php')."'></i>\n" : '',
                'user_pm_link'         => BASEDIR."messages.php?folder=inbox",
                'user_pm_notice'       => ($msg_count ? "<a href='".BASEDIR."messages.php?folder=inbox' title='".$pm_title."'><i class='fa fa-envelope-o'></i> $msg_count</a>" : ''),
                'user_pm_title'        => $pm_title,
                'user_pm_progress'     => $pm_progress,
                'submissions'          => $submissions_link_arr
            ] + $userdata;
    }

    public static function getLoginPostUrl() {
        $action_url = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
        $redirect_url = get("redirect");
        if ($redirect_url && strstr($redirect_url, "/")) {
            $action_url = cleanurl(urldecode($redirect_url));
        }
        return $action_url;
    }

    private function getGuestInfo() {

        if (!preg_match('/login.php/i', FUSION_SELF)) {


            switch (fusion_get_settings("login_method")) {
                case 2 :
                    $placeholder = $this->locale['global_101c'];
                    break;
                case 1 :
                    $placeholder = $this->locale['global_101b'];
                    break;
                default:
                    $placeholder = $this->locale['global_101a'];
            }

            return [
                'title'                => $this->locale['global_100'],
                'open_side'            => fusion_get_function('openside', $this->locale['global_100']),
                'close_side'           => fusion_get_function('closeside'),
                'login_openform'       => openform('loginform', 'post', self::getLoginPostUrl()),
                'login_closeform'      => closeform(),
                'login_name_input'     => form_text('user_name', $this->locale['global_101'], '', [
                    'placeholder' => $placeholder,
                    'required'    => 1,
                    'inline'      => 1
                ]),
                'login_pass_input'     => form_text('user_pass', $this->locale['global_102'], '', [
                    'placeholder' => $this->locale['global_102'],
                    'type'        => 'password',
                    'required'    => 1,
                    'inline'      => 1
                ]),
                'login_remember_input' => form_checkbox('remember_me', $this->locale['global_103'], '', ['value' => 'y']),
                'login_submit'         => form_button('login', $this->locale['global_104'], 'login', ['class' => 'm-t-20 m-b-20 btn-block btn-primary']),
                'registration_link'    => (fusion_get_settings('enable_registration') ? strtr($this->locale['global_105'], ['[LINK]' => "<a href='".BASEDIR."register.php'>", '[/LINK]' => "</a>\n"]) : ''),
                'lostpassword_link'    => strtr($this->locale['global_106'], ['[LINK]' => "<a href='".BASEDIR."lostpassword.php'>", '[/LINK]' => "</a>"])
            ];

        }

        return NULL;
    }
}

$uip = new User_Info();
display_user_info_panel($uip->getInfo());

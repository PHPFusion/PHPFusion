<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: user_info_panel.php
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

use PHPFusion\Admins;

defined( 'IN_FUSION' ) || exit;

require_once __DIR__ . '/templates/user_info.tpl.php';

$userdata = fusion_get_userdata();
$aidlink = fusion_get_aidlink();
$locale = fusion_get_locale();
$modules = Admins::getInstance()->getSubmitData();

if (iMEMBER) {
    $messages_count = dbquery( "SELECT
    SUM(message_folder=0) AS inbox_count,
    SUM(message_folder=1) AS outbox_count,
    SUM(message_folder=2) AS archive_count,
    SUM(message_read=0 AND message_folder=0) AS unread_count
    FROM " . DB_MESSAGES . "
    WHERE message_to=:user_id", [':user_id' => $userdata['user_id']] );

    $messages_count = dbarray( $messages_count );
    $inbox_count = (int)$messages_count['inbox_count'];
    $outbox_count = (int)$messages_count['outbox_count'];
    $archive_count = (int)$messages_count['archive_count'];
    $msg_count = (int)$messages_count['unread_count'];
    $forum_exists = defined( 'FORUM_EXISTS' );
    $forum_settings = get_settings( 'forum' );

    $pm_progress = '';
    if (!iSUPERADMIN) {
        $inbox_cfg = user_pm_settings( $userdata['user_id'], 'user_inbox' );
        if ($inbox_cfg != 0) {
            $inbox_percent = $inbox_cfg > 1 ? number_format( ($inbox_count / $inbox_cfg) * 99 ) : number_format( 0 * 99 );
            $pm_progress .= progress_bar( $inbox_percent, $locale['UM098'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0)] );
        }

        $outbox_cfg = user_pm_settings( $userdata['user_id'], 'user_outbox' );
        if ($outbox_cfg != 0) {
            $outbox_percent = $outbox_cfg > 1 ? number_format( ($outbox_count / $outbox_cfg) * 99 ) : number_format( 0 * 99 );
            $pm_progress .= progress_bar( $outbox_percent, $locale['UM099'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0)] );
        }

        $archive_cfg = user_pm_settings( $userdata['user_id'], 'user_archive' );
        if ($archive_cfg != 0) {
            $archive_percent = $archive_cfg > 1 ? number_format( ($archive_count / $archive_cfg) * 99 ) : number_format( 0 * 99 );
            $pm_progress .= progress_bar( $archive_percent, $locale['UM100'], ['reverse' => TRUE, 'disabled' => ($inbox_cfg == 0)] );
        }
    }

    $submissions_link_arr = [];
    $submissions_link = '';
    if (!empty( $modules )) {
        foreach ($modules as $stype => $title) {
            $submissions_link_arr[$stype] = [
                'link'  => BASEDIR . $title['submit_link'],
                'title' => sprintf( $title['title'], str_replace( '...', '', $locale['UM089'] ) ),
            ];
        }
    }

    $info = [
        'userdata'        => $userdata,
        'user_avatar'     => display_avatar( $userdata, '80px', '', FALSE, 'img-rounded' ),
        'user_name'       => profile_link( $userdata['user_id'], $userdata['user_name'], $userdata['user_status'] ),
        'user_level'      => getuserlevel( $userdata['user_level'] ),
        'forum_exists'    => $forum_exists,
        'show_reputation' => !empty( $forum_settings['forum_show_reputation'] ) && $forum_settings['forum_show_reputation'] ? 1 : 0,
        'pm_msg_count'    => $msg_count,
        'pm_progress'     => $pm_progress,
        'user_pm_link'    => BASEDIR . "messages.php?folder=inbox",
        'user_pm_title'   => sprintf( $locale['UM085'], $msg_count ) . ($msg_count == 1 ? $locale['UM086'] : $locale['UM087']),
        'login_session'   => session_get( 'login_as' ),
        'submissions'     => $submissions_link_arr
    ];

    display_user_info_panel( $info );

} else {

    if (!preg_match( '/login.php/i', FUSION_SELF )) {
        $action_url = FUSION_SELF . (FUSION_QUERY ? "?" . FUSION_QUERY : "");
        if (isset( $_GET['redirect'] ) && strstr( $_GET['redirect'], "/" )) {
            $action_url = cleanurl( urldecode( $_GET['redirect'] ) );
        }
        switch (fusion_get_settings( "login_method" )) {
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
            'login_name_field'     => form_text( 'user_name', $locale['global_101'], '', [
                'input_id'    => 'username_uip',
                'placeholder' => $placeholder,
                'required'    => TRUE
            ] ),
            'login_pass_field'     => form_text( 'user_pass', $locale['global_102'], '', [
                'input_id'    => 'userpass_uip',
                'placeholder' => $locale['global_102'],
                'type'        => 'password',
                'required'    => TRUE
            ] ),
            'login_remember_field' => form_checkbox( 'remember_me', $locale['global_103'], '', ['value' => 'y'] ),
            'login_submit'         => form_button( 'login', $locale['global_104'], '', ['class' => 'm-t-20 m-b-20 btn-block btn-primary'] ),
            'registration'         => (fusion_get_settings( 'enable_registration' ) ? strtr( $locale['global_105'], ['[LINK]' => '<a href="' . BASEDIR . 'register.php">', '[/LINK]' => '</a>'] ) : ''),
            'lostpassword'         => strtr( $locale['global_106'], ['[LINK]' => '<a href="' . BASEDIR . 'lostpassword.php">', '[/LINK]' => '</a>'] ),
            'openform'             => openform( 'userinfopanel_login', 'post', $action_url ),
            'closeform'            => closeform()
        ];

        display_user_info_panel( $info );
    }
}

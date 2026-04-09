<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: administrators.php
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

use PHPFusion\PasswordAuth;
use PHPFusion\UserFields\Reset;

defined('IN_FUSION') || exit;

$locale = fusion_get_locale('', LOCALE . LOCALESET . "admin/admins.php");

$contents = [
    'post'     => 'pf_post',
    'view'     => 'pf_view',
    'button'   => 'pf_button',
    'js'       => 'pf_js',
    'settings' => TRUE,
    'link'     => ( $admin_link ?? '' ),
    'title'    => $locale['ADM_420'],
    'files'    => [
        INCLUDES . 'sendmail_include.php',
        ADMIN . 'administrator/admin_view.php',
        ADMIN . 'administrator/admin_helper.php',
        ADMIN . 'administrator/admin_form.php',
    ],
    'actions'  => [
        'post' => [
            'add_admin'     => 'adminFrm',
            'update_admin'  => 'editadminFrm',
            'cancel_update' => 'editadminFrm',
            'cancel_add'    => 'adminFrm',
            'adminreset'    => 'adminresetFrm'
        ]
    ],
];

/*
 * Remove administrator action
 */

function pf_post() {

    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    if ( admin_post('cancel_add') || admin_post('cancel_update') ) {
        redirect(ADMIN_CURRENT_DIR);
    }

    if ( admin_post('add_admin') ) {

        if ( $rights = post('admin_rights', FILTER_VALIDATE_INT) ) {

            if ( $user_data = fusion_get_user(post('uid', FILTER_VALIDATE_INT)) ) {

                // make admin rights
                $admin_rights = '';
                $admin_level = USER_LEVEL_ADMIN;

                if ( $rights == 3 ) {
                    // make super admin
                    $admin_rights_array = [];
                    $result = dbquery('SELECT DISTINCT admin_rights AS admin_right, admin_language FROM ' . DB_ADMIN . " WHERE admin_language='" . LANGUAGE . "' ORDER BY admin_right");
                    while ( $data = dbarray($result) ) {
                        $admin_rights_array[] = $data['admin_right'];
                    }
                    $admin_rights = implode('.', $admin_rights_array);
                    $admin_level = USER_LEVEL_SUPER_ADMIN;
                }
                else if ( $rights == 4 or $rights < 3 ) {
                    $admin_rights = sanitizer([ 'rights' ], '', 'rights');
                }


                $data = [
                    'admin_uid'       => $user_data['user_id'],
                    'admin_datestamp' => time(),
                    'admin_rights'    => $admin_rights,
                    'admin_code'      => hash_hmac('sha1',
                                                   PasswordAuth::getNewPassword(),
                                                   $user_data['user_id'] . $user_data['user_joined']),
                    'admin_level'     => $admin_level,
                ];


                // Admin cannot promote anyone to superadmin
                if ( fusion_get_userdata('user_level') > $admin_level ) {
                    fusion_stop($locale['ADM_433']);
                }


                if ( fusion_safe() ) {
                    dbquery_insert(DB_NEW_ADMIN, $data, 'save', [ 'primary_key' => 'admin_uid' ]);

                    /*[SENDER] has invited you to join [SITENAME] staff team
                     *
                     * <strong>Welcome</strong><br><br>
                    <div>[SITENAME] is using Fusion Pro to publish contents on the internet. [SITEUSER] has invited you to join the staff team. Please click on the link below to activate your administrator access.</div>
                    <br><div><a href='[LINK_URL]' class='btn btn-inverse'>Click here to activate your account</a></div>
                    <br><div>No idea what Fusion Pro is? Fusion is a simple, beutiful platform for running a community portal online. Find out more.</div>
                    <br><div>If you have trouble activating your [SITENAME] account, you can reach out to [SITEUSER] on [SITEEMAIL] for assistance.</div>
                    <br><div>Have fun, and good luck!</div>
                     */

                    fusion_sendmail('ADMIN_INVITE',
                                    $user_data['user_name'],
                                    $user_data['user_email'],
                                    [
                                        'link_url' => $settings['siteurl'] . 'invite.php?admin_code=' . $data['admin_code']
                                    ]);

                    add_notice('success', $locale['ADM_424']);
                    redirect(ADMIN_CURRENT_DIR);
                }
            }
        }
    }

    if ( admin_post('adminreset') ) {
        (new Reset())->doReset();
    }

    do_admin_update();
}

function pf_view() {

    if ( get('pg') == 'form' ) {
        // actions from admin listing
        do_admin_action();

        // if there are id another page
        if ( check_get('id') ) {

            admin_edit_form();

            ( new Reset() )->resetForm();
        }
        else {

            admin_invite_form();
        }
    }
    else {
        admin_listing();
    }

}

function pf_button()
: string {

    $locale = fusion_get_locale();
    if ( get('pg') == 'form' ) {

        if ( check_get('id') ) {
            return form_button('cancel_update', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-default' ]) .
                   form_button('reset_user', $locale['ADM_471'], $locale['ADM_471'], [ 'class' => 'btn-inverse' ]) .
                   form_button('update_admin', $locale['save'], $locale['save'], [ 'class' => 'btn-primary' ]);
        }

        return form_button('cancel_add', $locale['cancel'], $locale['cancel'], [ 'class' => 'btn-default' ]) .
               form_button('add_admin', $locale['ADM_448'], 'add_admin', [ 'class' => 'btn-primary' ]);
    }

    return '<a class="btn btn-primary" href="' . ADMIN_CURRENT_DIR . '&pg=form"><span>Invite administrator</span></a>';
}


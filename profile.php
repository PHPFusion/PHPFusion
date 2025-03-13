<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: profile.php
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
require_once __DIR__ . '/maincore.php';
require_once FUSION_HEADER;
$locale = fusion_get_locale( '', LOCALE . LOCALESET . "user_fields.php" );
$settings = fusion_get_settings();
if ( check_get( 'lookup' ) && get( 'lookup', FILTER_VALIDATE_INT ) ) {
    require_once THEMES . 'templates/global/profile.tpl.php';

    if ( !iMEMBER && $settings['hide_userprofiles'] == 1 || user_blacklisted( get( 'lookup' ) ) ) {
        redirect( BASEDIR . 'index.php' );
    }

    $user_status = iADMIN ? '' : " AND (u.user_status = '0' OR u.user_status = '3' OR u.user_status = '7')";

    $user_data = [];
    $result = dbquery( "SELECT u.*, s.suspend_reason
        FROM " . DB_USERS . " AS u
        LEFT JOIN " . DB_SUSPENDS . " AS s ON u.user_id = s.suspended_user
        WHERE user_id = :uid" . $user_status . "
        ORDER BY suspend_date DESC
        LIMIT 1", [':uid' => (int)get( 'lookup' )]
    );
    if ( dbrows( $result ) ) {
        $user_data = dbarray( $result );
    } else {
        redirect( BASEDIR . 'index.php' );
    }

    set_title( $locale['u103'] . $locale['global_201'] . $user_data['user_name'] );

    if (iADMIN && checkrights( "UG" ) && get( 'lookup' ) != $user_data['user_id'] ) {
        if ( ( check_post( 'add_to_group' ) ) && ( check_post( 'user_group' ) && post( 'user_group', FILTER_VALIDATE_INT ) ) ) {
            if ( !preg_match( "(^\.{$_POST['user_group']}$|\.{$_POST['user_group']}\.|\.{$_POST['user_group']}$)", $user_data['user_groups'] ) ) {
                dbquery( "UPDATE " . DB_USERS . " SET user_groups = '" . $user_data['user_groups'] . "." . post( 'user_group' ) . "'
                    WHERE user_id = '" . get( 'lookup' ) . "'
                " );
            }
            redirect( FUSION_SELF . "?lookup=" . get( 'lookup' ) );
        }
    }

    $userFields = new PHPFusion\UserFields();
    $userFields->userData = $user_data;
    $userFields->showAdminOptions = TRUE;
    $userFields->method = 'display';
    $userFields->plugin_folder = [INCLUDES . "user_fields/", INFUSIONS];
    $userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
    $userFields->displayProfileOutput();

    \PHPFusion\OpenGraph::ogUserProfile( get( 'lookup' ) );
} else if ( check_get( 'group_id' ) && get( 'group_id', FILTER_VALIDATE_INT ) ) {
    /*
     * Show group
     */
    \PHPFusion\UserGroups::getInstance()->setGroup( get( 'group_id' ) )->showGroup();
} else {
    redirect( BASEDIR . 'index.php' );
}
require_once FUSION_FOOTER;

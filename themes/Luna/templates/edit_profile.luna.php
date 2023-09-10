<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: edit_profile.luna.php
| Author: meangczac (Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Panels;

const INPUT_INLINE = FALSE;


/**
 * Edit profile
 *
 * @param array $info
 */
function display_profile_form( array $info = [] ) {

    // Add panel to the left side
    function navigation_panel( $info ) {

        if (!empty( $info )) {
            $menu = '';
            $_get = get( 'section' );
            $i = 0;
            foreach ($info as $key => $rows) {

                $active = (!$i && !$_get || $_get == $key ? ' active' : '');

                $menu .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">'
                    . '<a class="nav-link d-flex mb-0' . $active . '" href="' . $rows['link'] . '" aria-selected="true" role="tab">' . $rows['title'] . '</a>'
                    . '</li>';
                $i++;
            }
        }

        return fusion_get_function( 'openside', '' )
            . '<ul class="nav nav-tabs nav-pills nav-pills-soft flex-column fw-bold gap-2 border-0" role="tablist">'
            . ($menu ?? '')
            . '</ul>'
            . fusion_get_function( 'closeside' );
    }

    function account( $info ) {

        $locale = fusion_get_locale();

        $html = fusion_get_function( 'openside', $locale['uf_100'] );
        $html .= '<p>General user account and public profile information.</p>';

        $html .= openform( 'lunaFrm', 'POST' );

        $html .= '<div class="row">' .
            '<div class="col-xs-12 col-sm-12 col-md-4">' . $info['user_firstname'] . '</div>' .
            '<div class="col-xs-12 col-sm-12 col-md-4">' . $info['user_lastname'] . '</div>' .
            '<div class="col-xs-12 col-sm-12 col-md-4">' . $info['user_addname'] . '</div>' .
            '</div>';

        $html .= $info['user_name'];

        $html .= '<div class="row">' .
            '<div class="col-xs-12 col-sm-12 col-md-6">' . $info['user_phone'] . '</div>' .
            '<div class="col-xs-12 col-sm-12 col-md-6">' . $info['user_email'] . '</div>' .
            '</div>';
        $html .= '<div class="row"><div class="col-xs-12">';
        $html .= $info['user_hide_phone'];
        $html .= '</div></div>';

        $html .= '<div class="row"><div class="col-xs-12">';
        $html .= $info['user_hide_email'];
        $html .= '</div></div>';

        $html .= '<div class="row"><div class="col-xs-12">';
        $html .= $info['user_bio'];
        $html .= '</div></div>';

        // this section update this
        $html .= $info['user_hash'];
        $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
        $html .= closeform();
        $html .= fusion_get_function( 'closeside' );

        // Password
        $html .= fusion_get_function( 'openside', $locale['u129'] );
        $html .= '<p>He moonlights difficult engrossed it, sportsmen. Interested has all Devonshire difficulty gay assistance joy. Unaffected at ye of compliment alteration to.</p>';
        $html .= openform( 'lunaPassFrm', 'POST' );
        $html .= $info['user_password'];
        $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
        $html .= closeform();
        $html .= fusion_get_function( 'closeside' );

        // Admin Password
        if ($info['user_admin_password']) {
            $html .= fusion_get_function( 'openside', $locale['u129'] );
            $html .= '<p>He moonlights difficult engrossed it, sportsmen. Interested has all Devonshire difficulty gay assistance joy. Unaffected at ye of compliment alteration to.</p>';
            $html .= openform( 'lunaAdminPassFrm', 'POST' );
            $html .= $info['user_admin_password'];
            $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
            $html .= closeform();
            $html .= fusion_get_function( 'closeside' );
        }

        if (!empty( $info['user_field'] )) {
            $i = 1;
            foreach ($info['user_field'] as $field) {
                $html .= fusion_get_function( 'openside', $field['title'] );
                $html .= openform( 'lunaCustom_' . $i, 'POST', FORM_REQUEST );
                if (!empty( $field['fields'] )) {
                    foreach ($field['fields'] as $subfield) {
                        $html .= '<div class="row"><div class="col-xs-12">';
                        $html .= $subfield;
                        $html .= '</div></div>';
                    }
                }
                $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
                $html .= closeform();
                $html .= fusion_get_function( 'closeside' );

                $i++;
            }
        }

        return $html;
    }

    function notifications( $info ) {
        $locale = fusion_get_locale();

        $html = fusion_get_function( 'opentable', $locale['u500'] );
        $html .= '<p>' . $locale['u501'] . '</p>';
        $html .= openform( 'notifyFrm', 'POST' );
        $html .= $info['user_hash'];
        $html .= $info['user_comments_notify'];
        $html .= '<div class="hr"></div>';
        $html .= $info['user_tag_notify'];
        $html .= '<div class="hr"></div>';
        $html .= $info['user_newsletter_notify'];
        $html .= '<div class="hr"></div>';
        $html .= $info['user_follow_notify'];
        $html .= '<div class="hr"></div>';
        $html .= $info['user_pm_notify'];
        $html .= '<div class="hr"></div>';
        $html .= opencollapse( 'email', 'accordion-flush' );
        $html .= opencollapsebody( $locale['u510'] . '<p class="small">' . $locale['u511'] . '</p>', 'x1', 'email', TRUE );
        $html .= $info['user_pm_email'];
        $html .= $info['user_follow_email'];
        $html .= $info['user_feedback_email'];
        $html .= '<div class="hr mb-3"></div>';
        $html .= $info['user_email_duration'];
        $html .= closecollapsebody() . closecollapse();
        $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['notify_button'] . '</div>';
        $html .= closeform();
        $html .= fusion_get_function( 'closetable', '' );

        return $html;
    }

    function privacy( $info ) {

        $locale = fusion_get_locale();

        $html = fusion_get_function( 'opentable', $locale['u600'] );
        $html .= '<p>' . $locale['u601'] . '</p>';

        if ($view = get( 'd' )) {

            function display_two_step( $info ) {
                $locale = fusion_get_locale();

                // Email template checks
                if (check_get( 'auth' )) {

                    fusion_confirm_exit();

                    $html = '<h6>' . $locale['u606'] . '</h6>';
                    $html .= '<p>' . strtr( $locale['u607'], ['[EMAIL]' => '<strong>' . $info['email_display'] . '</strong>'] ) . '</p>';
                    $html .= openform( 'privacyFrm', 'POST' );
                    $html .= $info['user_id'];
                    $html .= $info['user_hash'];
                    $html .= '<div class="mb-4">' . $info['user_code'] . '</div>';
                    $html .= $info['button'];
                    $html .= '<div class="small mt-2">' . $locale['u609'] . '</div>';
                    $html .= closeform();

                } else {

                    $html =
                        openform( 'authFrm', 'POST', clean_request( 'auth=1', ['auth'], FALSE ) ) .
                        '<strong>' . $locale['u602'] . '</strong>' .
                        '<p>' . $locale['u603'] . '</p>' .
                        '<p>' . $locale['u604'] . '</p>' .
                        $info['get_auth'] .
                        closeform();
                }

                return $html;
            }

            function display_login_activity( $info ) {

                $locale = fusion_get_locale();

                $html = '<h6>Login sessions</h6>';
                $html .= '<div class="mb-3">The information listed are logged sessions that has accessed your account.</div>';

                if (!empty( $info )) {
                    $i = 0;
                    foreach ($info['user_logins'] as $session) {
                        if (!$i) {
                            $html .= '<div class="text-dark-emphasis"><strong><small>Current Session</small></strong></div>';
                        } else {
                            $html .= '<div class="text-dark-emphasis mt-3 mb-3"><small>Last accessed</small><br>' . timer( $session['user_logintime'], FALSE, $locale['ago'] ) . '</div>';
                        }
                        $html .= '<div class="mt-3 mb-3"><small>Details</small></div>';
                        $html .= '<div class="mb-3">Session ID:<br>' . $session['user_session'] . '</div>';
                        $html .= '<div class="mb-3">' . $session['user_os'] . ' on ' . $session['user_browser'] . '</div>';
                        $html .= '<div class="mb-3">IP address:<br>' . $session['user_ip'] . '</div>';

                        if (!$i) {
                            $html .= '<a href="' . FUSION_SELF . '?logout=yes" class="btn btn-primary-soft btn-block">End current session and log out</a>';
                        }
                        $html .= '<div class="hr"></div>';
                        $i++;
                    }
                }

                return $html;
            }

            function display_data_activity( $info ) {
                $locale = fusion_get_locale( '', LOCALE . LOCALESET . 'admin/user_log.php' );
                $settings = fusion_get_settings();

                $html = '<h6>Activity</h6>';
                $html .= '<div class="mb-3">' . sprintf( $locale['UL_021'], $settings['sitename'] ) . '</div>';
                if (!empty( $info['user_log'] )) {
                    $html .= opencollapse( 'dataActivity', 'accordion-flush' );
                    foreach ($info['user_log'] as $log_id => $rows) {
                        $html .= opencollapsebody( '<p class="mb-3"><small class="text-normal">' . showdate( 'forumdate', $rows['userlog_time'] ) . '</small></p>' . $rows['title'], 'log_' . $log_id, '' );
                        $html .= $rows['description'];
                        $html .= closecollapsebody();
                    }
                    $html .= closecollapse();
                } else {
                    $html .= '<div class="my-3">' . $locale['UL_015'] . '</div>';
                }
                return $html;
            }

            function display_social_logins( $info ) {
                return '';
            }

            $html .= '<p><small><a href="' . clean_request( '', ['d'], FALSE ) . '">' . get_icon( 'left', 'me-1', $locale['back'] ) . $locale['back'] . '</a></small></p>';

            $html .= match ($view) {
                default => 'Content not found',
                'twostep' => display_two_step( $info ),
                'records' => display_login_activity( $info ),
                'data' => display_data_activity( $info ),
                'login' => display_social_logins( $info ),
            };

        } else {
            $html .= '<div class="list-group">';
            $html .= '<div class="list-group-item d-flex align-items-center">';
            $html .= '<h6>' . $locale['u602'] . '</h6>';
            $html .= '<a href="' . $info['twostep_url'] . '" class="ms-auto btn btn-sm btn-primary-soft">Setup</a>';
            $html .= '</div>';
            $html .= '<div class="list-group-item d-flex align-items-center">';
            $html .= '<h6>Login activity</h6>';
            $html .= '<a href="' . $info['records_url'] . '" class="ms-auto btn btn-sm btn-primary-soft">View</a>';
            $html .= '</div>';
            $html .= '<div class="list-group-item d-flex align-items-center">';
            $html .= '<h6>Manage your data and activity</h6>';
            $html .= '<a href="' . $info['data_url'] . '" class="ms-auto btn btn-sm btn-primary-soft">View</a>';
            $html .= '</div>';
            $html .= '<div class="list-group-item d-flex align-items-center">';
            $html .= '<h6>Social Login</h6>';
            $html .= '<a href="' . $info['login_url'] . '" class="ms-auto btn btn-sm btn-primary-soft">Bindings</a>';
            $html .= '</div>';
            $html .= '</div>';
        }


        $html .= fusion_get_function( 'closetable', '' );

        return $html;
    }


    Panels::getInstance()->hidePanel( 'RIGHT' );
    Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1 );

    echo '<!--editprofile_pre_idx-->';
    echo match (get( 'section' )) {
        default => account( $info ),
        'notifications' => notifications( $info ),
        'privacy' => privacy( $info ),
    };
    echo '<!--editprofile_sub_idx-->';

}
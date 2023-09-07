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
        $html .= $info['user_id'];
        $html .= $info['user_hash'];
        $html .= $info['user_comment_notify'];
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

    Panels::getInstance()->hidePanel( 'RIGHT' );
    Panels::addPanel( 'navigation_panel', navigation_panel( $info['section'] ), 1 );

    echo '<!--editprofile_pre_idx-->';
    echo match (get( 'section' )) {
        default => account( $info ),
        'notifications' => notifications( $info ),
    };
    echo '<!--editprofile_sub_idx-->';

}
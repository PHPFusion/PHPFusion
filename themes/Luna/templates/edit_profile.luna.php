<?php
/*
 * -------------------------------------------------------+
 * | PHPFusion Content Management System
 * | Copyright (C) PHP Fusion Inc
 * | https://phpfusion.com/
 * +--------------------------------------------------------+
 * | Filename: edit_profile.luna.php
 * | Author:  meangczac (Chan)
 * +--------------------------------------------------------+
 * | This program is released as free software under the
 * | Affero GPL license. You can redistribute it and/or
 * | modify it under the terms of this license which you
 * | can read by viewing the included agpl.txt or online
 * | at www.gnu.org/licenses/agpl.html. Removal of this
 * | copyright header is strictly prohibited without
 * | written permission from the original author(s).
 * +--------------------------------------------------------
 */

use PHPFusion\Panels;
const INPUT_INLINE = FALSE;
/**
 * Edit profile
 *
 * @param array $info
 */
function display_profile_form( array $info = [] ) {

    // Add panel to the left side
    function navigation_panel() {
        $html = fusion_get_function( 'openside', '' );

        $html .= '<ul class="nav nav-tabs nav-pills nav-pills-soft flex-column fw-bold gap-2 border-0" role="tablist">';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0 active" href="#profile-settings-1" data-bs-toggle="tab" aria-selected="true" role="tab">Account</a>';
        $html .= '</li>';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0" href="#notifications" data-bs-toggle="tab" aria-selected="true" role="tab">Notifications</a>';
        $html .= '</li>';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0" href="#privacy" data-bs-toggle="tab" aria-selected="true" role="tab">Privacy and safety</a>';
        $html .= '</li>';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0" href="#communications" data-bs-toggle="tab" aria-selected="true" role="tab">Communications</a>';
        $html .= '</li>';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0" href="#messaging" data-bs-toggle="tab" aria-selected="true" role="tab">Messaging</a>';
        $html .= '</li>';

        $html .= '<li class="nav-item" data-bs-dismiss="offcanvas" role="presentation">';
        $html .= '<a class="nav-link d-flex mb-0" href="#close" data-bs-toggle="tab" aria-selected="true" role="tab">Close account</a>';
        $html .= '</li>';

        $html .= '</ul>';

        $html .= fusion_get_function( 'closeside' );

        return $html;
    }

    function account( $info ) {

        $locale = fusion_get_locale();

        $html = fusion_get_function( 'openside', $locale['uf_100'] );
        $html .= '<p>General user account and public profile information.</p>';

        $html .= openform('lunaFrm', 'POST');

        $html .= '<div class="row">'.
        '<div class="col-xs-12 col-sm-12 col-md-4">'.$info['user_firstname'].'</div>'.
        '<div class="col-xs-12 col-sm-12 col-md-4">'.$info['user_lastname'].'</div>'.
        '<div class="col-xs-12 col-sm-12 col-md-4">'.$info['user_addname'].'</div>'.
            '</div>';

        $html .= $info['user_name'];

        $html .= '<div class="row">'.
            '<div class="col-xs-12 col-sm-12 col-md-6">'.$info['user_phone'].'</div>'.
            '<div class="col-xs-12 col-sm-12 col-md-6">'.$info['user_email'].'</div>'.
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
        $html .= openform('lunaPassFrm', 'POST');
        $html .= $info['user_password'];
        $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
        $html .= closeform();
        $html .= fusion_get_function( 'closeside' );

        // Admin Password
        if ($info['user_admin_password']) {
            $html .= fusion_get_function( 'openside', $locale['u129'] );
            $html .= '<p>He moonlights difficult engrossed it, sportsmen. Interested has all Devonshire difficulty gay assistance joy. Unaffected at ye of compliment alteration to.</p>';
            $html .= openform('lunaAdminPassFrm', 'POST');
            $html .= $info['user_admin_password'];
            $html .= '<div class="d-flex flex-row justify-content-end m-t-20">' . $info['button'] . '</div>';
            $html .= closeform();
            $html .= fusion_get_function( 'closeside' );
        }

        if (!empty( $info['user_field'] )) {
            $i = 1;
            foreach ($info['user_field'] as $field) {
                $html .= fusion_get_function( 'openside', $field['title'] );
                $html .= openform('lunaCustom_'.$i, 'POST', FORM_REQUEST);
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

    Panels::addPanel( 'navigation_panel', navigation_panel(), 1 );


    $opentab = '';
    $closetab = '';
    if (!empty( $info['tab_info'] )) {
        $opentab = opentab( $info['tab_info'], check_get( 'section' ) ? get( 'section' ) : 1, 'user-profile-form', TRUE );
        $closetab = closetab();
    }


    echo '<div class="tab-content py-0 mb-0">';
    echo '<div class="tab-pane fade active show" id="profile-settings-1" role="tabpanel">' . account( $info ) . '</div>';
    echo '</div>';

    opentable( '' );
    echo $opentab;
    echo "<!--editprofile_pre_idx--><div id='profile_form' class='spacer-sm'>";
    echo $info['validate'];
    echo $info['terms'];
    echo "</div><!--editprofile_sub_idx-->";
    echo $closetab;
    closetable();

}
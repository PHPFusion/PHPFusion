<?php

const FLOATING_LABEL = TRUE;


function display_login_form( $info ) {

    $settings = fusion_get_settings();
    $locale = fusion_get_locale();

    if (iMEMBER) {

        redirect( BASEDIR . $settings['opening_page'] );

    } else {

        define('LUNA_BODY_CLASS', 'login');


        echo "<!--login_pre_idx-->";
        opentable( $locale['global_100'] . '<p class="small">Stay updated on your professional world</p>' );
        echo openform( 'loginFrm', 'POST' ) .
            $info['user_name'] .
            $info['user_pass'] .
            $info['remember_me'] .
            '<p class="my-3 strong">' . $info['forgot_password_link'] . '</p>' .
            $info['login_button'] .
            closeform();
        // Facebook, Google Auth, etc.
        if (!empty( $info['connect_buttons'] )) {
            echo "
        <hr/>
        ";
            foreach ($info['connect_buttons'] as $mhtml) {
                echo $mhtml;
            }
        }
        closetable();
        echo "<!--login_sub_idx-->";
        echo '<p class="lreg-link">'.$info['registration_link'].'</p>';


    }

}
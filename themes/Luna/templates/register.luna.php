<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: register.luna.php
| Author: meangczac (Chan)
| PHPFusion Lead Developer, PHPFusion Core Developer
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
 * Registration Form Template
 * echo output design in compatible with Version 7.xx theme set.
 *
 * @param $info - the array output that is accessible for your custom requirements
 */
function display_register_form( array $info = [] ) {
    Panels::getInstance()->hideAll();
    $settings = fusion_get_settings();
    $locale = fusion_get_locale();

    define( 'LUNA_BODY_CLASS', 'register' );
    
    echo '<!--HTML--><h2 class="text-center w-100 mt-4 mb-4">' . $locale['slogan'] . '</h2>';
    opentable( '' );
    echo "<!--register_pre_idx-->";
    echo openform( 'registerFrm', 'POST' ) .
        $info['user_name'] .
        $info['user_email'] .
        $info['user_avatar'] .
        $info['user_password'] .
        $info['user_admin_password'] .
        $info['user_custom'] .
        $info['validate'] .
        ($info['terms'] ? '<div class="mt-3 mb-3">' . $info['terms'] . '</div>' : '') .
        $info['user_id'] .
        form_button( 'register', $locale['u101'], 'register', ['class' => 'btn-block btn-lg btn-primary mt-3'] ) .
        closeform();
//    echo '<div class="hr"><span>or</span></div>';
    echo '<div class="text-center mt-5">' . strtr( $locale['u400'], ['[SITENAME]' => $settings['sitename']] ) . ' <a href="' . BASEDIR . 'login.php">' . $locale['login'] . '</a>';
    echo "<!--register_sub_idx-->";
    closetable();
    echo "<!--//HTML-->";
}


function display_gateway( $info ) {

    Panels::getInstance()->hideAll();

    $locale = fusion_get_locale();

    if ($info['showform']) {

        echo '<h2 class="text-center w-100 mt-4 mb-4">' . $locale['gateway_069'] . '</h2>';

        opentable( 'Please answer <p class="small">' . $info['gateway_question'] . '</p>' );
        echo $info['openform'];
        echo $info['hiddeninput'];
        echo $info['textinput'];
        echo $info['button'];
        echo $info['closeform'];
        closetable();

    } else if (!isset( $_SESSION["validated"] )) {
        echo '<div class="well text-center"><h3 class="m-0">' . $locale['gateway_068'] . '</h3></div>';
    }

    if (isset( $info['incorrect_answer'] ) && $info['incorrect_answer'] == TRUE) {
        opentable( $locale['gateway_069'] );
        echo '<div class="well text-center"><h3 class="m-0">' . $locale['gateway_066'] . '</h3></div>';
        echo '<input type="button" value="' . $locale['gateway_067'] . '" class="text-center btn btn-info spacer-xs" onclick="location=\'' . BASEDIR . 'register.php\'"/>';
        closetable();
    }

}
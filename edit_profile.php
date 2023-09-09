<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: edit_profile.php
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

require_once THEMES . 'templates/header.php';

$locale = fusion_get_locale( '', LOCALE . LOCALESET . "user_fields.php" );

include THEMES . "templates/global/profile.tpl.php";
//require_once THEMES . 'templates/global/profile.tpl.php';

if (!iMEMBER) {
    redirect( "index.php" );
}

add_to_title( $locale['u102'] );

$info = [];
$errors = [];
$settings = fusion_get_settings();
$post_name = 'update_profile';

if ($settings['email_verification'] == 1) {

    if (check_get('code')) {
        $userInput = new PHPFusion\UserFieldsInput();
        $userInput->verifyCode( get( 'code' ) );
        redirect( FUSION_REQUEST );
    }

    $result = dbquery( "SELECT user_email FROM " . DB_EMAIL_VERIFY . " WHERE user_id='" . fusion_get_userdata( 'user_id' ) . "'" );
    if (dbrows( $result )) {
        $data = dbarray( $result );
        $info['email_notification'] = sprintf( $locale['u200'], $data['user_email'] ) . "\n<br />\n" . $locale['u201'];
    }
}

// Always invoke because there are multiple user forms in this class
$userInput = new PHPFusion\UserFieldsInput();
$userInput->username_change = fusion_get_settings( 'username_change' );
$userInput->verifyNewEmail = TRUE;
$userInput->registration = FALSE;
$userInput->userData = fusion_get_userdata();
if ($userInput->saveUpdate() === TRUE) {
    redirect( FUSION_REQUEST );
}


$userFields = new PHPFusion\UserFields();
$userFields->postName = $post_name;
$userFields->postValue = $locale['u105'];
$userFields->userData = fusion_get_userdata();
$userFields->plugin_folder = [INCLUDES . "user_fields/", INFUSIONS];
$userFields->plugin_locale_folder = LOCALE . LOCALESET . "user_fields/";
$userFields->username_change = $settings['username_change'];// setUserNameChange( fusion_get_settings( "username_change" ) );
$userFields->registration = FALSE;
$userFields->inputInline = (!defined( 'INPUT_INLINE' ) || INPUT_INLINE);
$userFields->displayProfileInput();

if (!defined( 'EDITPROFILE_JS_CHECK' )) {
    define( 'EDITPROFILE_JS_CHECK', TRUE );
    add_to_jquery( '
        function delayKeyupTimer(callback, ms) {
            let timer = 0;
            return function () {
                let context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, ms || 0);
            };
        }

        // User Password check
        let r_userpass1 = $("#userfieldsform #user_password1");
        let r_userpass1_field = $("#userfieldsform #user_password1-field"); // BS3
        r_userpass1.keyup(delayKeyupTimer(function () {
            $.ajax({
                url: "' . INCLUDES . 'api/?api=userpass-check",
                method: "GET",
                data: $.param({"pass": $(this).val()}),
                dataType: "json",
                success: function (e) {
                    $(".userpass-checker").remove();

                    if (e.result === "valid") {
                        r_userpass1.addClass("is-valid").removeClass("is-invalid");
                        r_userpass1_field.addClass("has-success").removeClass("has-error"); // BS3
                    } else if (e.result === "invalid") {
                        r_userpass1.addClass("is-invalid").removeClass("is-valid");
                        r_userpass1_field.addClass("has-error").removeClass("has-success"); // BS3
                        let feedback_html = "<div class=\"userpass-checker invalid-feedback help-block\">" + e.response + "</div>";
                        if (r_userpass1_field.find(".input-group").length > 0) {
                            r_userpass1_field.find(".input-group").after(feedback_html);
                        } else {
                            r_userpass1.after(feedback_html);
                        }
                    }
                }
            });
        }, 400));

        // Admin Password check
        let r_adminpass1 = $("#userfieldsform #user_admin_password1");
        let r_adminpass1_field = $("#userfieldsform #user_admin_password1-field"); // BS3
        r_adminpass1.keyup(delayKeyupTimer(function () {
            $.ajax({
                url: "' . INCLUDES . 'api/?api=userpass-check",
                method: "GET",
                data: $.param({"pass": $(this).val()}),
                dataType: "json",
                success: function (e) {
                    $(".adminpass-checker").remove();

                    if (e.result === "valid") {
                        r_adminpass1.addClass("is-valid").removeClass("is-invalid");
                        r_adminpass1_field.addClass("has-success").removeClass("has-error"); // BS3
                    } else if (e.result === "invalid") {
                        r_adminpass1.addClass("is-invalid").removeClass("is-valid");
                        r_adminpass1_field.addClass("has-error").removeClass("has-success"); // BS3
                        let feedback_html = "<div class=\"adminpass-checker invalid-feedback help-block\">" + e.response + "</div>";
                        if (r_adminpass1_field.find(".input-group").length > 0) {
                            r_adminpass1_field.find(".input-group").after(feedback_html);
                        } else {
                            r_adminpass1.after(feedback_html);
                        }
                    }
                }
            });
        }, 400));
    ' );
}

require_once THEMES . 'templates/footer.php';

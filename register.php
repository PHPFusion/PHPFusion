<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: register.php
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
require_once __DIR__.'/maincore.php';
require_once THEMES.'templates/header.php';
$locale = fusion_get_locale("", LOCALE.LOCALESET."user_fields.php");
$settings = fusion_get_settings();
require_once THEMES."templates/global/profile.tpl.php";
add_to_title($locale['global_107']);
add_to_meta("keywords", $locale['global_107']);
$_GET['profiles'] = 1;

if (iMEMBER || $settings['enable_registration'] == 0) {
    redirect(BASEDIR.'index.php');
}

if ($settings['gateway'] == 1) {
    if (empty($_SESSION["validated"])) {
        $_SESSION['validated'] = 'False';
    }
    if (isset($_SESSION["validated"]) && $_SESSION['validated'] !== 'True') {
        require_once INCLUDES."gateway/gateway.php";
    }
}

if ((isset($_SESSION["validated"]) && $_SESSION["validated"] == "True") || $settings['gateway'] == 0) {
    $errors = [];

    if (check_get('email') && check_get('code')) {
        if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", get('email'))) {
            redirect("register.php?error=activate");
        }

        if (!preg_check("/^[0-9a-z]{40}$/", get('code'))) {
            redirect("register.php?error=activate");
        }

        $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code=:code AND user_email=:email", [
            ':code' => get('code'), ':email' => get('email')
        ]);

        if (dbrows($result) > 0) {

            add_to_title($locale['u155']);

            $data = dbarray($result);

            $user_info = unserialize(base64_decode($data['user_info']));

            if (empty($user_info['user_hide_email'])) {
                $user_info['user_hide_email'] = 1;
            }

            dbquery_insert(DB_USERS, $user_info, 'save');

            $result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code=:code LIMIT 1", [':code' => get('code')]);

            if ($settings['admin_activation'] == 1) {
                addnotice("info", $locale['u171']." - ".$locale['u162'], $settings["opening_page"]);
            } else {
                addnotice("info", $locale['u171']." - ".$locale['u161'], $settings["opening_page"]);
            }

        }
        redirect($settings['opening_page']);

    } else if (check_post('register')) {

        $userInput = new PHPFusion\UserFieldsInput();
        $userInput->validation = $settings['display_validation'];
        $userInput->emailVerification = $settings['email_verification'];
        $userInput->adminActivation = $settings['admin_activation'];
        $userInput->moderation = 0;
        $userInput->skipCurrentPass = 1;
        $userInput->registration = 1;

        $insert = $userInput->saveInsert();

        if (fusion_safe()) {
            redirect($settings['opening_page']);
        }

        unset($userInput);
    }

    if (!check_get('email') && !check_get('code')) {

        $userFields = new PHPFusion\UserFields();
        $userFields->postName = "register";
        $userFields->postValue = $locale['u101'];
        $userFields->displayValidation = $settings['display_validation'];

        $userFields->displayTerms = $settings['enable_terms'];

        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";

        $userFields->showAdminPass = FALSE;
        $userFields->skipCurrentPass = TRUE;

        $userFields->registration = TRUE;
        $userFields->inputInline = (!defined( 'INPUT_INLINE' ) || INPUT_INLINE);

        $userFields->displayProfileInput();

        if (!defined('REGISTER_JS_CHECK')) {
            define('REGISTER_JS_CHECK', TRUE);
            add_to_jquery('
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

                // Username check
                let r_username = $("#userfieldsform #user_name");
                let r_username_field = $("#userfieldsform #user_name-field"); // BS3
                r_username.keyup(delayKeyupTimer(function () {
                    $.ajax({
                        url: "'.INCLUDES.'api/?api=username-check",
                        method: "GET",
                        data: $.param({"name": $(this).val()}),
                        dataType: "json",
                        success: function (e) {
                            $(".username-checker").remove();

                            if (e.result === "valid") {
                                r_username.addClass("is-valid").removeClass("is-invalid");
                                r_username_field.addClass("has-success").removeClass("has-error"); // BS3
                            } else if (e.result === "invalid") {
                                r_username.addClass("is-invalid").removeClass("is-valid");
                                r_username_field.addClass("has-error").removeClass("has-success"); // BS3
                                let feedback_html = "<div class=\"username-checker invalid-feedback help-block\">" + e.response + "</div>";
                                r_username.after(feedback_html);
                            }
                        }
                    });
                }, 400));

                // Password check
                let r_userpass1 = $("#userfieldsform #user_password1");
                let r_userpass1_field = $("#userfieldsform #user_password1-field"); // BS3
                r_userpass1.keyup(delayKeyupTimer(function () {
                    $.ajax({
                        url: "'.INCLUDES.'api/?api=userpass-check",
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
            ');
        }
    }
}

require_once THEMES.'templates/footer.php';

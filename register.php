<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: register.php
| Author: PHP-Fusion Development Team
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
add_to_title($locale['global_107']);
add_to_meta("keywords", $locale['global_107']);

require_once THEMES."templates/global/profile.php";

if (iMEMBER || $settings['enable_registration'] == 0) {
    redirect(BASEDIR.'index.php');
}

if ($settings['gateway'] == 1) {
    if (session_get('validated') !== 'TRUE') {
        require_once BASEDIR."gateway/gateway.php";
    }
}

if ($settings['gateway'] == 1 && session_get('validated') == 'TRUE' || $settings['gateway'] == 0) {
    $errors = [];
    $email = get('email');
    $code = get('code');

    if ($email && $code) {
        if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $email)) {
            redirect("register.php?error=activate");
        }

        if (!preg_check("/^[0-9a-z]{40}$/", $code)) {
            redirect("register.php?error=activate");
        }

        $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code=:code AND user_email=:email", [':code' => $code, ':email' => $email]);

        if (dbrows($result) > 0) {

            add_to_title($locale['global_200'].$locale['u155']);

            $data = dbarray($result);

            $user_info = unserialize(base64_decode($data['user_info']));

            dbquery_insert(DB_USERS, $user_info, 'save');

            $result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code=:code LIMIT 1", [':code' => $_GET['code']]);

            if ($settings['admin_activation'] == 1) {
                addNotice("success", $locale['u171']." - ".$locale['u162'], 'all');
            } else {
                addNotice("success", $locale['u171']." - ".$locale['u161'], 'all');
            }
            redirect($settings['opening_page']);

        } else {
            redirect($settings['opening_page']);
        }

    } else {

        $userFields = new PHPFusion\UserFields();
        $userFields->post_name = "register";
        $userFields->post_value = $locale['u101'];
        $userFields->display_validation = $settings['display_validation'];
        $userFields->display_terms = $settings['enable_terms'];
        $userFields->plugin_folder = [INCLUDES."user_fields/", INFUSIONS];
        $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
        $userFields->show_admin_password = FALSE;
        $userFields->skip_password = TRUE;
        $userFields->registration = TRUE;
        $userFields->inline_field = FALSE;

        $userInput = new PHPFusion\UserFieldsInput();
        $userInput->validation = $settings['display_validation'];
        $userInput->email_verification = $settings['email_verification'];
        $userInput->admin_activation = $settings['admin_activation'];
        $userInput->hide_user_email = TRUE; // make settings for this.
        $userInput->skip_password = TRUE;
        $userInput->registration = TRUE;
        $userInput->post_name = 'register';
        $userInput->redirect_uri = BASEDIR.$settings['opening_page'];
        $userInput->saveInsert();

        echo $userFields->registration();
    }
}

require_once THEMES.'templates/footer.php';

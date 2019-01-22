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
require_once THEMES.'templates/global/profile.php';

$locale = fusion_get_locale("", LOCALE.LOCALESET."user_fields.php");
add_to_title($locale['global_107']);
add_to_meta("keywords", $locale['global_107']);
$_GET['profiles'] = 1;
$settings = fusion_get_settings();
if (iMEMBER || $settings['enable_registration'] == 0) {
    redirect(BASEDIR.'index.php');
}

if (isset($_GET['email']) && isset($_GET['code'])) {
    if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $_GET['email'])) {
        redirect(BASEDIR."register.php?error=activate");
    }
    if (!preg_check("/^[0-9a-z]{40}$/", $_GET['code'])) {
        redirect(BASEDIR."register.php?error=activate");
    }
    $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code=:code AND user_email=:email", [':code' => $_GET['code'], ':email' => $_GET['email']]);
    if (dbrows($result) > 0) {
        add_to_title($locale['global_200'].$locale['u155']);
        $data = dbarray($result);
        $user_info = unserialize(base64_decode($data['user_info']));
        dbquery_insert(DB_USERS, $user_info, 'save');
        $result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code=:code LIMIT 1", [':code' => $_GET['code']]);
        if ($settings['admin_activation']) {
            addNotice("success", "<strong>".$locale['u171']."</strong> ".$locale['u162'], 'all');
        } else {
            addNotice("success", "<strong>".$locale['u171']."</strong> ".$locale['u161'], 'all');
        }
        redirect(BASEDIR.$settings['opening_page']);
    } else {
        redirect(BASEDIR.$settings['opening_page']);
    }
} else {

    if (isset($_SESSION['validated']) && $_SESSION["validated"] == "True") {

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
        $userFields->is_admin_panel = FALSE;
        $userFields->inline_field = FALSE;
        echo $userFields->display_input();

    }
}

require_once THEMES.'templates/footer.php';

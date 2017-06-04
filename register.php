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
require_once dirname(__FILE__).'/maincore.php';
require_once THEMES."templates/header.php";
$locale = fusion_get_locale("", LOCALE.LOCALESET."user_fields.php");
//require_once THEMES."templates/global/register.php";
require_once THEMES."templates/global/profile.php";
add_to_title($locale['global_107']);
add_to_meta("keywords", $locale['global_107']);
$_GET['profiles'] = 1;

if (iMEMBER or fusion_get_settings('enable_registration') == 0) {
    redirect("index.php");
}

$errors = array();

if (isset($_GET['email']) && isset($_GET['code'])) {

    if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $_GET['email'])) {
        redirect("register.php?error=activate");
    }

    if (!preg_check("/^[0-9a-z]{40}$/", $_GET['code'])) {
        redirect("register.php?error=activate");
    }

    $result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code='".$_GET['code']."' AND user_email='".$_GET['email']."'");

    if (dbrows($result) > 0) {

        add_to_title($locale['global_200'].$locale['u155']);

        $data = dbarray($result);

        $user_info = unserialize(base64_decode($data['user_info']));

        dbquery_insert(DB_USERS, $user_info, 'save');

        $result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code='".$_GET['code']."' LIMIT 1");

        if (fusion_get_settings('admin_activation') == 1) {
            addNotice("success", $locale['u171']." - ".$locale['u162'], 'all');
        } else {
            addNotice("success", $locale['u171']." - ".$locale['u161'], 'all');
        }
        redirect(fusion_get_settings('opening_page'));

    } else {
        redirect(fusion_get_settings('opening_page'));
    }

} elseif (isset($_POST['register'])) {

    $userInput = new PHPFusion\UserFieldsInput();
    $userInput->validation = $settings['display_validation'];
    $userInput->emailVerification = $settings['email_verification'];
    $userInput->adminActivation = $settings['admin_activation'];
    $userInput->skipCurrentPass = TRUE;
    $userInput->registration = TRUE;
    $insert = $userInput->saveInsert();

    if ($insert && $defender->safe()) {
        redirect(fusion_get_settings('opening_page'));
    }
    unset($userInput);

}

if (!isset($_GET['email']) && !isset($_GET['code'])) {
    $userFields = new PHPFusion\UserFields();
    $userFields->postName = "register";
    $userFields->postValue = $locale['u101'];
    $userFields->displayValidation = $settings['display_validation'];
    $userFields->displayTerms = $settings['enable_terms'];
    $userFields->plugin_folder = INCLUDES."user_fields/";
    $userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
    $userFields->showAdminPass = FALSE;
    $userFields->skipCurrentPass = TRUE;
    $userFields->registration = TRUE;
    $userFields->display_profile_input();
}

require_once THEMES."templates/footer.php";
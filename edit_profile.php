<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: edit_profile.php
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
$locale = fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');
$user_name_change = fusion_get_settings('userNameChange');
require_once THEMES."templates/global/profile.php";

// The output class
$userFields = \PHPFusion\UserFields::getInstance();
$userFields->post_name = "update_profile";
$userFields->post_value = $locale['u105'];
$userFields->user_data = fusion_get_userdata();
$userFields->user_name_change = $user_name_change;
$userFields->skip_password = TRUE;
$userFields->registration = FALSE;
$userFields->method = 'input';
//
// // The input class
$userInput = \PHPFusion\UserFieldsInput::get_instance();
$userInput->post_name = 'update_profile';
$userInput->registration = FALSE;
$userInput->skip_password = TRUE;
$userInput->user_name_change = $user_name_change;
$userInput->verifyNewEmail = TRUE;
$userInput->user_data = fusion_get_userdata();
$userInput->saveUpdate();

echo $userFields->editProfile();

require_once THEMES.'templates/footer.php';


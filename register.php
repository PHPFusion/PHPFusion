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
require_once "maincore.php";
require_once THEMES."templates/header.php";
include LOCALE.LOCALESET."user_fields.php";
require_once THEMES."templates/global/register.php";
if (iMEMBER || !fusion_get_settings("enable_registration")) {
	redirect("index.php");
}
$_GET['profiles'] = 1;
$errors = array();

if (isset($_GET['email']) && isset($_GET['code'])) {
	if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $_GET['email'])) 	redirect("register.php?error=activate");
	if (!preg_check("/^[0-9a-z]{40}$/", $_GET['code'])) redirect("register.php?error=activate");
	$result = dbquery("SELECT user_info FROM ".DB_NEW_USERS." WHERE user_code='".$_GET['code']."' AND user_email='".$_GET['email']."' LIMIT 1");
	if (dbrows($result)>0) {
		add_to_title($locale['global_200'].$locale['u155']);
		function unserializeFix($var) {
			$var = preg_replace('!s:(\d+):"(.*?)";!e', "'s:'.strlen('$2').':\"$2\";'", $var);
			return unserialize($var);
		}
		$data = dbarray($result);
		$user_info = unserializeFix(stripslashes($data['user_info']));
		dbquery_insert(DB_USERS, $user_info, 'save');
		$result = dbquery("DELETE FROM ".DB_NEW_USERS." WHERE user_code='".$_GET['code']."' LIMIT 1");
		if (fusion_get_settings('admin_activation') == "1") {
			addNotice("info", $locale['u171']." - ".$locale['u162']);
		} else {
			addNotice("info", $locale['u171']." - ".$locale['u161']);
		}
	} else {
		redirect("index.php");
	}
}
elseif (isset($_POST['register'])) {
	$userInput = new PHPFusion\UserFieldsInput();
	$userInput->validation = fusion_get_settings('display_validation'); //$settings['display_validation'];
	$userInput->emailVerification = fusion_get_settings('email_verification'); //$settings['email_verification'];
	$userInput->adminActivation = fusion_get_settings('admin_activation'); //$settings['admin_activation'];
	$userInput->skipCurrentPass = TRUE;
	$userInput->registration = TRUE;
	if ($userInput->saveInsert()) {
		redirect(BASEDIR."index.php");
	};
	unset($userInput);
}

if (!isset($_GET['email']) && !isset($_GET['code'])) {
	$userFields = new PHPFusion\UserFields();
	$userFields->postName = "register";
	$userFields->postValue = $locale['u101'];
	$userFields->displayValidation = fusion_get_settings('display_validation'); // $settings['display_validation'];
	$userFields->displayTerms = fusion_get_settings('enable_terms'); // $settings['enable_terms'];
	$userFields->plugin_folder = INCLUDES."user_fields/";
	$userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
	$userFields->showAdminPass = FALSE;
	$userFields->skipCurrentPass = TRUE;
	$userFields->registration = TRUE;
	ob_start();
	$userFields->render_profile_input();
	$info['register_form'] = ob_get_contents();
	ob_end_clean();
	display_registerform($info);
}
require_once THEMES."templates/footer.php";
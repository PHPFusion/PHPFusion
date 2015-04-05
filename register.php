<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: register.php
| Author: Hans Kristian Flaatten {Starefossen}
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
if (iMEMBER || !$settings['enable_registration']) {
	redirect("index.php");
}
$_GET['profiles'] = 1;
$errors = array();
if (isset($_GET['email']) && isset($_GET['code'])) {

	if (!preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $_GET['email'])) 	redirect("register.php?error=activate");
	if (!preg_check("/^[0-9a-z]{40}$/", $_GET['code'])) redirect("register.php?error=activate");

	$result = dbquery("SELECT user_info FROM ".DB_NEW_USERS."
				WHERE user_code='".$_GET['code']."' AND user_email='".$_GET['email']."'
				LIMIT 1");

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
		opentable($locale['u155']);
		if (fusion_get_settings('admin_activation') == "1") {
			echo "<div style='text-align:center'><br />\n".$locale['u171']."<br /><br />\n".$locale['u162']."<br /><br />\n</div>\n";
		} else {
			echo "<div style='text-align:center'><br />\n".$locale['u171']."<br /><br />\n".$locale['u161']."<br /><br />\n</div>\n";
		}
		closetable();
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
	$userInput->saveInsert();
	unset($userInput);
} else {
	// hide by default
	opentable($locale['u101']);
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
	$userFields->render_profile_input();
	closetable();
}
require_once THEMES."templates/footer.php";
?>
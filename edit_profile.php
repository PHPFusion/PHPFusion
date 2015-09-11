<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: edit_profile.php
| Author: Nick Jones (Digitanium)
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
if (!iMEMBER) {
	redirect("index.php");
}
$_GET['profiles'] = isset($_GET['profiles']) && isnum($_GET['profiles']) ? $_GET['profiles'] : 1;
add_to_title($locale['global_200'].$locale['u102']);
$errors = array();
$_GET['profiles'] = isset($_GET['profiles']) && isnum($_GET['profiles']) ? $_GET['profiles'] : 1;
if (isset($_POST['update_profile'])) {
	$userInput = new PHPFusion\UserFieldsInput();
	$userInput->setUserNameChange(fusion_get_settings('userNameChange')); // accept or not username change.
	$userInput->verifyNewEmail = TRUE;
	$userInput->userData = $userdata;
	$userInput->saveUpdate();
	if (defender::safe()) {
		redirect(FUSION_SELF);
	}
} elseif (isset($_GET['code']) && fusion_get_settings('email_verification') == 1) {
	$userInput = new PHPFusion\UserFieldsInput();
	$userInput->verifyCode($_GET['code']);
	redirect(BASEDIR.'edit_profile.php');
}
opentable($locale['u102']);
if (fusion_get_settings('email_verification') == 1) {
	$result = dbquery("SELECT user_email FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$userdata['user_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		echo "<div class='well text-center' style='margin: 5px auto 10px auto;'>".sprintf($locale['u200'], $data['user_email'])."\n<br />\n".$locale['u201']."\n</div>\n";
	}
}
$userFields = new PHPFusion\UserFields();
$userFields->postName = "update_profile";
$userFields->postValue = $locale['u105'];
$userFields->userData = $userdata;
$userFields->plugin_folder = INCLUDES."user_fields/";
$userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
$userFields->setUserNameChange(fusion_get_settings("userNameChange"));
$userFields->registration = FALSE;
$userFields->method = 'input';
$userFields->render_profile_input();
closetable();
require_once THEMES."templates/footer.php";

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
define('RIGHT_OFF', true);
if (!iMEMBER) {
	redirect("index.php");
}
add_to_title($locale['global_200'].$locale['u102']);
$errors = array();
if (isset($_POST['update_profile'])) {
	$userInput = new UserFieldsInput();
	$userInput->setUserNameChange($settings['userNameChange']); // accept or not username change.
	$userInput->verifyNewEmail = TRUE;
	$userInput->userData = $userdata; // inject to override so whatever not in this page is not lost.. deprecate
	$userInput->saveUpdate();
	$userInput->displayMessages();
	if (empty($errors) && $userInput->themeChanged()) redirect(BASEDIR.'index.php');
	$userdata = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$userdata['user_id']."'"));
	unset($userInput);
}

elseif (isset($_GET['code']) && $settings['email_verification'] == "1") {
	$userInput = new UserFieldsInput();
	$userInput->verifyCode($_GET['code']);
	$userInput->displayMessages();
	$userdata = dbarray(dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".$userdata['user_id']."'"));
	unset($userInput);
}

opentable($locale['u102']);

if ($settings['email_verification'] == "1") {
	$result = dbquery("SELECT user_email FROM ".DB_EMAIL_VERIFY." WHERE user_id='".$userdata['user_id']."'");
	if (dbrows($result)) {
		$data = dbarray($result);
		echo "<div class='tbl2' style='text-align:center; width:500px; margin: 5px auto 10px auto;'>".sprintf($locale['u200'], $data['user_email'])."\n<br />\n".$locale['u201']."\n</div>\n";
	}
}

$userFields = new UserFields();
$userFields->postName = "update_profile";
$userFields->postValue = $locale['u105'];
$userFields->userData = $userdata;
$userFields->plugin_folder = INCLUDES."user_fields/";
$userFields->plugin_locale_folder = LOCALE.LOCALESET."user_fields/";
$userFields->setUserNameChange($settings['userNameChange']);
$userFields->method = 'input';
$userFields->renderInput();
closetable();
require_once THEMES."templates/footer.php";
?>
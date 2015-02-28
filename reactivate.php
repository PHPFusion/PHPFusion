<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: reactivate.php
| Author: Paul Beuk (muscapaul)
| Co Author: Hans Krisitan Flaatten (Starefossen)
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
require_once INCLUDES."suspend_include.php";
include LOCALE.LOCALESET."reactivate.php";

if (iMEMBER) { redirect("index.php"); }

if (isset($_GET['user_id']) && isnum($_GET['user_id']) && isset($_GET['code']) && preg_check("/^[0-9a-z]{32}$/", $_GET['code'])) {
	$result = dbquery("SELECT user_name, user_email, user_actiontime, user_password FROM ".DB_USERS." WHERE user_id='".$_GET['user_id']."' AND user_actiontime>'0' AND user_status='7'");
	if (dbrows($result)) {
		$data = dbarray($result);
		$code = md5($data['user_actiontime'].$data['user_password']);
		if ($_GET['code'] == $code) {
			if ($data['user_actiontime'] > time()) {
				$result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0', user_lastvisit='".time()."' WHERE user_id='".$_GET['user_id']."'");
				unsuspend_log($_GET['user_id'], 7, $locale['506'], true);
				$message = str_replace("[USER_NAME]", $data['user_name'], $locale['505']);
				require_once INCLUDES."sendmail_include.php";
				sendemail($data['user_name'], $data['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['504'], $message);
				redirect(BASEDIR."login.php");
			} else {
				redirect(FUSION_SELF."?error=1");
			}
		} else {
			redirect(FUSION_SELF."?error=2&user_id=".$data['user_id']."&code=".$_GET['code']);
		}
	} else {
		redirect(FUSION_SELF."?error=3");
	}
} elseif (isset($_GET['error']) && isnum($_GET['error'])) {
	opentable($locale['500']);
	if ($_GET['error'] == 1) {
		echo $locale['501'];
	} elseif ($_GET['error'] == 2) {
		echo $locale['502'];
	} elseif ($_GET['error'] == 3) {
		echo $locale['503'];
	} else {
		redirect(BASEDIR."index.php");
	}
	closetable();
} else {
	redirect(BASEDIR."index.php");
}

require_once THEMES."templates/footer.php";
?>

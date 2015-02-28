<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_check.php
| Author: Hans Kristian Flaatten
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

require_once "recaptchalib.php";

$resp = null; $error = null;
if (isset($_POST["recaptcha_challenge_field"]) && isset($_POST["recaptcha_response_field"])) {
	$resp = recaptcha_check_answer($settings['recaptcha_private'], USER_IP, $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
	if ($resp->is_valid) { $_CAPTCHA_IS_VALID = true; }
}
?>
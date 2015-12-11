<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_check.php
| Author: skpacman
| Copyright 2015 Stephen D King Jr
| ------------------------------------------------------
| This integrates the NEW reCAPTCHA Google API v2 into
| PHP-Fusion using the built-in PHP-Fusion captcha system
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
require_once INCLUDES."captchas/grecaptcha/recaptchalib.php"; //a required library from Google
$resp = null; $error = null;
$googleArray = array(
	"ip" => $_SERVER["REMOTE_ADDR"],
	"captcha" => !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : FALSE,
	"secret" =>	fusion_get_settings("recaptcha_private")
);
$reCaptcha = new ReCaptcha($googleArray['secret']);
$resp = $reCaptcha->verifyResponse($googleArray['ip'], $googleArray['captcha']);
if ($resp != null && $resp->success && $error == null) {
	$_CAPTCHA_IS_VALID = true;
}
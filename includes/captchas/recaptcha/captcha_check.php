<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_check.php
| Author: Krelli (systemweb.de)
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}
require_once INCLUDES."captchas/recaptcha/recaptchalib.php";
$resp = NULL;
$error = NULL;
$googleArray = array(
    "ip" => $settings['siteurl'], //$_SERVER["REMOTE_ADDR"],
    "captcha" => !empty($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : FALSE,
    "secret" => $settings['recaptcha_private']
);
$reCaptcha = ReCaptcha::getInstance($googleArray['secret']);
$resp = $reCaptcha->verifyResponse($googleArray['ip'], $googleArray['captcha']);
if ($resp != NULL && $resp->success && $error == NULL) {
    $_CAPTCHA_IS_VALID = TRUE;
}

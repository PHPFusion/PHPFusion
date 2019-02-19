<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_check.php
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
defined('IN_FUSION') || exit;

require_once INCLUDES.'captchas/grecaptcha/recaptchalib.php';

$resp = NULL;

$reCaptcha = new ReCaptcha(fusion_get_settings('recaptcha_private'));

if (isset($_POST['g-recaptcha-response'])) {
    $resp = $reCaptcha->verifyResponse(
        $_SERVER['REMOTE_ADDR'],
        $_POST['g-recaptcha-response']
    );
}

if ($resp != NULL && $resp->success) {
    $_CAPTCHA_IS_VALID = TRUE;
}

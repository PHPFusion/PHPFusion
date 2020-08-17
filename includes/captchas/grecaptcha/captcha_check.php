<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: captcha_check.php
| Author: Core Development Team (coredevs@phpfusion.com)
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

if ($captcha = post('g-recaptcha-response')) {
    $resp = $reCaptcha->verifyResponse(
        server('REMOTE_ADDR'),
        $captcha
    );
}

if ($resp != NULL && $resp->success) {
    $_CAPTCHA_IS_VALID = TRUE;
}

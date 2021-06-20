<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: captcha_check.php
| Author: RobiNN
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

if (check_post('g-recaptcha-response')) {
    $settings = fusion_get_settings();

    require_once __DIR__.'/lib/autoload.php';
    $recaptcha = new \ReCaptcha\ReCaptcha($settings['recaptcha_private']);
    $recaptcha->setScoreThreshold($settings['recaptcha_score']);
    $resp = $recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
        ->verify(post('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);

    if ($resp->isSuccess()) {
        $_CAPTCHA_IS_VALID = TRUE;
    }
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
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

if (post('g-recaptcha-response')) {
    $context = stream_context_create([
        'http' => [
            'header'   => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'   => 'POST',
            'content'  => http_build_query([
                'secret'   => fusion_get_settings('recaptcha_private'),
                'response' => post('g-recaptcha-response')
            ]),
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ]
    ]);

    if ($resp['success'] === TRUE && $resp['score'] >= fusion_get_settings('recaptcha_score')) {
        $_CAPTCHA_IS_VALID = TRUE;
    }
}

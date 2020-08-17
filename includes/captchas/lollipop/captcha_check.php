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

$_CAPTCHA_IS_VALID = NULL;

$form_id = post('form_id');
if ($form_id) {
    require_once INCLUDES.'captchas/lollipop/lollipop.php';

    $captcha = new Lollipop(post('form_id'));
    $_CAPTCHA_IS_VALID = $captcha->validateCaptcha();
}

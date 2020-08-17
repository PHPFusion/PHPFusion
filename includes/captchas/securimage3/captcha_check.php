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

require_once 'securimage.php';

$securimage = new Securimage();

if (isset($_POST['captcha_code'])) {
    $captcha_code = stripinput($_POST['captcha_code']);

    if ($securimage->check(form_sanitizer($captcha_code)) == TRUE) {
        $_CAPTCHA_IS_VALID = TRUE;
    }
}

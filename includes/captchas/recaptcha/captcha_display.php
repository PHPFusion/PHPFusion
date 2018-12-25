<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
| Author: Krelli (systemweb.de)
| ------------------------------------------------------
| This integrates the NEW reCAPTCHA Google API v2 into
| PHP-Fusion 7 using the built-in PHP-Fusion captcha system
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
$_CAPTCHA_HIDE_INPUT = TRUE;
add_to_head("<script type='text/javascript' src='https://www.google.com/recaptcha/api.js?hl=".$locale['xml_lang']."' async defer></script>");
echo "<div class='g-recaptcha' data-type='".(IsSet($settings['recaptcha_type']) ? $settings['recaptcha_type'] : 'text')."' data-theme='".($settings['recaptcha_theme']=='dark' ? 'dark' : 'light')."' data-sitekey='".$settings['recaptcha_public']."'></div>\n";

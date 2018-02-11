<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

$_CAPTCHA_HIDE_INPUT = TRUE;

add_to_head("<script type='text/javascript' src='https://www.google.com/recaptcha/api.js?hl=".fusion_get_locale('xml_lang')."'></script>");

echo "<div class='g-recaptcha' data-type='".fusion_get_settings("recaptcha_type")."' data-theme='".fusion_get_settings("recaptcha_theme")."' data-sitekey='".fusion_get_settings("recaptcha_public")."'></div>\n";

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: captcha_display.php
| Author: Hans Kristian Flaatten
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

require_once "recaptchalib.php";

$lang = array("en", "nl", "fr", "de", "pt", "ru", "es", "tr");
$recaptchaLocale = "";
if (!isset($locale['recaptcha']) || !in_array($locale['recaptcha'], $lang)) {
	if (isset($locale['recaptcha'])&& isset($locale['recaptcha_l10n'])) {
		$recaptchaLocale = "\n\t"."custom_translations : {".$locale['recaptcha_l10n']."}, ";
	} elseif (!isset($locale['recaptcha'])) {
		$locale['recaptcha'] = "en";
	}
}

add_to_head("<script type=\"text/javascript\">
/*<![CDATA[*/
var RecaptchaOptions = { ".$recaptchaLocale."
   lang : '".$locale['recaptcha']."',
   theme : '".$settings['recaptcha_theme']."'
};
/*]]>*/
</script>");

// Hid extra input
$_CAPTCHA_HIDE_INPUT = true;

echo recaptcha_get_html($settings['recaptcha_public']);
?>
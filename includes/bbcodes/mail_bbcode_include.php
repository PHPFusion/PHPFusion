<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: mail_bbcode_include.php
| Author: Wooya
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

if (phpversion()>5) {
	$text = preg_replace_callback(
		"#\[mail\]([\r\n]*)([^\s\'\";:\+]*?)([\r\n]*)\[/mail\]#si",
		function($m) {
			require LOCALE.LOCALESET."bbcodes/mail.php";
			$mail = $m['2'];
			return hide_email($mail);
		}, $text);
} else {
	$text = preg_replace('#\[mail\]([\r\n]*)([^\s\'\";:\+]*?)([\r\n]*)\[/mail\]#sie', "hide_email('\\2').''", $text);
}

if (phpversion()>5) {
	$text = preg_replace_callback(
		"#\[mail=([\r\n]*)([^\s\'\";:\+]*?)\](.*?)([\r\n]*)\[/mail\]#si",
		function($m) {
			require LOCALE.LOCALESET."bbcodes/mail.php";
			$mail = $m['2'];
			return hide_email($mail);
		}, $text);
} else {
	$text = preg_replace('#\[mail=([\r\n]*)([^\s\'\";:\+]*?)\](.*?)([\r\n]*)\[/mail\]#sie', "hide_email('\\2').''", $text);
}


?>
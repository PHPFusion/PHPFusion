<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: preview.ajax.php
| Author: Frederick MC CHan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__)."../../../../../maincore.php";
$text = stripinput($_POST['text']);
// filter to relative path conversion
echo "<div class='preview-response p-t-20 m-b-20'>\n";
if ($_POST['editor'] == 'html_input') {
	$text = stripslash(nl2br(parsesmileys($text)));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo $text ? : $locale['nopreview'];
} elseif ($_POST['editor'] == 'bbcode') {
	$text = parseubb(parsesmileys($text));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo $text ? : $locale['nopreview'];
} else {
	$text = parsesmileys($text);
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo nl2br($text) ? : $locale['nopreview'];
}
echo "<hr>\n";
echo "</div>\n";


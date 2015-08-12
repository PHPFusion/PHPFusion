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
echo "<div class='preview-response well m-10'>\n";
if ($_POST['editor'] == 'html') {
	$text = parsesmileys(nl2br(html_entity_decode(stripslashes($text))));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo $text ? : "<p class='text-center'>".$locale['nopreview']."</p>\n";
} elseif ($_POST['editor'] == 'bbcode') {
	$text = parseubb(parsesmileys($text));
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo $text ? : "<p class='text-center'>".$locale['nopreview']."</p>\n";
} else {
	$text = parsesmileys($text);
	if (isset($_POST['mode']) && $_POST['mode'] == 'admin') {
		$images = str_replace('../../../', '', IMAGES);
		$text = str_replace(IMAGES, $images, $text);
		$text = str_replace(IMAGES_N, $images, $text);
	}
	echo nl2br($text) ? : "<p class='text-center'>".$locale['nopreview']."</p>\n";
}
echo "</div>\n";
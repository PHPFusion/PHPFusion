<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_labelling.php
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
function form_label($title, $array = FALSE) {
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	if (!is_array($array)) {
		$class = "";
		$icon = "";
	} else {
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$icon = (array_key_exists('icon', $array)) ? "<i class='".$array['icon']."'></i>" : "";
	}
	return "<span class='label $class'>$icon $title</span>\n";
}

function form_badge($title, $array = FALSE) {
	if (!is_array($array)) {
		$class = "";
		$icon = "";
	} else {
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$icon = (array_key_exists('icon', $array)) ? "<i class='".$array['icon']."'></i>" : "";
	}
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	return "<span class='badge $class'>$icon $title</span>\n";
}

?>
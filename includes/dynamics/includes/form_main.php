<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_main.php
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
function openform($form_name, $form_id, $method, $action, $array = FALSE) {
	global $defender;
	if (!is_array($array)) {
		$class = '';
		$enctype = '';
		$downtime = 10;
		$notice = 1;
	} else {
		$class = array_key_exists('class', $array) && $array['class'] ? $array['class'] : '';
		$enctype = array_key_exists('enctype', $array) && $array['enctype'] == 1 ? 1 : 0;
		$downtime = array_key_exists('downtime', $array) && isnum($array['downtime']) ? $array['downtime'] : 10;
		$notice = array_key_exists('notice', $array) && isnum($array['notice']) ? $array['notice'] : 1;
	}
	$html = "<form name='".$form_name."' id='".$form_id."' method='".$method."' action='".$action."' class='".(defined('FUSION_NULL') ? 'warning' : '')." $class' ".($enctype ? "enctype='multipart/form-data'" : '')." >\n";
	$html .= generate_token($form_name, $downtime);
	if (defined('FUSION_NULL') && $notice) {
		echo $defender->showNotice();
	}
	return $html;
}

function form_rowstart() {
	return "<div class='inline field'>\n";
}

function form_rowend() {
	return "</div>\n";
}

function closeform() {
	$html = '';
	$html .= "</form>\n";
	return $html;
}

?>
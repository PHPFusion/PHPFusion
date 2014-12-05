<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_main.php
| Author: Frederick MC Chan (Hien)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function openform($form_name, $form_id, $method, $action, array $options = array()) {
	global $defender;
	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : '',
		'enctype' => !empty($options['enctype']) && $options['enctype'] == 1 ? 1 : 0,
		'notice' => !empty($options['notice']) && $options['notice'] == 0 ? 0 : 1,
		'downtime' => !empty($options['downtime']) && isnum($options['downtime']) ? $options['downtime'] : 1,
	);
	$html = "<form name='".$form_name."' id='".$form_id."' method='".$method."' action='".$action."' class='".(defined('FUSION_NULL') ? 'warning' : '')." ".$options['class']." ' ".($options['enctype'] ? "enctype='multipart/form-data'" : '')." >\n";
	$html .= generate_token($form_name, $options['downtime']);
	if (defined('FUSION_NULL') && $options['notice']) {
		echo $defender->showNotice();
	}
	return $html;
}

function closeform() {
	$html = '';
	$html .= "</form>\n";
	return $html;
}
?>
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
/**
 * @param       $form_name
 * @param       $method     - 'post' or 'get'
 * @param       $action_url - form current uri
 * @param array $options    :
 *                          form_id = default as form_name
 *                          class = default empty
 *                          enctype = true or false , set true to allow file upload
 *                          max_tokens = store into session number of tokens , default as 1.
 * @return string
 */
function openform($form_name, $method, $action_url, array $options = array()) {
    global $defender;
	$method = (strtolower($method) == 'post') ? 'post' : 'get';
	$options = array(
		'form_id'	=> !empty($options['form_id']) ? $options['form_id'] : $form_name,
		'class'		=> !empty($options['class']) ? $options['class'] : '',
		'enctype' => !empty($options['enctype']) && $options['enctype'] == TRUE ? TRUE : FALSE,
        'max_tokens' => !empty($options['max_tokens']) && isnum($options['max_tokens']) ? $options['max_tokens'] : 1,
    );

    $class = "";
    if (!$defender->safe()) {
        $class .= "class='warning ".$options['class']."' ";
    } elseif (!empty($options['class'])) {
        $class .= "class='".$options['class']."'";
    }

    $action_prefix = fusion_get_settings("site_seo") && !defined("ADMIN_PANEL") ? FUSION_ROOT : "";
    $html = "<form name='".$form_name."' id='".$options['form_id']."' method='".$method."' action='".$action_prefix.$action_url."' ".$class." ".($options['enctype'] ? "enctype='multipart/form-data'" : '')." >\n";
	if ($method == 'post') {
		$token = defender::generate_token($options['form_id'], $options['max_tokens']);
		$html .= "<input type='hidden' name='fusion_token' value='".$token."' />\n";
		$html .= "<input type='hidden' name='form_id' value='".$options['form_id']."' />\n";
	}
	return $html;
}

function closeform() {
	$html = "</form>\n";
	return $html;
}
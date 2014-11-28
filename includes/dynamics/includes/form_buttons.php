<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_buttons.php
| Author: Frederick MC Chan (Hien)
| Co-Author : Tyler Hurlbut
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_button($title, $input_name, $input_id, $input_value, array $options = array()) {
	$input_value = stripinput($input_value);
	$html = "";

	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : 'btn-default',
		'icon' => !empty($options['icon']) ? $options['icon'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'type' => !empty($options['type']) ? $options['type']  : 'submit',
		'block' => !empty($options['block']) && $options['block'] == 1 ? 'btn-block' : '',
		'alt' => !empty($options['alt']) && $options['alt'] && !empty($title) ? $options['alt'] : $title
	);

	if ($options['type'] == 'link') {
		$html .= "<a id='".$input_id."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled' : '')." btn ".$options['class']." button' href='".$input_name."' data-value='".$input_value."' ".($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']."'></i>" : '')." ".$title."</a>";
	} elseif ($options['type'] == 'button') {
		$html .= "<button id='".$input_id."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled' : '')." btn ".$options['class']." button' name='".$input_name."' value='".$input_value."' type='button' ".($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']."'></i>" : '')." ".$title."</button>";
	} else {
		$html .= "<button id='".$input_id."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled' : '')." btn ".$options['class']." button' name='".$input_name."' value='".$input_value."' type='submit' ".($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']."'></i>" : '')." ".$title."</button>";
	}
	return $html;
}


function form_btngroup($title, $input_name, $input_id, $opts, $input_value, array $options = array()) {

	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";

	$options += array(
		'class' => !empty($options['class']) ? $options['class'] : 'btn-default',
		'icon' => !empty($options['icon']) ? $options['icon']  : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'error_text' => !empty($options['error_text']) ? $options['error_text']  : '',
		'inline' => !empty($options['inline']) ? $options['inline']  : 0,
		'safemode' => !empty($options['safemode']) ? $options['safemode']  : 0,
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : 0,
	);

	$html = '';
	$html .= "<div id='$input_id-field' class='form-group clearfix'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
	$html .= "<div class='btn-group' id='".$input_id."'>";
	$x = 1;
	if (is_array($opts)) {
		foreach ($opts as $arr => $v) {
			$active = '';
			if (($input_value == $arr)) { $active = "active"; }
			$html .= "<button type='button' data-value='$arr' class='btn ".$options['class']." ".((count($opts) == $x ? 'last-child' : ''))." $active'>".$v."</button>\n";
			$x++;
		}
	}
	$html .= "</div>\n";
	$html .= "<input name='$input_name' type='hidden' id='".$input_id."-text' value='$input_value' />\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= $options['inline'] ? "</div>\n" : '';
	$html .= "</div>\n";

	$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=".$options['required']."],[safemode=".$options['safemode']."]' />";

	add_to_jquery("
	$('#".$input_id." button').bind('click', function(e){
		$('#".$input_id." button').removeClass('active');
		$(this).toggleClass('active');
		value = $(this).data('value');
		$('#".$input_id."-text').val(value);
	});
	");
	return $html;
}

?>
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
function form_button($title, $input_name, $input_id, $input_value, $options = FALSE) {
	$input_value = stripinput($input_value);
	$html = "";
	if (!is_array($options)) {
		$class = "btn-default";
		$icon = "";
		$type = '';
		$icon_stack = 0;
		$btn_block = '';
		$deactivate = '';
	} else {
		$class = isset($options['class']) && $options['class'] ? $options['class'] : 'btn-default';
		$icon = isset($options['icon']) && $options['icon'] ? $options['icon'] : "";
		$deactivate = isset($options['deactivate']) && $options['deactivate'] == 1 ? 1 : 0;
		$icon_stack = isset($options['icon_stack']) && $options['icon_stack'] == 1 ? 1 : 0;
		$type = isset($options['type']) && $options['type'] ? $options['type'] : '';
		$btn_block = isset($options['block']) && $options['block'] == 1 ? 'btn-block' : 0;
	}
	if ($type == 'link') {
		$html .= "<a id='".$input_id."' title='".$title."' class='".($deactivate ? 'disabled' : '')." btn $class button' href='".$input_name."' data-value='".$input_value."' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</a>";
	} elseif ($type == 'button') {
		$html .= "<button id='".$input_id."' title='".$title."' class='".($deactivate ? 'disabled' : '')." btn $class button' name='".$input_name."' value='".$input_value."' type='button' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
	} else {
		$html .= "<button id='".$input_id."' title='".$title."' class='".($deactivate ? 'disabled' : '')." btn $class button' name='".$input_name."' value='".$input_value."' type='submit' ".($deactivate ? "disabled='disabled'" : '')." >".($icon ? "<i class='$icon'></i>" : '')." ".$title."</button>";
	}
	return $html;
}

function form_btngroup($title, $input_name, $input_id, $opts, $input_value, $options = FALSE) {
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";
	if (!is_array($options)) {
		$class = '';
		$well = '';
		$error_text = '';
		$required = 0;
		$safemode = 0;
		$inline = '';
	} else {
		$class = isset($options['class']) && $options['class'] ? $options['class'] : '';
		$well = isset($options['well']) && $options['well'] ? "style='margin-top:-10px;'" : "";
		$error_text = isset($options['error_text']) && $options['error_text'] ? $options['error_text'] : "";
		$required = isset($options['required']) && $options['required'] == 1 ? 1 : 0;
		$safemode = isset($options['safemode']) && $options['safemode'] == 1  ? 1 : 0;
		$inline = isset($options['inline']) && $options['inline'] == 1 ? 1 : 0;
	}
	$html = '';
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 $class ".($icon ? 'has-feedback' : '')."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= "<div class='btn-group' id='".$input_id."'>";
	$x = 1;
	if (is_array($opts)) {
		foreach ($opts as $arr => $v) {
			$active = '';
			if (($input_value == $arr)) { $active = "active"; }
			$html .= "<div data-value='$arr' class='btn $class ".((count($options) == $x ? 'last-child' : ''))." $active'>".$v."</div>\n";
			$x++;
		}
	}
	$html .= "</div>\n";
	$html .= "<input readonly name='$input_name' type='hidden' id='".$input_id."-text' value='$input_value' />\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";

	$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly />";

	add_to_jquery("
        $('#".$input_id." span').bind('click', function(e){
            $('#".$input_id." span').removeClass('active');
            $(this).toggleClass('active');
            value = $(this).data('value');
            $('#".$input_id."-text').val(value);
        });
        ");
	return $html;
}

?>
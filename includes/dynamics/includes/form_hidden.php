<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_hidden.php
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
function form_hidden($title, $input_name, $input_id, $input_value, $options = FALSE) {
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	if (!$options) {
		$show_title = 0;
		$width = '';
		$inline = 0;
		$required = 0;
		$class = '';
	} else {
		// note: select2 can be appended to a hidden field to display json/ajax output.
		$show_title = isset($options['title']) && $options['title'] ?  1 : 0;
		$width = isset($options['width']) && $options['width'] && $show_title ? "style='width: ".$options['width']."'" : "style='width:250px'";
		$inline = isset($options['inline']) && $options['inline'] && $show_title ? 1 : 0;
		$class = isset($options['class']) && $options['class'] ? $options['class'] : '';
		$required = isset($options['required']) && $options['required'] == 1 ? '1' : '0';
	}
	$html = '';
	if ($show_title) {
		$html .= "<div id='$input_id-field' class='form-group m-b-0 $class'>\n";
		$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
		$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	}
	$html .= "<input type='hidden' name='$input_name' id='$input_id' value='$input_value' ".$width." ".($show_title ? "" : "readonly")." />\n";
	if ($show_title) {
		$html .= "<div id='$input_id-help'></div>";
		$html .= ($inline) ? "</div>\n" : "";
		$html .= "</div>\n";
	}
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=0]' readonly />";
	return $html;
}

?>
<?php

/* 1 or 0 only */
function form_toggle($title, $input_name, $input_id, $opts, $input_value, $array = FALSE) {
	$html = '';
	$title2 = ucfirst(strtolower(str_replace("_", " ", $input_name)));
	if (!is_array($array)) {
		$class = 'small';
		$justified = "";
		$well = "";
		$wellclass = "";
		$helper_text = "";
		$slider = 0;
		$required = 0;
		$safemode = 0;
		$type_config = 'toggle';
	} else {
		$class = (array_key_exists("class", $array)) ? $array['class'] : "small";
		$justified = (array_key_exists("justified", $array)) ? "btn-group-justified" : "";
		$well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		if (array_key_exists("checkbox", $array) && ($array['checkbox'] == 1)) {
			$type_config = "checkbox";
		} elseif (array_key_exists("slider", $array) && ($array['slider'] == 1)) {
			$type_config = "slider";
		} else {
			$type_config = "toggle";
		}
	}
	$html .= "<div id='".$input_id."-field' class='field'/>\n";
	if ($type_config !== 'checkbox') {
		$text = ($input_value) ? $opts[1] : $opts[0];
		$html .= "<label><h3>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</h3></label>\n";
		$html .= "<div class='ui $type_config checkbox'>\n";
		$html .= "<input id='$input_id' name='$input_name' value='1' type='checkbox' ".($input_value == 1 ? 'checked' : '')."/>\n";
		$html .= "<label style='font-weight:bold;' id='$input_id-label' for='$input_id'/>$text</label>\n";
		$html .= "</div>\n";
		$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
		add_to_jquery("
            $('#".$input_id."-label').bind('click', function(e){
            var text = $(this).text();
            if (text == '".$opts[0]."') {
                $(this).text('".$opts[1]."');
            } else {
                $(this).text('".$opts[0]."');
            }
            });
            ");
	} else {
		$html .= "<div class='ui $type_config'/>\n";
		$html .= "<input id='$input_id' name='$input_name' value='1' type='checkbox' ".($input_value == 1 ? 'checked' : '')."/>\n";
		$html .= "<label style='font-weight:bold;' id='$input_id-label' for='$input_id'/>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n";
		$html .= "</div>\n";
		$html .= "<input type='hidden' name='def[$input_name]' value='[type=text],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
	}
	$html .= "</div>\n";
	//        $html .= "</div></div>\n";
	return $html;
}

?>
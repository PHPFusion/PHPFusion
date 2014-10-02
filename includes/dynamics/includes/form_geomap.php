<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Address Input Based
| Filename: form_geomap.php
| Author: Frederick MC Chan (Hien)
| Sub-Author: Joakim Falke
| Communities of PHP-Fusion at PHP-Fusion.co.uk
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_address($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	if (isset($input_name) && ($input_name !== "")) {
		$input_name = stripinput($input_name);
	} else {
		$input_name = "";
	}
	if (isset($input_id) && ($input_id !== "")) {
		$input_id = stripinput($input_id);
	} else {
		$input_id = "";
	}
	$input_id = str_replace(" ", "", $input_id);
	$input_id = str_replace("/", "", $input_id);
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	require INCLUDES."geomap/geomap.inc.php";
	// NOTE (remember to parse readback value as such):
	// $input_value = "Lot 87, Taman Khidmat,|Lorong Pokok Seraya 3A,|North-Korea|Sabah|Kota Kinabalu|89350";
	if (isset($input_value) && (!empty($input_value))) {
		if (!is_array($input_value)) {
			$input_value = construct_array($input_value, "", "|");
		}
	} else {
		$input_value['0'] = "";
		$input_value['1'] = "";
		$input_value['2'] = "";
		$input_value['3'] = "";
		$input_value['4'] = "";
		$input_value['5'] = "";
	}
	if (!is_array($array)) {
		$array = array();
		$before = "";
		$after = "";
		$required = "";
		$deactivate = "";
		$width = "";
		$class = "";
		$well = "";
		$required = 0;
		$safemode = 0;
		$stacking = 0;
		$helper_text = "";
	} else {
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$class = (array_key_exists('class', $array)) ? $array['class'] : "";
		$width = (array_key_exists('width', $array)) ? $array['width'] : "";
		$well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$stacking = (array_key_exists("stacking", $array)) ? 1 : "";
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
	}
	$html = "";
	$html .= "<div class='field'/>\n";
	$html .= ($title) ? "<label for='$input_id'/><h3>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</h3></label>\n" : '';
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control input-sm' id='".$input_id."-street' value='".$input_value['0']."' placeholder='Street Address 1' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "")." />\n";
	$html .= ($required) ? "<div class='ui corner label'/><i class='icon asterisk'/></i></div>\n" : '';
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div class='field'>\n";
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' name='".$input_name."[]' class='form-control input-sm' id='".$input_id."-street2' value='".$input_value['1']."' placeholder='Street Address 2' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-field' class='three fields'/>\n";
	$html .= "<div class='field'>\n";
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<select name='".$input_name."[]' id='$input_id-country' style='width:100%;'/>\n";
	$html .= "<option value=''></option>";
	foreach ($countries as $arv => $countryname) { // outputs: key, value, class - in order
		$country_key = str_replace(" ", "-", $countryname);
		if ($input_value['2'] == $country_key) {
			$select = "selected";
		} else {
			$select = "";
		}
		$html .= "<option value='$country_key' $select>$countryname</option>";
	}
	$html .= "</select>\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div class='field'>\n";
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<div id='state-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
	$html .= "<input type='hidden' name='".$input_name."[]' id='$input_id-state' value='".$input_value['3']."' style='width:100%;' />\n";
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div class='field'>\n";
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-city' value='".$input_value['4']."' placeholder='City' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<div id='$input_id-field' class='three fields'/>\n";
	$html .= "<div class='field'>\n";
	$html .= "<div class='ui left labeled input'/>\n";
	$html .= "<input type='text' name='".$input_name."[]'  id='".$input_id."-postcode' value='".$input_value['5']."' placeholder='Postcode' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">";
	$html .= "<div id='$input_id-help'></div>";
	$html .= "</div>\n";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=$title Street Address 1],[id=".$input_id."-street],[required=$required],[safemode=$safemode]' readonly />\n";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=$title Street Address 2],[id=".$input_id."-street2],[required=$required],[safemode=$safemode]' readonly>";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=dropdown],[title=$title Country],[id=".$input_id."-country],[required=$required],[safemode=$safemode]' readonly />\n";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=dropdown],[title=$title State],[id=".$input_id."-state],[required=$required],[safemode=$safemode]' readonly />\n";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=$title City],[id=".$input_id."-city],[required=$required],[safemode=$safemode]' readonly>";
	$html .= "<input type='hidden' name='def[$input_name][]' value='[type=textbox],[title=$title Postcode],[id=".$input_id."-postcode],[required=$required],[safemode=$safemode]' readonly>";
	$html .= "</div>\n";
	add_to_jquery("
         $('#$input_id-country').select2({placeholder: 'Country'});
         $('#".$input_id."-country').bind('change', function(){

            var ce_id = $(this).val();
            $.ajax({
                url: '".INCLUDES."geomap/form_geomap.json.php',
                type: 'GET',
                data: { id : ce_id },
                dataType: 'json',
                beforeSend: function(e) {
//                $('#state-spinner').show();
                },
                success: function(data) {
//                    $('#state-spinner').hide();
                    $('#".$input_id."-state').select2({
                    placeholder: 'Select State',
                    allowClear: true,
                    data : data
                    });
                },
                error : function() {
                        $.pnotify({title: 'Error! Something went wrong.',
                        text: 'We cannot read the database, please recheck source codes.',
                        icon: 'pngicon-l-badge-multiply',
                        width: 'auto'
                        });
                }
            })
            }).trigger('change');
         ");
	/*
	add_to_jquery("
					//function icon(item) {
					  //  if(!item.id) {return item.text;}
					  //  var icon = 'pngicon-flag-'+ item.id.toLowerCase();
					  //  return '<i style=\"float:left; margin-top:5px;\" class=\"' + icon + '\"/></i>' + item.text;
					//}
					$('#".$input_id."-country').select2({
					placeholder: 'Select Country',
					allowClear:true,
					formatResult: icon,
					formatSelection: icon,
					escapeMarkup: function(m) { return m; }
					});

					$('#".$input_id."-state').select2({
					placeholder: 'Select State',
					allowClear:true,
					data: [{id:'0', 'text' :'Select Country to View State'}]
					});
	");



	/*
	$html .= add_to_jquery("$('#".$input_id."-country').chained('#".$input_id."-country');");
	*/
	return $html;
}

?>
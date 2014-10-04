<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_select.php
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
function form_select($title, $input_name, $input_id, $option_array, $input_value = FALSE, $array = FALSE) {
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	$input_value = ($input_value) ? $input_value : '0';
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	if (!is_array($array)) {
		$placeholder = '';
		$deactivate = "";
		$multiple = 0;
		$multiple_attr = "";
		$width = '';
		$keyflip = 0;
		$chainable = "";
		$allowclear = '';
		$jsonmode = 0;
		$required = 0;
		$safemode = 0;
		$tags = 0;
		$maximum_selection = 30;
		$class = '';
		$inline = '';
		$error_text = '';
	} else {
		$chainable = (array_key_exists('chainable', $array)) ? $array['chainable'] : "";
		$multiple = (array_key_exists('multiple', $array) && ($array['multiple'] == 1)) ? 1 : 0;
		$multiple_attr = ($multiple == 1) ? "multiple" : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : '';
		$deactivate = (array_key_exists("deactivate", $array) && ($array['deactivate'] == "1")) ? 1 : 0;
		$width = (array_key_exists("width", $array) && ($array['width'])) ? $array['width'] : '';
		$keyflip = (array_key_exists('keyflip', $array)) ? $array['keyflip'] : 0;
		$allowclear = (array_key_exists('allowclear', $array) && $array['allowclear']) ? 'allowClear:true' : "";
		$jsonmode = (array_key_exists('jsonmode', $array) && ($array['jsonmode'] == "1")) ? 1 : 0;
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$tags = (array_key_exists("tags", $array) && ($array['tags'] == 1)) ? 1 : 0;
		if ($multiple) {
			$maximum_selection = (array_key_exists('maxselect', $array) && isnum($array['maxselect'])) ? $array['maxselect'] : 30;
		}
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$class = (array_key_exists("class", $array)) ? $array['class'] : '';
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
	}
	if ($multiple == 1) {
		if ($input_value) {
			$input_value = construct_array($input_value);
		} else {
			$input_value = array();
		}
	}
	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$class."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	if ($jsonmode == 1) {
		// json mode.
		$html .= "<div id='$input_id-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
		$html .= "<input ".($required ? "class='req'" : '')." type='hidden' name='$input_name' id='$input_id' ".(($width) ? "style='width: $width'" : "style='min-width: 250px'").">\n";
	} else {
		// normal mode
		$html .= "<select name='$input_name' id='$input_id' ".(($width) ? "style='width: $width'" : "style='min-width: 250px'")." ".($deactivate == "1" && (isnum($deactivate)) ? "disabled" : "")." $multiple_attr >"; //
		$html .= ($allowclear) ? "<option value=''></option>" : "";
		if (is_array($option_array)) {
			foreach ($option_array as $arr => $v) { // outputs: key, value, class - in order
				if (isnum($keyflip) && ($keyflip == "1")) { // flip mode = store array values
					$chain = ($chainable == "1") ? "class='$v'" : "";
					$select = '';
					if ($input_value !== NULL) {
						$select = ($input_value == $v) ? "selected" : "";
					}
					$html .= "<option value='$v' $chain $select>$v</option>";
				} else { // normal mode = store array keys
					$chain = ($chainable == "1") ? "class='$arr'" : "";
					$select = '';
					if ($input_value || $input_value == '0') {
						$input_value = stripinput($input_value); // make selected based on $input_value.
						$select = (isset($input_value) && $input_value == $arr) ? "selected" : "";
					}
					$html .= "<option value='$arr' ".$chain." ".$select.">$v</option>";
				}
				unset($arr);
			} // end foreach
		}
		$html .= "</select>";
	}
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	if ($required) {
		$html .= "<input class='req' id='dummy-$input_id' type='hidden'>\n"; // for jscheck
	}
	// Generate Defender Tag
	$input_name = ($multiple) ? str_replace("[]", "", $input_name) : $input_name;
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=dropdown],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	// Initialize Select2
	// Select 2 Multiple requires hidden DOM.
	if ($jsonmode == 0) {
		// not json mode (normal)
		$max_js = '';
		if ($multiple) {
			$max_js = "maximumSelectionSize : $maximum_selection,";
		}
		if ($required) {
			add_to_jquery("
                    var init_value = $('#".$input_id."').select2('val');
                    if (init_value) {
                    $('dummy-".$input_id."').val(init_value);
                    } else {
                    $('dummy-".$input_id."').val('');
                    }
                    $('#".$input_id."').select2({
                        placeholder: '".$placeholder."',
                        ".$max_js."
                        ".$allowclear."
                    }).bind('change', function(e) {
                    $('#dummy-".$input_id."').val($(this).val());
                    });
                    ");
		} else {
			add_to_jquery("
                    $('#".$input_id."').select2({
                        placeholder: '".$placeholder."',
                        ".$max_js."
                        ".$allowclear."
                    });
                    ");
		}
	} else {
		// json mode
		add_to_jquery("
                var this_data = [{id:0, text: '$placeholder'}];
                $('#".$input_id."').select2({
                placeholder: '".$placeholder."',
                data: this_data
                });
            ");
	}
	// For Multiple Callback.
	if (is_array($input_value) && $multiple == 1) { // stores as value;
		$vals = '';
		foreach ($input_value as $arr => $val) {
			$vals .= ($arr == count($input_value)-1) ? "'$val'" : "'$val',";
		}
		add_to_jquery("
            $('#".$input_id."').select2('val', [$vals]);
            ");
		// For Tags */
		/* foreach ($input_value as $id => $text) {
			$select_array[] = $keyflip ? array('id' => "$text", 'text' => "$text") : array('id' => "$id", 'text' => "$text");
		}
		if (!isset($select_array)) {
			$select_array = array();
		}
		$encoded = json_encode($select_array);
		add_to_jquery("$('#".$input_id."').select2('data', $encoded);"); */
	}
	// alert('Selected value is '+$('#".$input_id."').select2('val'));
	return $html;
}

function form_user_select($title, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	global $userdata;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	$title = (isset($title) && (!empty($title))) ? $title : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	$html = "";
	if (!is_array($array)) {
		$placeholder = "Choose a User...";
		$stacking = 0;
		$multiple = 1;
		$allowclear = "allowClear:true,";
		$length = "minimumInputLength: 1,";
		$helper_text = "";
		$required = 0;
		$safemode = 0;
		$deactivate = 0;
		$maximum_selection = 1;
		$file = '';
		$inline = '';
	} else {
		$placeholder = (array_key_exists("placeholder", $array) && (!empty($array['placeholder']))) ? $array['placeholder'] : "Choose a User...";
		$stacking = (array_key_exists("stacking", $array) && ($array['stacking'] == 1)) ? 1 : 0;
		$multiple = (array_key_exists("multiple", $array) && ($array['multiple'] == 1)) ? 1 : 0;
		$allowclear = ($multiple !== 1) ? "allowClear:true," : "";
		$length = "minimumInputLength: 1,";
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
		$required = (array_key_exists('required', $array)) ? $array['required'] : "";
		$safemode = (array_key_exists('safemode', $array)) ? $array['safemode'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$maximum_selection = (array_key_exists('maxselect', $array) && isnum($array['maxselect'])) ? $array['maxselect'] : 1;
		$file = (array_key_exists('file', $array) && ($array['file'])) ? $array['file'] : '';
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
	}
	$html = "";
	$html .= "<div id='$input_id-field' class='form-group clearfix m-b-10'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-2 col-lg-2" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-10 col-lg-10'>\n" : "";
	$html .= "<input ".($required ? "class='req'" : '')." type='hidden' name='$input_name' id='$input_id' data-placeholder='$placeholder' style='width:100%;' ".($deactivate == 1 ? "disabled" : "").">";
	if ($deactivate == 1) {
		$html .= form_hidden("", $input_name, $input_id, $input_value);
	}
	$html .= "<div id='$input_id-help' style='display:inline-block !important;'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	$path = ($file) ? $array['file'] : INCLUDES."search/users.json.php";
	if (!empty($input_value)) {
		// json mode.
		$encoded = ($file) ? $file : user_search($input_value);
	} else {
		$encoded = array();
	}
	add_to_footer("
                <script type='text/javascript'>
                function avatar(item) {
                    if(!item.id) {return item.text;}
                    var avatar = item.avatar;
                    var level = item.level;
                    if (item.realname) { var dev_name = '('+item.realname +','; } else { var dev_name = ''; }
                    if (item.co) { var co_name = item.co + ')'; } else { var co_name = ''; }
                    if (item.realname) { var status = ' (PHP-Fusion Accredited Developer)'; } else { var status = ''; }
                    return '<table><tr><td style=\"\"><img style=\"height:30px;\" class=\"img-rounded\" src=\"".IMAGES."avatars/' + avatar + '\"/></td><td style=\"padding-left:10px\"><div><strong>' + item.text + ' ' + dev_name + ' ' + co_name + '</strong></div>' + level + ' '+status+'</div></td></tr></table>';
                }

                $('#".$input_id."').select2({
                $length
                multiple: true,
                maximumSelectionSize: $maximum_selection,
                placeholder: '$placeholder',
                ajax: {
                url: '$path',
                dataType: 'json',
                data: function (term, page) {
                            return {q: term};
                      },
                      results: function (data, page) {
                        return {results: data};
                      }
                },
                formatSelection: avatar,
                escapeMarkup: function(m) { return m; },
                formatResult: avatar,
                $allowclear
                })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
            </script>
            ");
	return $html;
}

function user_search($user_id)
{
	// returns json encoded object.
	$user_id = stripinput($user_id);

	$result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM " . DB_USERS . " WHERE user_status='0' AND user_id='$user_id'");

	if (dbrows($result) > 0) {

		while ($udata = dbarray($result)) {

			$user_id = $udata['user_id'];

			$user_text = $udata['user_name'];

			$user_avatar = ($udata['user_avatar']) ? $udata['user_avatar'] : "noavatar50.png";

			$user_name = $udata['user_name'];

			$user_level = getuserlevel($udata['user_level']);

			$user_opts[] = array('id' => "$user_id", 'text' => "$user_name", 'avatar' => "$user_avatar", "level" => "$user_level");

		}

		if (!isset($user_opts)) {
			$user_opts = array();
		}

		$encoded = json_encode($user_opts);

	} else {

		$encoded = "";

	}

	return $encoded;

}


// Returns a full hierarchy nested dropdown.
function form_select_tree($title, $input_name, $input_id, $input_value = FALSE, $array = FALSE, $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = FALSE, $data = FALSE) {
	global $_POST, $locale;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js' /></script>\n");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n");
	}
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_value = isset($input_value) ? stripinput($input_value) : '';
	if (isset($name) && ($name != "")) {
		$name = stripinput($name);
	} else {
		$name = "";
	}
	if (isset($id_col) && ($id_col != "")) {
		$id_col = stripinput($id_col);
	} else {
		$id_col = "";
	}
	if (isset($cat_col) && ($cat_col != "")) {
		$cat_col = stripinput($cat_col);
	} else {
		$cat_col = "";
	}
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$required = 0;
		$safemode = 0;
		$allowclear = "";
		$placeholder = $locale['choose'];
		$deactivate = "";
		$labeloff = "";
		$multiple = "";
		$stacking = 0;
		$width = "style='width:90%;'";
		$add_parent_opts = 0;
		$no_root = 0;
		$inline = '';
		$include_opts = ''; // for selective input. will not show items if value not in array.
		$class = '';
	} else {
		$multiple = (array_key_exists('is_multiple', $array)) ? $array['is_multiple'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : $locale['choose'];
		$allowclear = (!empty($placeholder) && ($multiple !== 1)) ? "allowClear:true" : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$labeloff = (array_key_exists('labeloff', $array)) ? $array['labeloff'] : "";
		$helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$add_parent_opts = (array_key_exists('add_parent_opts', $array) && ($array['add_parent_opts'] == 1)) ? 1 : 0;
		$no_root = (array_key_exists('no_root', $array)) && ($array['no_root'] == 1) ? 1 : 0;
		$width = (array_key_exists('width', $array)) ? $array['width'] : '';
		$multiple = ($multiple == 1) ? "multiple" : "";
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$include_opts = (array_key_exists("include_opts", $array)) ? $array['include_opts'] : '';
		$class = (array_key_exists("class", $array)) ? $array['class'] : '';
	}
	// Patterns
	if (!$level) {
		$level = 0;
		$html = "<div id='$input_id-field' class='form-group clearfix m-b-10 ".$class."'>\n";
		$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
		$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	}
	$opt_pattern = str_repeat("&#8212;", $level);
	// no need to count here, it's cosmetics.
	if ($level == "0") {
		add_to_jquery("
            $('#".$input_id."').select2({
            placeholder: '".$placeholder."',
            $allowclear
            });
            ");
		$html .= "<select name='$input_name' id='$input_id' ".(($width) ? "style='width: $width'" : "style='min-width: 250px'")." ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "")." $multiple>";
		if ($allowclear) {
			$html .= "<option value=''></option>";
		}
		if ($no_root !== 1) { // api options to remove root from selector. used in items creation.
			$this_select = '';
			if ($input_value !== NULL) {
				if ($input_value == '0') {
					$this_select = "selected";
				}
			}
			$html .= ($add_parent_opts == '1') ? "<option value='0' ".$this_select.">$opt_pattern ".$locale['parent']."</option>\n" : "<option value='0' $this_select>$opt_pattern Root</option>\n";
		}
		$index = dbquery_tree($db, $id_col, $cat_col);
		$data = dbquery_tree_data($db, $id_col, $cat_col);
	}
	if (!$id) {
		$id = 0;
	}
	if (isset($index[$id])) {
		foreach ($index[$id] as $key => $value) {
			$html = & $html;
			$name = $data[$value][$name_col];
			$select = ($input_value !== "" && ($input_value == $value)) ? 'selected' : '';
			if (isset($include_opts) && is_array($include_opts)) {
				$html .= (in_array($value, $include_opts)) ? "<option value='$value' ".$select." ".($self_id == $value ? 'disabled' : '').">$opt_pattern $name ".($self_id == $value ? '(Current Item)' : '')."</option>\n" : '';
			} else {
				$html .= "<option value='$value' ".$select." ".($self_id == $value ? 'disabled' : '').">$opt_pattern $name ".($self_id == $value ? '(Current Item)' : '')."</option>\n";
			}
			if (isset($index[$value])) {
				$html .= form_select_tree($title, $input_name, $input_id, $input_value, $array, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level+1, $index, $data);
			}
		}
	}
	if (!$level) {
		$html .= "</select>";
		$html .= "<br/><div id='$input_id-help' style='display:inline-block !important;'></div>";
		$html .= "<input type='hidden' name='def[$input_name]' value='[type=dropdown],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
		$html .= "</div>\n";
	}
	return $html;
}

?>
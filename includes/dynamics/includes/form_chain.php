<?php


function make_chain_cat_opts(&$result, $id_col, $cat_col, $title_col, $order_col = FALSE, $url_col = FALSE) {
	$master_sort = sorter($result, $order_col);
	foreach ($master_sort as $data) {
		$title = $data[$title_col];
		$order = $data[$order_col];
		$cat = $data[$cat_col];
		$id = $data[$id_col];
		if ((!empty($url_col))) {
			$url = $data[$url_col];
		} else {
			$url = "";
		}
		if (strpos($url, "class=") !== FALSE) {
		} else {
			if ($cat == 0) {
				$option[] = array("0" => "M$order. $title", "1" => "$id", "2" => "$id");
			} else {
				$option[] = array("0" => "$order. $title", "1" => "$id", "2" => "$cat");
			}
			if (array_key_exists("children", $data)) {
				$option = array_merge($option, make_chain_cat_opts($data['children'], $id_col, $cat_col, $title_col, $order_col, $url_col));
			}
		}
	}
	return $option;
}

## This is for the menu manager specific use.
function form_select_chain_hierarchy($title, $input_name, $input_id, $option_array, $input_value = FALSE, $chain_to_parent_id, $name, $self_id, $self_cat, $master_id, $indent = FALSE, $child_level = FALSE, $array = FALSE) {
	global $_POST;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."select2/select2.css' rel='stylesheet' />");
	}
	/**
	 * Coded by Hien
	 * Select2 Chain Hierarchy - This is SUPER COMPLEX STUFF
	 * This special API calls itself over and over again.
	 * Version: 1.00 Stable
	 * */
	if (isset($title) && ($title != "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	if (isset($input_name) && ($input_name != "")) {
		$input_name = stripinput($input_name);
	} else {
		$input_name = "";
	}
	if (isset($input_value) && ($input_value != "")) {
		$input_value = stripinput($input_value);
	} else {
		$input_value = "";
	}
	if (isset($chain_to_parent_id) && ($chain_to_parent_id != "")) {
		$chain_to_parent_id = stripinput($chain_to_parent_id);
	} else {
		$chain_to_parent_id = "";
	}
	if (isset($name) && ($name != "")) {
		$name = stripinput($name);
	} else {
		$name = "";
	}
	if (isset($self_id) && ($self_id != "")) {
		$self_id = stripinput($self_id);
	} else {
		$self_id = "";
	}
	if (isset($self_cat) && ($self_cat != "")) {
		$self_cat = stripinput($self_cat);
	} else {
		$self_cat = "";
	}
	if (isset($master_id) && ($master_id != "")) {
		$master_id = stripinput($master_id);
	} else {
		$master_id = "";
	}
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$required = "";
		$placeholder = "";
		$deactivate = "";
		$labeloff = "";
		$multiple = "";
		$width = "style='width:250px;'";
	} else {
		$required = (array_key_exists('required', $array)) ? $array['required'] : "";
		$is_multiple = (array_key_exists('is_multiple', $array)) ? $array['is_multiple'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$labeloff = (array_key_exists('labeloff', $array)) ? $array['labeloff'] : "";
		$width = (array_key_exists('width', $array)) ? "style='width:".$array['width']."'" : "style='width:250px;'";
		// $requested by Tyler for his project
		if (($required == "1") && (empty($input_value))) {
			$state_validation = "has-error";
		} else {
			$state_validation = "";
		}
		$multiple = ($is_multiple == "1") ? "multiple" : "";
	}
	$i = & $i;
	$counter = & $counter;
	if (!isset($indent)) {
		$indent = 0;
		$i = "";
		$html = "";
		$children = array();
		$counter = count($option_array);
	}
	if (!isset($child_level)) {
		$child_level = 0;
	} // first run must reset the space.
	if ($indent == "1") {
		$opt_pattern = "- ";
	} else if ($indent > "1") {
		$opt_pattern = str_repeat("&nbsp;&nbsp;", $child_level)."|-"; // check current ident add: <li>".$ident."</li>
	} else {
		$html = "";
		$html .= "<div class='form-group ".$state_validation." lres'><label class='col-lg-3 control-label' for='$input_id'>$title</label>";
		$html .= "<div class='col-lg-9'>";
		$html .= "<select name='$input_name' id='$input_id' $width ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "")." $multiple>";
		$opt_pattern = "";
		$i = "0";
		$child_level = "0"; // just dump a reset anyway, because this is hierarchy and $indent=0 is the start anyway.
		$counter = count($option_array); // this one jumps like crazy, but on single level it pass the test.
	}
	if (is_array($option_array)) {
		$html = & $html;
		foreach ($option_array as $arr) { // outputs: key, value, class - in order
			if ($input_value !== "" && ($input_value == $arr[$self_id])) {
				$select = "selected";
			} else {
				$select = "";
			}
			$subclass = "class='$arr[$master_id]'";
			$html .= "<option $subclass value='$arr[$self_id]' $select>$opt_pattern ".$arr[$name]."</option>";
			if (array_key_exists('children', $arr)) {
				if ($arr[$self_cat] == "0") {
					$indent = 1;
				} else {
					$indent = 2;
					$child_level++;
				}
				//$html .= "<option>has child</option>";
				$html .= form_select_chain_hierarchy($title, $input_name, $input_id, $arr['children'], $input_value, $chain_to_parent_id, $name, $self_id, $self_cat, $master_id, $indent, $child_level);
			}
			$i++;
		}
	} else {
		$html .= "<option value=''>No Option Available</option>";
	}
	if ($i == $counter) {
		$html .= "</select>";
		$html .= add_to_jquery("
        $('#".$input_id."').select2({
		placeholder: '".$placeholder."',
		allowClear: true
		});
        ");
		$html .= add_to_jquery("
        $('#".$input_id."').chained('#".$chain_to_parent_id."');
        ");
		$html .= "</div></div>";
	}
	return $html;
}

function form_select_chain($title, $input_name, $input_id, $option_array, $input_value = FALSE, $chain_to_parent_id, $array = FALSE) {
	global $_POST;
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."select2/select2.css' rel='stylesheet' />");
	}
	if (!defined("CHAIN")) {
		define("CHAIN", TRUE);
		add_to_footer("<script src='".DYNAMICS."chainselect/jquery.chained.js'></script>");
	}
	if (isset($title) && ($title !== "")) {
		$title = stripinput($title);
	} else {
		$title = "";
	}
	//if (isset($input_name) && ($input_name !=="")) { $input_name = stripinput($input_name); } else { $input_name = ""; }
	//if (isset($desc) && ($desc !=="")) { $desc = stripinput($desc); } else { $desc = ""; }
	//if (isset($input_value) && ($input_value !=="")) { $input_value = stripinput($input_value); } else { $input_value = ""; }
	if (isset($is_order) && ($is_order !== "")) {
		$is_order = stripinput($is_order);
	} else {
		$is_order = "";
	}
	if (!is_array($array)) {
		$array = array();
		$state_validation = "";
		$required = "";
		$placeholder = "";
		$deactivate = "";
		$labeloff = "";
		$multiple = "";
		$width = "style='width:250px;'";
		$allowclear = "";
		$well = "";
	} else {
		$required = (array_key_exists('required', $array)) ? $array['required'] : "";
		$is_multiple = (array_key_exists('is_multiple', $array)) ? $array['is_multiple'] : "";
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$labeloff = (array_key_exists('labeloff', $array)) ? $array['labeloff'] : "";
		$width = (array_key_exists('width', $array)) ? "style='width:".$array['width']."'" : "style='width:250px;'";
		$well = (array_key_exists('well', $array)) ? "well" : "";
		$allowclear = ($placeholder !== "") ? "allowClear:true" : "";
		// $requested by Tyler for his project
		if (($required == "1") && (empty($_POST['$input_name']))) {
			$state_validation = "has-error";
		} else {
			$state_validation = "";
		}
		$multiple = ($is_multiple == "1") ? "multiple" : "";
	}
	$html = "";
	if (!empty($title)) {
		$html .= "<div class='form-group ".$state_validation."'>";
		$html .= "<label for='$input_id' class='col-sm-12 col-md-3 col-lg-3 control-label'>$title</label>";
		$html .= "<div class='col-sm-12 col-md-9 col-lg-9 $well'>";
	}
	$html .= "<select name='$input_name' id='$input_id' $width ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "")." $multiple>";
	$html .= "<option value=''></option>";
	if (is_array($option_array)) {
		foreach ($option_array as $arr) { // outputs: key, value, class - in order
			//print_p($arr);
			if ($is_order == 1) {
				if ($input_value !== "" && ($arr['3'] == "1")) {
					$select = "selected";
				} else {
					$select = "";
				}
			} else {
				// $arr['1'] = link_value
				if ($input_value !== "" && ($input_value == $arr['1'])) {
					$select = "selected";
				} else {
					$select = "";
				}
			}
			// the fapi requires = array("1"=>"country", "2"=>"value");
			// $arr['2'] = parent value.
			if (array_key_exists("2", $arr)) {
				$subclass = "class='".$arr['2']."'";
			} else {
				$subclass = "";
			} // this one is used as chain selects
			$html .= "<option $subclass value='".$arr['1']."' $select>".$arr['0']."</option>";
		}
	} else {
		$html .= "<option value=''></option>";
	}
	$html .= "</select>";
	$html .= add_to_jquery("
        $('#".$input_id."').select2({
    	placeholder: '".$placeholder."',
		$allowclear
		});
        ");
	$html .= add_to_jquery("
    $('#".$input_id."').chained('#".$chain_to_parent_id."');
    ");
	if (!empty($title)) {
		$html .= "</div></div>";
	}
	return $html;
}

?>
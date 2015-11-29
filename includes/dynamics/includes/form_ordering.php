<?php


function make_order_opts(&$result, $id_col, $cat_col, $title_col, $order_col) {
    $master_sort = sorter($result, $order_col);
    foreach ($master_sort as $data) {
        $title = $data[$title_col];
        $order = $data[$order_col];
        $cat = $data[$cat_col];
        $id = $data[$id_col];
        $option[] = array("id" => "$id", "title" => "".$order.". ".$title."", "order" => "$order", "cat" => "$cat");
        if (array_key_exists("children", $data)) {
            $option = array_merge($option,
                                  make_order_opts($data['children'], $id_col, $cat_col, $title_col, $order_col));
        }
    }

    return $option;
}

function form_select_order($title, $input_name, $input_id, $option_array, $input_value = FALSE, $chain_to_parent_id, $array = FALSE) {
    global $_POST;
    if (!defined("SELECT2")) {
        define("SELECT2", TRUE);
        add_to_footer("<script src='".TEMPLATES_LIB."select2/select2.min.js'></script>");
        add_to_head("<link href='".TEMPLATES_LIB."select2/select2.css' rel='stylesheet' />");
    }
    if (!defined("SELECTCHAIN")) {
        define("SELECTCHAIN", TRUE);
        add_to_head("<script type='text/javascript' src='".TEMPLATES_LIB."chainselect/jquery.chained.js'></script>");
    }
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
    if (isset($desc) && ($desc !== "")) {
        $desc = stripinput($desc);
    } else {
        $desc = "";
    }
    if (isset($input_value) && ($input_value !== "")) {
        $input_value = stripinput($input_value);
    } else {
        $input_value = "";
    }
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
        $allowclear = "";
        $width = "style='width:250px;'";
    } else {
        $required = (array_key_exists('required', $array)) ? $array['required'] : "";
        $is_multiple = (array_key_exists('is_multiple', $array)) ? $array['is_multiple'] : "";
        $placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
        $deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
        $labeloff = (array_key_exists('labeloff', $array)) ? $array['labeloff'] : "";
        $width = (array_key_exists('width', $array)) ? "style='width:".$array['width']."'" : "style='width:250px;'";
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
    $html .= "<div class='form-group ".$state_validation." lres'><label class='col-lg-3 control-label' for='$input_id'>$title</label>";
    $html .= "<div class='col-lg-9'>";
    $html .= "<select name='$input_name' id='$input_id' $width $multiple>";
    if (is_array($option_array)) {
        foreach ($option_array as $arr) { // outputs: key, value, class - in order
            // will not select
            //print_p($option_array);
            //print_p($input_value);
            // accepts format input:
            /*
             *  $arr['cat'] = "the parent's value"
             *  $arr['order'] = "the order"
             *  $arr['title'] = "the name"
             */
            if ($input_value !== "" && ($arr['order'] == $input_value)) {
                $select = "selected";
            } else {
                $select = "";
            }
            if (array_key_exists("cat", $arr)) {
                $subclass = "class='".$arr['cat']."'";
            } else {
                $subclass = "";
            } // this one is used as chain selects
            $html .= "<option $subclass value='".$arr['order']."'>".$arr['title']."</option>";
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
    $html .= "</div></div>";

    return $html;
}


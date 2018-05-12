<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_ordering.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function make_order_opts(&$result, $id_col, $cat_col, $title_col, $order_col) {
    $master_sort = sorter($result, $order_col);
    $option = [];
    foreach ($master_sort as $data) {
        $title = $data[$title_col];
        $order = $data[$order_col];
        $cat = $data[$cat_col];
        $id = $data[$id_col];
        $option[] = ["id" => "$id", "title" => "".$order.". ".$title."", "order" => "$order", "cat" => "$cat"];
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
        add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
        add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
    }
    if (!defined("SELECTCHAIN")) {
        define("SELECTCHAIN", TRUE);
        add_to_head("<script type='text/javascript' src='".DYNAMICS."assets/chainselect/jquery.chained.js'></script>");
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
    if (!is_array($array)) {
        $state_validation = "";
        $placeholder = "";
        $multiple = "";
        $allowclear = "";
        $width = "style='width:250px;'";
    } else {
        $required = (array_key_exists('required', $array)) ? $array['required'] : "";
        $is_multiple = (array_key_exists('is_multiple', $array)) ? $array['is_multiple'] : "";
        $placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
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
    add_to_jquery("
        $('#".$input_id."').select2({
        placeholder: '".$placeholder."',
        $allowclear
        });
    ");
    add_to_jquery("$('#".$input_id."').chained('#".$chain_to_parent_id."');");
    $html .= "</div></div>";

    return $html;
}

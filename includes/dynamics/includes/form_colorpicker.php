<?php

/*

Courtesy of : Mjolnic @ http://mjolnic.github.io/bootstrap-colorpicker/
Source: http://mjaalnir.github.io/bootstrap-colorpicker
Previously by : eycon.ro
Ported to PHP-Fusion by : Hien (Frederick MC Chan)

*/

function form_colorpicker($title=false, $input_name, $input_id, $input_value=false, $array=false) {

    if (!defined("COLORPICKER")) {
        define("COLORPICKER", true);
        add_to_head("<link href='".DYNAMICS."assets/colorpick/css/bootstrap-colorpicker.css' rel='stylesheet' media='screen' />");
        add_to_head("<script src='".DYNAMICS."assets/colorpick/js/bootstrap-colorpicker.js'></script>");
    }

    global $_POST;

    $title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
    $title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
    $input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
    $input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";

    if (!is_array($array)) {
        $array = array();
        $state_validation = "";
        $placeholder = "";
        $width = "250px";
        $class = "";
        $well = "";
        $deactivate = "";
        $stacking = "";
        $format = "";
        $helper_text = "";
        $required = '0';
        $safemode = '0';

    } else {
        $required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
        $safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
        $placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
        $deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
        $class = (array_key_exists('class', $array)) ? $array['class'] : "";
        $width = (array_key_exists('width', $array)) ? $array['width'] : "250px";
        $well = (array_key_exists('well', $array)) ? "style='margin-top:-10px;'" : "";
        $stacking = (array_key_exists("stacking", $array)) ? 1 : "";
        $format = (array_key_exists("format", $array)) ? $array['format'] : "rgba"; // options = the color format - hex | rgb | rgba.
        $helper_text = (array_key_exists("helper",$array)) ? $array['helper'] : "";
    }

    $html = "";

    if (!empty($title)) {
        // turn off coloumn

        if ($stacking == 1) {

            $html .= open_form_title($title, $input_id, $helper_text, $required);

        } else {

            $html .= open_form_title_2($title, $input_id, $helper_text, $required);

        }

    }

    // start colorpicker
    $html .= "<div id='$input_id' style='width:$width' class='input-group colorpicker-component bscp colorpicker-element' data-color='$input_value' data-color-format='$format'>";

    $html .= "<input type='text' name='$input_name' class='form-control $class' id='".$input_id."' value='$input_value' data-color-format='$format' placeholder='".$placeholder."' ".($deactivate =="1" && (isnum($deactivate)) ? "readonly":"").">";
    $html .= "<input type='hidden' name='def[$input_name]' value='[type=color],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";

    $html .= "<span id='$input_id-cp' class='input-group-addon'>";

    $html .= "<i style='background: rgba(255,255,255,1);'></i>";

    $html .= "</span></div>";

    if (!empty($title)) {

        $html .= close_form_title();

    }

    $html .= add_to_jquery("
    $('#$input_id').colorpicker(
    {
    format : '$format'
    });
    ");

    return $html;

}


?>
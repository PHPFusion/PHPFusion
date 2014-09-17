<?php

/* http://gregpike.net/demos/bootstrap-file-input/demo.html*/
function form_fileinput($title = FALSE, $input_name, $input_id, $upload_path, $input_value = FALSE, $array = FALSE) {
    $title      = (isset($title) && (!empty($title))) ? stripinput($title) : "";
    $title2     = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
    // ok, start
    if (!defined('form_fileinput')) {
        add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput.min.css' media='all' rel='stylesheet' type='text/css' />");
        add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/fileinput.min.js' type='text/javascript'></script>");
        define('form_fileinput', TRUE);
    }
    // 4 choices to sub-array
    // a. icon, b. button, c.dropdown list d.dropdown with modal
    if (!is_array($array)) {
        $array       = array();
        $required    = 0;
        $safemode    = 1;
        $deactivate  = "";
        $width       = "";
        $label       = 'Browse ...';
        $class       = 'btn btn-primary btn-sm';
        $helper_text = '';
        $inline      = '';
        $url         = '';
        $type        = 'image';
        $max_size    = '';
    } else {
        $deactivate  = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
        $label       = (array_key_exists('label', $array)) ? $array['label'] : 'Browse ...';
        $class       = (array_key_exists('class', $array)) ? $array['class'] : 'btn-primary';
        $required    = (array_key_exists('required', $array) && ($array['required'] == 1)) ? '1' : '0';
        $safemode    = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? '1' : '0';
        $width       = (array_key_exists('width', $array)) ? $array['width'] : "";
        $helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
        $inline      = (array_key_exists('rowstart', $array)) ? 1 : 0;
        $url         = (array_key_exists('url', $array)) ? $array['url'] : ''; // for ajax uplaod file path
        $type        = (array_key_exists('image', $array)) && ($array['image'] == 1) ? 'image' : 'files'; // image only or all mimes.
        $max_size    = (array_key_exists('max_size', $array) && $array['max_size']) ? $array['max_size'] : '3145728'; // defaults to 3mb
    }
    $html = '';
    $html .= "<div id='$input_id-field' class='form-group m-b-0'>\n";
    $html .= "<label class='control-label ".($inline ? "col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n";
    $html .= ($inline) ? "<div class='col-sm-9 col-md-9 col-lg-9'>\n" : "";
    $html .= "<input type='file' name='$input_name' id='$input_id' class='input-sm file-preview-image' >\n";
    $html .= "<input type='hidden' name='def[$input_name]' value='[type=$type],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode],[path=$upload_path],[maxsize=$max_size]' readonly>";
    $html .= "<div id='$input_id-help'></div>";
    $html .= ($inline) ? "</div>\n" : "";
    $html .= "</div>\n";
    add_to_jquery("
        $('#".$input_id."').fileinput({
        previewFileType: 'any',
        browseClass: 'btn btn-sm $class',
        uploadClass: 'btn btn-default btn-sm',
        captionClass : 'input-sm',
        removeClass : 'btn btn-sm btn-default',
        browseLabel: '$label',
        browseIcon: '<i class=\"entypo cloud  m-r-10\"></i>',
        ".($url ? "uploadUrl : '$url'," : '')."
        ".($url ? '' : 'showUpload: false')."
        });
    ");
    return $html;
}

function form_image($title = FALSE, $input_name, $input_id, $folder, $input_value = FALSE, $array = FALSE) {
    $title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
    $title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
    $input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
    $input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";
    if (!is_array($array)) {
        $helper_text = "";
        $required = 0;
        $safemode = 0;
        $stacking = 0;
        $placeholder = "";
        $deactivate = "";
        $width = "250px";
        $class = "";
    } else {
        $stacking = (array_key_exists('stacking', $array)) ? 1 : "";
        $helper_text = (array_key_exists("helper", $array)) ? $array['helper'] : "";
        $required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
        $safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
        $placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "Please choose one..";
        $deactivate = (array_key_exists("deactivate", $array) && ($array['deactivate'] == "1")) ? 1 : 0;
        $width = (array_key_exists('width', $array)) ? "style='width:".$array['width']."'" : "style='width:250px;'";
        $class = (array_key_exists('class', $array)) ? $array['class'] : "";
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
    $html .= "";
    $html .= "<div class='input-group' style='width:$width'>";
    $html .= "<span class='input-group-btn'><a type='button' data-toggle='modal' data-target='#fileManagerbox' class='btn btn-default btn-sm'>Choose Image</a></span>";
    $html .= "<input type='text' name='$input_name' style='width:$width' class='form-control $class' id='".$input_id."' $width value='$input_value' placeholder='".$placeholder."' readonly>";
    $html .= "<input type='hidden' name='def[$input_name]' value='[type=media],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]' readonly>";
    $html .= "</div>";
    $html .= "<div class='modal fade' id='fileManagerbox' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true' style='z-index:1100;'>";
    $html .= "<div class='modal-dialog' style='width:1000px'>\n";
    $html .= "<div class='modal-content '>\n";
    $html .= "<div class='modal-header'>\n";
    $html .= " <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>\n";
    $html .= "<h4 class='modal-title' id='myModalLabel'>Choose $title2</h4>\n";
    $html .= "</div>\n";
    $html .= "<div class='modal-body'>\n";
    $html .= "<div class='row'>\n";
    $files = glob("$folder/*.{jpg,JPEG,jpeg,png,gif,GIF,bmp,BMP,tiff,TIFF}", GLOB_BRACE);
    $html .= "<style>";
    $html .= "
    .img-container {
    height: 91px;
    width: 133px;
    padding: 0px;
    border-radius: 4px 4px 0x 0px;
    overflow: hidden;
    display: block;
    text-align: center;
    vertical-align: middle;
    margin: auto;
    }

    .img-container img{
    max-width: 400px !important;
    max-height: 102px !important;
    border-radius:4px 4px 0px 0px;
    }
    ";
    $html .= "</style>";
    foreach ($files as $arr => $v) {
        $path = $v;
        $title = str_replace($folder, "", $v);
        $html .= "<div class='col-sm-2 col-md-2 col-lg-2 filepick'>\n";
        $html .= "<div class='panel panel-default'>\n";
        $html .= "<div class='panel-body img-container'>\n";
        $html .= "<img src='$path' style='overflow-x:hidden;'>\n";
        $html .= "</div>\n<div class='panel-footer'>\n";
        $html .= "<a href='#' class='btn btn-primary btn-xs btn-block' data-id='$path'>\nChoose\n</a>\n";
        $html .= "</div>\n";
        $html .= "</div>\n</div>\n";
    }
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "<div class='modal-footer'>\n";
    $html .= "<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>\n";
    $html .= "<button type='button' class='btn btn-primary'>Save changes</button>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    $html .= "</div>\n";
    if (!empty($title)) {
        // turn off column
        $html .= close_form_title();
    }
    add_to_jquery("

        $('div.filepick a').on('click', function(e){
            var ce_id = $(this).attr('data-id');
            //alert(ce_id);
             $('#".$input_id."').val(ce_id);
            $('#fileManagerbox').modal('hide');
         });

    ");
    return $html;
}

?>
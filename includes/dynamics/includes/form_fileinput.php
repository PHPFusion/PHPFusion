<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_fileinput.php
| Author: Frederick MC CHan (Hien)
| Credits: http://plugins.krajee.com/file-input
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_fileinput($input_name, $label = '', $input_value = FALSE, array $options = array()) {
	global $locale, $defender;
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$error_class = $defender->inputHasError($input_name) ? "has-error" : "";
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$template_choices = array('classic', 'modern');
	$default_settings = array(
		"input_id" => $input_name,
		"upload_path" => IMAGES,
		"required" => false,
		"safemode" => false,
		"deactivate" => false,
		"preview_off" => false,
		"type" => "image", //// ['image', 'html', 'text', 'video', 'audio', 'flash', 'object']
		"width" => "100%",
		"label" => $locale['browse'],
		"inline" => true,
		"class" => "",
		"error_text" => $locale['error_input_file'],
		"btn_class" => "btn-default",
		"icon" => "fa fa-upload",
		"jsonurl" => false,
		"valid_ext" => ".jpg,.png,.PNG,.JPG,.JPEG,.gif,.GIF,.bmp,.BMP",
		"thumbnail" => false,
		"thumbnail_w" => 300,
		"thumbnail_h" => 300,
		"thumbnail_folder" => "",
		"thumbnail_suffix" => "_t1",
		"thumbnail2" => false,
		"thumbnail2_w" => 600,
		"thumbnail2_h" => 400,
		"thumbnail2_suffix" => "_t2",
		"delete_original" => false,
		"max_width" => 1800,
		"max_height" => 1600,
		"max_byte" => 1500000,
		"max_count" => 1,
		"multiple" => false,
		"template" => "classic"
	);
	$options += $default_settings;

	if (!is_dir($options['upload_path'])) {
		$options['upload_path'] = $default_settings['upload_path'];
	}
	$options['thumbnail_folder'] = rtrim($options['thumbnail_folder'], "/");
	if (!in_array($options['template'], $template_choices)) {
		$options['template'] = $default_settings['template'];
	}
	// always trim id
	$options['input_id'] = trim($options['input_id'], "[]");
	// default max file size
	$format = '';
	// file type if single filter, if not will accept as object if left empty.
	$type_for_js = null;
	if ($options['type']) {
		if (!stristr($options['type'], ',') && $options['type']) {
			if ($options['type'] == 'image') {
				$format = "image/*";
			} elseif ($options['type'] == 'video') {
				$format = "video/*";
			} elseif ($options['type'] == 'audio') {
				$format = "audio/*";
			}
		}
		$type_for_js = json_encode((array)$options['type']);
	}

	$value = '';
	if (!empty($input_value)) {
		if (is_array($input_value)) {
			foreach($input_value as $value) {
				$value[] = "<img class='img-responsive' src='".$value."/>";
			}
		} else {
			$value = "<img class='img-responsive' src='".$input_value."'/>";
		}
		$value = json_encode($value);
	}

	if (!defined('form_fileinput')) {
		add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput.min.css' media='all' rel='stylesheet' type='text/css' />");
		add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/fileinput.min.js' type='text/javascript'></script>");
		define('form_fileinput', TRUE);
	}

	$html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']."' ".($options['width'] && !$label ? "style='width: ".$options['width']." !important;'" : '').">\n";
	$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='".$options['input_id']."'>$label ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")."'>\n" : "";
	$html .= "<input type='file' ".($format ? "accept='".$format."'" : '')." name='".$input_name."' id='".$options['input_id']."' style='width:".$options['width']."' ".($options['deactivate'] ? 'readonly' : '')." ".($options['multiple'] ? "multiple='1'" : '')." />\n"; //class='file-preview-".$type."'
	$html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$defender->add_field_session(array(
		'input_name' 	=> 	trim($input_name, '[]'),
		'type'			=>	((array)$options['type']==array('image') ? 'image' : 'file'),
		'title'			=>	$title,
		'id' 			=>	$options['input_id'],
		'required'		=>	$options['required'],
		'safemode' 		=> 	$options['safemode'],
		'error_text'	=> 	$options['error_text'],
		'path'			=> $options['upload_path'],
		'thumbnail_folder'	=>	$options['thumbnail_folder'],
		'thumbnail' 	=> $options['thumbnail'],
		'thumbnail_suffix' 	=> $options['thumbnail_suffix'],
		'thumbnail_w' 	=> $options['thumbnail_w'],
		'thumbnail_h' 	=> $options['thumbnail_h'],
		'thumbnail2'	=> $options['thumbnail2'],
		'thumbnail2_w' 	=> $options['thumbnail2_w'],
		'thumbnail2_h' 	=> $options['thumbnail2_h'],
		'thumbnail2_suffix' => $options['thumbnail2_suffix'],
		'delete_original' => $options['delete_original'],
		'max_width'		=>	$options['max_width'],
		'max_height'	=>	$options['max_height'],
		'max_count'		=> $options['max_count'],
		'max_byte'		=>	$options['max_byte'],
		'multiple'		=>	$options['multiple'],
		'valid_ext'		=>	$options['valid_ext'],
	 )
	);
	switch($options['template']) {
		case "classic":
	add_to_jquery("
		$('#".$options['input_id']."').fileinput({
		allowedFileTypes: ".$type_for_js.",
		allowedPreviewTypes : ".$type_for_js.",
	".($value ? "initialPreview: ".$value.", " : '')."
	".($options['preview_off'] ? "showPreview: false, " : '')."
	browseClass: 'btn ".$options['btn_class']." button',
	uploadClass: 'btn btn-default button',
	captionClass : '',
	removeClass : 'btn btn-default button',
	browseLabel: '".$options['label']."',
	browseIcon: '<i class=\"".$options['icon']." m-r-10\"></i>',
	".($options['jsonurl'] ? "uploadUrl : '".$options['url']."'," : '')."
	".($options['jsonurl'] ? '' : 'showUpload: false')."
	});
	");
			break;
		case "modern":
		add_to_jquery("
		$('#".$options['input_id']."').fileinput({
		allowedFileTypes: ".$type_for_js.",
		allowedPreviewTypes : ".$type_for_js.",
		".($value ? "initialPreview: ".$value.", " : '')."
		".($options['preview_off'] ? "showPreview: false, " : '')."
		browseClass: 'btn btn-modal',
		uploadClass: 'btn btn-modal',
		captionClass : '',
		removeClass : 'btn button',
		browseLabel: 'Click to Add Photo',
		browseIcon: '<i class=\"fa fa-plus m-r-10\"></i>',
		showCaption: false,
		showRemove: false,
		showUpload: false,
		layoutTemplates: {
		 main2: '<div class=\"btn-photo-upload btn-link\">'+' {browse}'+' </div></span></div> {preview}',
		 },
		});
		");
		break;
	}
	return $html;
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_fileinput.php
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
/* http://plugins.krajee.com/file-input  - many many more options */
function form_fileinput($title = FALSE, $input_name, $input_id, $upload_path, $input_value = FALSE, array $options = array()) {
	global $locale, $settings, $defender;
	$title = (isset($title) && (!empty($title))) ? stripinput($title) : "";
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

	$options += array(
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
		'error_text' => !empty($options['error_text']) && $options['error_text'] ? $options['error_text'] : '', // error text feedback
		'width' => !empty($options['width']) && $options['width'] ? $options['width'] : '100%',
		'label' => !empty($options['label']) && $options['label'] ? $options['label'] : $locale['browse'],
		'inline' => !empty($options['inline']) && $options['inline'] == 1 ? '1' : '0',
		'class' => !empty($options['class']) && $options['class'] ? $options['class'] : '', // form-group class
		'btn_class' => !empty($options['btn_class']) && $options['btn_class'] ? $options['btn_class'] : 'btn-default', // upload button class
		'icon' => !empty($options['icon']) && $options['icon'] ? $options['icon'] : 'entypo upload-cloud', // upload button class
		'preview_off' => !empty($options['preview_off']) && $options['preview_off'] == 1 ? 1 : 0,
		'type' => !empty($options['type']) && $options['type'] ? $options['type'] : 'object', // ['image', 'html', 'text', 'video', 'audio', 'flash', 'object'] <--- defender goes for this maybe
		'valid_ext' => !empty($options['valid_ext']) && $options['valid_ext'] ? $options['valid_ext'] : '',
		// ajax
		'jsonurl' => !empty($options['jsonurl']) && $options['jsonurl'] ? $options['jsonurl'] : 0,
		// the only real thumbnail
		'thumbnail' => !empty($options['thumbnail']) && $options['thumbnail'] == 1 ? 1 : 0,
		'thumbnail_w' 	=> !empty($options['thumbnail_w']) && isnum($options['thumbnail_w']) ? $options['thumbnail_w'] : 150,
		'thumbnail_h' 	=> !empty($options['thumbnail_h']) && isnum($options['thumbnail_h']) ? $options['thumbnail_h'] : 150,
		'thumbnail_folder' 	=> !empty($options['thumbnail_folder']) && $options['thumbnail_folder'] && $options['thumbnail'] ? rtrim($options['thumbnail_folder'], '/') : '',
		'thumbnail_suffix' 	=> !empty($options['thumbnail_suffix']) ? $options['thumbnail_suffix'] : '_t1',
		// fusion use this to shrink image and delete original as true
		'thumbnail2' 	=> !empty($options['thumbnail2']) && $options['thumbnail2'] == 1 ? 1 : 0,
		'thumbnail2_w' 	=> !empty($options['thumbnail2_w']) && isnum($options['thumbnail2_w']) ? $options['thumbnail2_w'] : 150,
		'thumbnail2_h' 	=> !empty($options['thumbnail2_h']) && isnum($options['thumbnail2_h']) ? $options['thumbnail2_h'] : 150,
		'thumbnail2_suffix' 	=> !empty($options['thumbnail2_suffix']) ? $options['thumbnail2_suffix'] : '_t2',
		'delete_original' => !empty($options['delete_original']) && $options['delete_original'] == 1 ? 1 : 0,
		// max upload
		'max_width'		=>	!empty($options['max_width']) && isnum($options['max_width']) ? $options['max_width'] : 1800,
		'max_height'	=>	!empty($options['max_height']) && isnum($options['max_height']) ? $options['max_height'] : 1600,
		'max_byte'		=>	!empty($options['max_byte']) && isnum($options['max_byte']) ? $options['max_byte'] : 1500000, // 1.5 million bytes is 1.5mb
		'multiple' => !empty($options['multiple']) && $options['multiple'] == 1 ? 1 : 0,
	);

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
				$value[] = "<img src='".$value."/>";
			}
		} else {
			$value = "<img src='".$input_value."'/>";
		}
		$value = json_encode($value);
	}

	if (!defined('form_fileinput')) {
		add_to_head("<link href='".DYNAMICS."assets/fileinput/css/fileinput.min.css' media='all' rel='stylesheet' type='text/css' />");
		add_to_footer("<script src='".DYNAMICS."assets/fileinput/js/fileinput.min.js' type='text/javascript'></script>");
		define('form_fileinput', TRUE);
	}

	$html = "<div id='$input_id-field' class='form-group ".$options['class']."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>$title ".($options['required'] ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "";
	$html .= "<input type='file' ".($format ? "accept='".$format."'" : '')." name='".$input_name."' id='".$input_id."' style='width:".$options['width']."' ".($options['deactivate'] ? 'readonly' : '')." />\n"; //class='file-preview-".$type."'
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$defender->add_field_session(array(
		'input_name' 	=> 	$input_name,
		'type'			=>	((array)$options['type']==array('image') ? 'image' : 'file'),
		'title'			=>	$title2,
		'id' 			=>	$input_id,
		'required'		=>	$options['required'],
		'safemode' 		=> 	$options['safemode'],
		'error_text'	=> 	$options['error_text'],
		'path'			=> $upload_path,
		'thumbnail_folder'	=>	$options['thumbnail_folder'],
		'thumbnail' 	=> $options['thumbnail'],
		'thumbnail_w' 	=> $options['thumbnail_w'],
		'thumbnail_h' 	=> $options['thumbnail_h'],
		'thumbnail2'	=> $options['thumbnail2'],
		'thumbnail2_w' 	=> $options['thumbnail2_w'],
		'thumbnail2_h' 	=> $options['thumbnail2_h'],
		'delete_original' => FALSE,
		'max_width'		=>	$options['max_width'],
		'max_height'	=>	$options['max_height'],
		'max_byte'		=>	$options['max_byte'],
		'multiple'		=>	$options['multiple'],
		'valid_ext'		=>	$options['valid_ext'],
	 ));
	add_to_jquery("
	$('#".$input_id."').fileinput({
	".($value ? "initialPreview: ".$value.", " : '')."
	".($options['preview_off'] ? "showPreview: false, " : '')."
	allowedFileTypes: ".$type_for_js.",
	allowedPreviewTypes : ".$type_for_js.",
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

	return $html;
}
?>
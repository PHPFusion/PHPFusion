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
	global $locale, $settings;
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
		'url' => !empty($options['url']) && $options['url'] ? $options['url'] : '', // path to store the image
		'thumbnail' => !empty($options['thumbnail']) && $options['thumbnail'] ? $options['thumbnail'] : '', // path to store the thumbnail.
		'max_b' => !empty($options['max_b']) && isnum($options['max_b']) ? $options['max_b'] : 0, // maximum bytes allowed.
		'type' => !empty($options['type']) && $options['type'] ?$options['type'] : 0, // ['image', 'html', 'text', 'video', 'audio', 'flash', 'object']
		'preview_off' => !empty($options['preview_off']) && $options['preview_off'] == 1 ? 1 : 0,
		'mime' => !empty($options['mime']) && $options['mime'] ? $options['mime'] : 0,
		'jsonmode' => !empty($options['jsonmode']) && $options['jsonmode'] ? $options['jsonmode'] : 0,
		'multiple' => !empty($options['multiple']) && $options['multiple'] == 1 ? 1 : 0,
	);

	// default max file size
	$format = '';
	$max_b = $options['max_b'] ? $options['max_b'] : $settings['download_max_b'];
	// file type if single filter, if not will accept as object if left empty.
	$type_for_js = null;
	if ($options['type']) {
		if (!stristr($options['type'], ',') && $options['type']) {
			if ($options['type'] == 'image') {
				$format = "image/*";
				$max_b = $options['max_b'] ? $options['max_b'] : $settings['photo_max_b']/1000000;
			} elseif ($options['type'] == 'video') {
				$format = "video/*";
			} elseif ($options['type'] == 'audio') {
				$format = "audio/*";
			} elseif (isset($options['format']) && $options['format'] && isset($options['type']) && $options['type']) {
				/* http://www.iana.org/assignments/media-types/media-types.xhtml */
				$format = $options['format'];
			}
		}
		$type_for_js = json_encode((array)$options['type']);
	}

	if ($options['mime']) {
		$options['mime'] = json_encode($options['mime']);
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
	$html .= ($options['inline']) ? "<div class='col-xs-12 col-sm-12 col-md-6 col-lg-6'>\n" : "";
	$html .= "<input type='file' ".($format ? "accept='".$format."'" : '')." name='".$input_name."' id='".$input_id."'  ".($options['deactivate'] ? 'readonly' : '')." />\n"; //class='file-preview-".$type."'
	$html .= "<div id='$input_id-help'></div>";
	$html .= ($options['inline']) ? "</div>\n" : "";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=".((array)$options['type']==array('image') ? 'image' : 'file')."],[title=$title2],[id=$input_id],[required=".$options['required']."],[safemode=".$options['safemode']."],[path=$upload_path],[thumbnail=".$options['thumbnail']."],".($options['error_text'] ? ",[error_text=".$options['error_text']."" : '')."' />\n";

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
	".($options['url'] && $options['jsonmode'] ? "uploadUrl : '".$options['url']."'," : '')."
	".($options['url'] && $options['jsonmode'] ? '' : 'showUpload: false')."
	});
	");

	return $html;
}
?>
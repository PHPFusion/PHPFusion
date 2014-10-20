<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_textarea.php
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



function form_textarea($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	global $locale, $userdata, $userdata; // for editor

	require_once INCLUDES."bbcode_include.php";
	include_once LOCALE.LOCALESET."admin/html_buttons.php";
	require_once INCLUDES."html_buttons_include.php";

	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($array)) {
		$required = 0;
		$safemode = 0;
		$deactivate = "";
		$height = "80px";
		$editor = 0;
		$placeholder = "";
		$inline = '';
		$form_name = 'input_form';
		$bbcode = 0;
		$html_input = 0;
		$error_text = '';
		$class = '';
		$preview = 1;
		$autosize = 0;
		$resize = 1;
		$path = IMAGES;
	} else {
		$resize = (array_key_exists('resize', $array) && ($array['resize'] == 0)) ? 0 : 1;
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$bbcode = (array_key_exists('bbcode', $array) && $array['bbcode'] == 1) ? 1 : 0;
		$html_input = (array_key_exists('html', $array) && $array['html'] == 1) ? 1 : 0;
		$preview = (array_key_exists('preview', $array) && $array['preview'] == 1) ? 1 : 0;
		$width = (array_key_exists('width', $array)) ? $array['width'] : "98%";
		$height = (array_key_exists('height', $array)) ? $array['height'] : "80px";
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$autosize = (array_key_exists("autosize", $array)) ? 1 : 0;
		$form_name = (array_key_exists('form_name', $array)) ? $array['form_name'] : 'input_form';
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$class = (array_key_exists("class", $array) && $array['class']) ? $array['class'] : '';
		$path = (array_key_exists("path", $array) && $array['path']) ? $array['path'] : '';
	}

	$type = '';
	if ($bbcode) {
		$type = 'bbcode';
	} elseif ($html_input) {
		$type = 'html_input';
	}

	if (!defined('autogrow') && $autosize) {
		define('autogrow', true);
		add_to_footer("<script src='".DYNAMICS."assets/autosize/jquery.autosize.min.js'></script>");
	}

	$input_value = html_entity_decode(stripslashes($input_value));
	$input_value = str_replace("<br />", "", $input_value);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group m-b-10 clearfix ".$class."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";

	if ($preview && $type) {
		$tab_title['title'][] = "Preview";
		$tab_title['id'][] = "prw-".$input_id."";
		$tab_title['icon'][] = '';
		$tab_title['title'][] = "Text";
		$tab_title['id'][] = "txt-".$input_id."";
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, 1);
		$html .= opentab($tab_title, $tab_active, "".$input_id."-link", '', 'editor-wrapper');
		$html .= opentabbody($tab_title['title'][1], "txt-".$input_id."", $tab_active);
	}

	$html .= ($type) ? "<div class='panel panel-default' ".($preview ? "style='border-top:0;'" : '').">\n<div class='panel-heading clearfix' style='padding-bottom:0 !important;'>\n" : '';
	if ($bbcode) {
		$html .= display_bbcodes('90%', $input_name, $form_name);
	} elseif ($html_input) {
		$html .= display_html($form_name, $input_name, TRUE, TRUE, TRUE, $path);
	}
	$html .= ($type) ? "</div>\n<div class='panel-body p-0'>\n" : '';

	$html .= "<textarea name='$input_name' style='width:100%; height:$height; ".($resize == '0' ? 'resize: none;' : '')."' class='form-control p-10 $class ".($autosize ? 'animated-height' : '')." ".($bbcode || $html_input ? "no-shadow no-border" : '')." textbox ' placeholder='$placeholder' id='$input_id' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">$input_value</textarea>\n";

	if ($type) {
		$html .= "</div>\n<div class='panel-footer'>\n";
		$html .= "<small>Word Count: <span id='".$input_id."-wordcount'></span></small>";
		add_to_jquery("
		var init_str = $('#".$input_id."').val().length;
		$('#".$input_id."-wordcount').text(init_str);
		$('#".$input_id."').on('input propertychange paste', function() {
		var str = $(this).val().length;
		$('#".$input_id."-wordcount').text(str);
		});
		");
		$html .= "</div>\n</div>\n";
	}

	if ($preview && $type) {
		$html .= closetabbody();
		$html .= opentabbody($tab_title['title'][0], "prw-".$input_id."", $tab_active);
		$html .= closetabbody();
		$html .= closetab($tab_title, $tab_active, "".$input_id."-link");
		add_to_jquery("
		// preview syntax
		$('#tab-prw-".$input_id."Preview').bind('click',function(){
		var txt_data = $('#".$input_id."').val();
		var format = '".$type."';
		var cid = '".$input_id."';
		$.ajax({
			url: '".INCLUDES."dynamics/assets/preview/preview.ajax.php',
			type: 'POST',
			dataType: 'html',
			data : { text: txt_data, editor: format, fusion_token: '".generate_token($input_id, 1, 1)."', id: cid},
			success: function(result){
			$('#prw-".$input_id."Preview').html(result);
			},
			error: function(result) {
				new PNotify({
					title: 'Error fetching result',
					text: 'There are server error and preview cannot be resolved. Please contact administrator.',
					icon: 'notify_icon n-attention',
					animation: 'fade',
					width: 'auto',
					delay: '3000'
				});
			}
			});
		});
		");
	}

	if ($autosize) {
		add_to_jquery("
		$('#".$input_id."').autosize();
		");
	}

	$html .= "<div id='$input_id-help'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=textarea],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	return $html;
}

?>
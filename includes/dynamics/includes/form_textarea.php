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

function form_textarea($title = FALSE, $input_name, $input_id, $input_value = FALSE, $options = array()) {
	global $locale, $userdata; // for editor

	require_once INCLUDES."bbcode_include.php";
	require_once INCLUDES."html_buttons_include.php";
	include_once LOCALE.LOCALESET."admin/html_buttons.php";
	include_once LOCALE.LOCALESET."error.php";

	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";

	$options += array(
		'required' => !empty($options['required']) ? : '0',
		'placeholder' => !empty($options['placeholder']) ? : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? : '',
		'width' => !empty($options['width']) ? : '98%',
		'height' => !empty($options['height']) ? : '80px',
		'class' => !empty($options['class']) ? : '',
		'inline' => !empty($options['inline']) ? : '',
		'length' => !empty($options['length']) ? : '200',
		'error_text' => !empty($options['error_text']) ? : '',
		'safemode' => !empty($options['safemode']) ? : '',
		'form_name' => !empty($options['form_name']) ? : 'input_form',
		'bbcode' => !empty($options['bbcode']) && $options['bbcode'] == 1 ? : 0,
		'html' => !empty($options['html']) && $options['html'] == 1 ? : 0,
		'resize' => !empty($options['resize']) && $options['resize'] == 0 ? : 1,
		'autosize' => !empty($options['autosize']) && $options['autosize'] == 1 ? : 0,
		'preview' => !empty($options['preview']) && $options['preview'] == 1 ? : 0,
		'path' => !empty($options['path']) && $options['path'] ? : IMAGES,
	);

	if (!defined('autogrow') && $options['autosize']) {
		define('autogrow', true);
		add_to_footer("<script src='".DYNAMICS."assets/autosize/jquery.autosize.min.js'></script>");
	}

	$input_value = html_entity_decode(stripslashes($input_value));
	$input_value = str_replace("<br />", "", $input_value);

	$html = "";
	$html .= "<div id='$input_id-field' class='form-group ".$options['class']."'>\n";
	$html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";

	if ($options['preview'] && $options['bbcode'] || $options['html']) {
		$tab_title['title'][] = $locale['preview'];
		$tab_title['id'][] = "prw-".$input_id."";
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['texts'];
		$tab_title['id'][] = "txt-".$input_id."";
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, 1);
		$html .= opentab($tab_title, $tab_active, "".$input_id."-link", '', 'editor-wrapper');
		$html .= opentabbody($tab_title['title'][1], "txt-".$input_id."", $tab_active);
	}

	$html .= ($options['bbcode'] || $options['html']) ? "<div class='panel panel-default' ".($options['preview'] ? "style='border-top:0 !important; border-radius:0 !important;'" : '').">\n<div class='panel-heading clearfix' style='padding-bottom:0 !important;'>\n" : '';
	if ($options['bbcode'] && $options['form_name']) {
		$html .= display_bbcodes('90%', $input_name, $options['form_name']);
	} elseif ($options['html'] && $options['form_name']) {
		$html .= display_html($options['form_name'], $input_name, TRUE, TRUE, TRUE, $options['path']);
	}
	$html .= ($options['bbcode'] || $options['html']) ? "</div>\n<div class='panel-body p-0'>\n" : '';
	$html .= "<textarea name='$input_name' style='width:99%; height:".$options['height']."; ".($options['resize'] == '0' ? 'resize: none;' : '')."' class='form-control m-0 p-10 ".$options['class']." ".($options['autosize'] ? 'animated-height' : '')." ".($options['bbcode'] || $options['html'] ? "no-shadow no-border" : '')." textbox ' placeholder='".$options['placeholder']."' id='$input_id' ".($options['deactivate'] == '1' ? 'readonly' : '').">$input_value</textarea>\n";

	if ($options['bbcode'] || $options['html']) {
		$html .= "</div>\n<div class='panel-footer'>\n";
		$html .= "<small>".$locale['word_count'].": <span id='".$input_id."-wordcount'></span></small>";
		add_to_jquery("
		var init_str = $('#".$input_id."').val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
		$('#".$input_id."-wordcount').text(init_str);
		$('#".$input_id."').on('input propertychange paste', function() {
		var str = $(this).val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
		$('#".$input_id."-wordcount').text(str);
		});
		");
		$html .= "</div>\n</div>\n";
	}

	if ($options['preview'] && $options['bbcode'] || $options['html']) {
		$html .= closetabbody();
		$html .= opentabbody($tab_title['title'][0], "prw-".$input_id."", $tab_active);
		$html .= closetabbody();
		$html .= closetab($tab_title, $tab_active, "".$input_id."-link");
		add_to_jquery("
		// preview syntax
		var form = $('#".$options['form_name']."');
		$('#tab-prw-".$input_id."Preview').bind('click',function(){
		var text = $('#".$input_id."').val();
		var format = '".($options['bbcode'] ? 'bbcode' : 'html')."';
		var data = {
			'text' : text,
			'editor' : format
		};
		var sendData = form.serialize() + '&' + $.param(data);
		$.ajax({
			url: '".INCLUDES."dynamics/assets/preview/preview.ajax.php',
			type: 'POST',
			dataType: 'html',
			data : sendData,
			success: function(result){
			console.log(result);
			$('#prw-".$input_id."Preview').html(result);
			},
			error: function(result) {
				new PNotify({
					title: '".$locale['error_preview']."',
					text: '".$locale['error_preview_text']."',
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
	if ($options['autosize']) {
		add_to_jquery("
		$('#".$input_id."').autosize();
		");
	}
	$html .= "<div id='$input_id-help'></div>";
	$html .= $options['inline'] ? "</div>\n" : '';
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=textarea],[title=$title2],[id=$input_id],[required=".$options['required']."],[safemode=".$options['safemode']."]".($options['error_text'] ? ",[error_text=".$options['error_text']."]" : '')."' readonly />";
	return $html;
}

?>
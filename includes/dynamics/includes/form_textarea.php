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

function form_textarea($input_name, $label = '', $input_value = '', array $options = array()) {
	global $locale, $defender, $userdata; // for editor

	require_once INCLUDES."bbcode_include.php";
	require_once INCLUDES."html_buttons_include.php";
	include_once LOCALE.LOCALESET."admin/html_buttons.php";
	include_once LOCALE.LOCALESET."error.php";

	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";

	$options = array(
		'input_id'	=> !empty($options['input_id']) ? $options['input_id'] : $input_name,
		'required' => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
		'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
		'deactivate' => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '',
		'width' => !empty($options['width']) ? $options['width']  : '100%',
		'height' => !empty($options['height']) ? $options['height']  : '80px',
		'class' => !empty($options['class']) ? $options['class']  : '',
		'inline' => !empty($options['inline']) && $options['inline'] == 1 ?  '1'  : '0',
		'length' => !empty($options['length']) ? $options['length'] : '200',
		'error_text' => !empty($options['error_text']) ? $options['error_text']  : '',
		'safemode' => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
		'form_name' => !empty($options['form_name']) ? $options['form_name']  : 'input_form',
		'bbcode' => !empty($options['bbcode']) && $options['bbcode'] == 1 ?  '1' : '0',
		'html' => !empty($options['html']) && $options['html'] == 1 ? '1' : '0',
		'no_resize' => !empty($options['no_resize']) && $options['no_resize'] == '1' ? '1' : '0',
		'autosize' => !empty($options['autosize']) && $options['autosize'] == 1 ? '1' : '0',
		'preview' => !empty($options['preview']) && $options['preview'] == 1 ? '1' : '0',
		'path' => !empty($options['path']) && $options['path'] ? $options['path'] : IMAGES,
		'maxlength' => !empty($options['maxlength']) && isnum($options['maxlength']) ? $options['maxlength'] : '',
		'tip' => !empty($options['tip']) ? $options['tip'] : '',
	);
	if (!defined('autogrow') && $options['autosize']) {
		define('autogrow', true);
		add_to_footer("<script src='".DYNAMICS."assets/autosize/jquery.autosize.min.js'></script>");
	}
	if ($input_value !=='') {
		$input_value = html_entity_decode(stripslashes($input_value));
		$input_value = str_replace("<br />", "", $input_value);
	}

	$html = "<div id='".$options['input_id']."-field' class='form-group ".$options['class']."' ".($options['inline'] && $options['width'] && !$label ? "style='width: ".$options['width']." !important;'" : '').">\n";
	$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='".$options['input_id']."'>$label ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$tab_active = 0; $tab_title = array();
	if ($options['preview'] && $options['bbcode'] || $options['html']) {
		$tab_title['title'][] = $locale['preview'];
		$tab_title['id'][] = "prw-".$options['input_id'];
		$tab_title['icon'][] = '';
		$tab_title['title'][] = $locale['texts'];
		$tab_title['id'][] = "txt-".$options['input_id'];
		$tab_title['icon'][] = '';
		$tab_active = tab_active($tab_title, 1);
		$html .= opentab($tab_title, $tab_active, "".$options['input_id']."-link", '', 'editor-wrapper');
		$html .= opentabbody($tab_title['title'][1], "txt-".$options['input_id'], $tab_active);
	}

	$html .= ($options['bbcode'] || $options['html']) ? "<div class='panel panel-default m-b-0' ".($options['preview'] ? "style='border-top:0 !important; border-radius:0 !important;'" : '').">\n<div class='panel-heading clearfix' style='padding-bottom:0 !important;'>\n" : '';
	if ($options['bbcode'] && $options['form_name']) {
		$html .= display_bbcodes('90%', $input_name, $options['form_name']);
	} elseif ($options['html'] && $options['form_name']) {
		$html .= display_html($options['form_name'], $input_name, TRUE, TRUE, TRUE, $options['path']);
	}
	$html .= ($options['bbcode'] || $options['html']) ? "</div>\n<div class='panel-body p-0'>\n" : '';
	$html .= "<textarea name='$input_name' style='width:100%; height:".$options['height']."; ".($options['no_resize'] ? 'resize: none;' : '')."' class='form-control p-15 m-0 ".$options['class']." ".($options['autosize'] ? 'animated-height' : '')." ".($options['bbcode'] || $options['html'] ? "no-shadow no-border" : '')." textbox ' placeholder='".$options['placeholder']."' id='".$options['input_id']."' ".($options['deactivate'] ? 'readonly' : '').($options['maxlength'] ? "maxlength='".$options['maxlength']."'" : '').">".$input_value."</textarea>\n";

	if ($options['bbcode'] || $options['html']) {
		$html .= "</div>\n<div class='panel-footer'>\n";
		$html .= "<small>".$locale['word_count'].": <span id='".$options['input_id']."-wordcount'></span></small>";
		add_to_jquery("
		var init_str = $('#".$options['input_id']."').val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
		$('#".$options['input_id']."-wordcount').text(init_str);
		$('#".$options['input_id']."').on('input propertychange paste', function() {
		var str = $(this).val().replace(/<[^>]+>/ig, '').replace(/\\n/g,'').replace(/ /g, '').length;
		$('#".$options['input_id']."-wordcount').text(str);
		});
		");
		$html .= "</div>\n</div>\n";
	}

	if ($options['preview'] && $options['bbcode'] || $options['html']) {
		$html .= closetabbody();
		$html .= opentabbody($tab_title['title'][0], "prw-".$options['input_id']."", $tab_active);
		$html .= "No Result";
		$html .= closetabbody();
		$html .= closetab($tab_title, $tab_active, "".$options['input_id']."-link");

		add_to_jquery("
		// preview syntax
		var form = $('#".$options['form_name']."');
		$('#tab-".$options['input_id']."-linkPreview').bind('click',function(){
		var text = $('#".$options['input_id']."').val();
		var format = '".($options['bbcode'] ? 'bbcode' : 'html')."';
		var data = {
			".(defined('ADMIN_PANEL') ? "'mode': 'admin', " : "")."
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
			$('#prw-".$options['input_id']."Preview').html(result);
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
		$('#".$options['input_id']."').autosize();
		");
	}
	$html .= "<div id='".$options['input_id']."-help'></div>";
	$html .= $options['inline'] ? "</div>\n" : '';
	$html .= "</div>\n";
	$defender->add_field_session(array(
			 'input_name' 	=> 	$input_name,
			 'type'			=>	'textarea',
			 'title'		=>	$label,
			 'id' 			=>	$options['input_id'],
			 'required'		=>	$options['required'],
			 'safemode' 	=> 	$options['safemode'],
			 'error_text'	=> 	$options['error_text']
		 ));


	return $html;
}

?>
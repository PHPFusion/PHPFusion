<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System Version 8
| Copyright (C) 2002 - 2013 Nick Jones
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Textarea Input Based & Editor
| Filename: form_textarea.php
| Author: Frederick MC Chan (Hien)
| Version : 8.5.3 (Please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
/* @ to do
 * a. to load up current loaded bbcode and enable it with return editor function.
 * b. to parse using maincore loader.

 */
function form_textarea($title = FALSE, $input_name, $input_id, $input_value = FALSE, $array = FALSE) {
	global $userdata; // for editor
	$title2 = (isset($title) && (!empty($title))) ? stripinput($title) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
	$input_id = (isset($input_id) && (!empty($input_id))) ? stripinput($input_id) : "";
	if (!is_array($array)) {
		$required = 0;
		$safemode = 0;
		$deactivate = "";
		$width = "100%";
		$height = "80px";
		$editor = 0;
		$placeholder = "";
		$inline = '';
		$form_name = 'input_form';
		$bbcode = 0;
		$error_text = '';
		$class = '';
	} else {
		$required = (array_key_exists('required', $array) && ($array['required'] == 1)) ? 1 : 0;
		$safemode = (array_key_exists('safemode', $array) && ($array['safemode'] == 1)) ? 1 : 0;
		$placeholder = (array_key_exists('placeholder', $array)) ? $array['placeholder'] : "";
		$deactivate = (array_key_exists('deactivate', $array)) ? $array['deactivate'] : "";
		$bbcode = (array_key_exists('bbcode', $array) && $array['bbcode'] == 1) ? 1 : 0;
		$editor = (array_key_exists('editor', $array)) ? $array['editor'] : "";
		$width = (array_key_exists('width', $array)) ? $array['width'] : "100%";
		$height = (array_key_exists('height', $array)) ? $array['height'] : "80";
		$inline = (array_key_exists("inline", $array)) ? 1 : 0;
		$form_name = (array_key_exists('form_name', $array)) ? $array['form_name'] : 'input_form';
		$error_text = (array_key_exists("error_text", $array)) ? $array['error_text'] : "";
		$class = (array_key_exists("class", $array) && $array['class']) ? $array['class'] : '';
	}
	$input_value = phpentities(stripslashes($input_value));
	$input_value = str_replace("<br />", "", $input_value);
	if ($bbcode) {
		require_once INCLUDES."bbcode_include.php";
	}
	$html = "";
	$html .= "<div id='$input_id-field' class='form-group m-b-0 ".$class."'>\n";
	$html .= ($title) ? "<label class='control-label ".($inline ? "col-xs-12 col-sm-3 col-md-3 col-lg-3" : '')."' for='$input_id'>$title ".($required == 1 ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($inline) ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
	$html .= ($bbcode) ? "".display_bbcodes('90%', $input_name, $form_name)."" : '';
	$html .= "<textarea name='$input_name' style='width:100%; min-height:100px;' class='form-control textbox' placeholder='$placeholder' id='$input_id' ".($deactivate == "1" && (isnum($deactivate)) ? "readonly" : "").">$input_value</textarea>\n";
	$html .= "<div id='$input_id-help' class='display-inline-block'></div>";
	$html .= ($inline) ? "</div>\n" : "";
	$html .= "</div>\n";
	$html .= "<input type='hidden' name='def[$input_name]' value='[type=textarea],[title=$title2],[id=$input_id],[required=$required],[safemode=$safemode]".($error_text ? ",[error_text=$error_text]" : '')."' readonly />";
	// Editor Parse Configurations.
	if ($editor) {
		$result = dbquery("SELECT * FROM ".DB_EDITOR." WHERE ".groupaccess('editor_access')." AND editor_enable='1' ORDER BY editor_access DESC LIMIT 1");
		if (dbrows($result) > 0) {
			$data = dbarray($result);
			if (!defined('text_editor_on')) {
				define('text_editor_on', TRUE);
				// load editor js
				add_to_head("<script src='".DYNAMICS."assets/editor/development/jquery.sceditor.bbcode.js'></script>\n");
				// load editor theme
				$editor_theme = DYNAMICS."assets/editor/development/themes/css/monocons.css";
				if (file_exists(DYNAMICS."assets/editor/development/themes/css/".$data['editor_theme']."")) {
					$editor_theme = DYNAMICS."assets/editor/development/themes/css/".$data['editor_theme'];
				}
				add_to_head("<link rel='stylesheet' href='$editor_theme' type='text/css' media='all' />\n");
				// load smileys;
				$editor_smiley = cache_editor_smiley();
				// load editor bbcode settings;
				$editor_config = get_editor_buttons($data['editor_name']);
				// initialize the editor
				add_to_jquery("
                        $('#$input_id').sceditor({
                        plugins: 'bbcode',
                        id: '$input_id-editor',
                        height : '300',
                        width : '$width',
                        //toolbar: '$editor_config',
                        autoUpdate: true,
                        style: '$editor_theme',
                        $editor_smiley
                        });
                    ");
			}
			/*
			 * width: '720',
			 * height: '300'
			 *                         resizeEnabled: false,
			 */
		}
	} // endif editor config
	// Api for Adding Custom BBCodes - Example
	/*
	add_to_jquery("
	   $.sceditor.plugins.bbcode.bbcode.set('spoiler', {
			tags: {
				'div': {
					'class': ['spoiler']
				}
			},
			format: '[spoiler]{0}[/spoiler]',
			html: '<div class=\"spoiler\">{0}</div>'
	   });
		");
	*/
	return $html;
}

// Prepare the Editor Settings for Callbacks
function get_editor_buttons($mode) {
	require_once INCLUDES."bbcodes/bbcode.inc.php";
	$cache = cache_bbcode();
	$enabled_bbcodes = array();
	$editor_settings = array();
	foreach ($cache as $arr => $bbdata) {
		$enabled_bbcodes[] = $bbdata['name'];
		$editor_settings[$bbdata['name']] = $bbdata;
	}
	$editor_config = "";
	foreach (editor_features() as $feature) {
		if (in_array($feature, $enabled_bbcodes)) {
			$edata = $editor_settings[$feature];
			if ($edata[$mode] == '1') {
				if (file_exists(LOCALE.LOCALESET."bbcodes/$feature.php")) { // varpath is infusions/bbcode_infusion/locale/bbcodes/ - this is locale
					include(LOCALE.LOCALESET."bbcodes/$feature.php"); // load the locale
				} elseif (file_exists(LOCALE."English/bbcodes/".$feature.".php")) {
					include(LOCALE."English/bbcodes/".$feature.".php"); // load the default locale
				}
				include(INCLUDES."bbcodes/bbcode_libs/$feature/$feature.bbcode.var.php");
				$bbcode = $__BBCODE__['0'];
				if (array_key_exists("editor", $bbcode)) {
					$editor_config .= $bbcode['editor'];
				}
				unset($__BBCODE__);
			}
		}
	}
	$editor_config .= "|source,maximize|";
	return $editor_config;
}

// Automates Editor Output
function load_editor_settings($user_level) {
	require_once INCLUDES."bbcodes/bbcode.inc.php";
	$cache = cache_bbcode();
	$enabled_bbcodes = array();
	$editor_settings = array();
	foreach ($cache as $arr => $bbdata) {
		$enabled_bbcodes[] = $bbdata['name'];
		$editor_settings[$bbdata['name']] = $bbdata;
	}
	$editor_config = "";
	// Auto Determine Mode.
	$mode = return_editor_access($user_level);
	foreach (editor_features() as $feature) {
		if (in_array($feature, $enabled_bbcodes)) {
			$edata = $editor_settings[$feature];
			if ($edata[$mode] == 1) {
				if (file_exists(LOCALE.LOCALESET."bbcodes/$feature.php")) { // varpath is infusions/bbcode_infusion/locale/bbcodes/ - this is locale
					include(LOCALE.LOCALESET."bbcodes/$feature.php"); // load the locale
				} elseif (file_exists(LOCALE."English/bbcodes/".$feature.".php")) {
					include(LOCALE."English/bbcodes/".$feature.".php"); // load the default locale
				}
				include(INCLUDES."bbcodes/bbcode_libs/$feature/$feature.bbcode.var.php");
				$bbcode = $__BBCODE__['0'];
				if (array_key_exists("editor", $bbcode)) {
					$editor_config .= $bbcode['editor'];
				}
				unset($__BBCODE__);
			}
		}
	}
	$editor_config .= "|source,maximize|";
	return $editor_config;
}

// Cache Smileys for the Editor
function cache_editor_smiley() {
	$result = dbcount("(bbcode_id)", DB_BBCODES, "bbcode_name='smiley'");
	if ($result) {
		$path = INCLUDES."bbcodes/bbcode_libs/smiley/smiley.inc.php";
		if (file_exists($path)) {
			require_once $path;
			$data = cache_smileys();
			$counter = count(cache_smileys());
			$show_limit = ($counter <= 50) ? $counter : 50;
			$i = 0;
			$threshold = 30;
			$more_threshold = 60;
			foreach ($data as $emoticons) {
				if ($emoticons['smiley_placement'] == 0 && $i <= $threshold) {
					$smiley[] = "'".$emoticons['smiley_code']."' : '".$emoticons['smiley_image']."'";
				} elseif ($emoticons['smiley_placement'] == 1 || $i > $threshold && $i <= $more_threshold) {
					$smiley_more[] = "'".$emoticons['smiley_code']."' : '".$emoticons['smiley_image']."'";
				} elseif ($emoticons['smiley_placement'] == 2 || $i >= $more_threshold) {
					$smiley_hidden[] = "'".$emoticons['smiley_code']."' : '".$emoticons['smiley_image']."'";
				}
				$i++; // to ensure only 30 on main, 60 on more and the rest is hidden.
			}
		}
	}
	$smiley_ = "";
	$smiley_more_ = "";
	$smiley_hidden_ = "";
	if (isset($smiley)) {
		$smiley_ = "";
		foreach ($smiley as $arr => $text) {
			$smiley_ .= ($arr == count($smiley)-1) ? "$text" : "$text,";
		}
	}
	if (isset($smiley_more)) {
		$smiley_more_ = "";
		foreach ($smiley_more as $arr => $text) {
			$smiley_more_ .= ($arr == count($smiley)-1) ? "$text" : "$text,";
		}
	}
	if (isset($smiley_hidden)) {
		$smiley_hidden_ = "";
		foreach ($smiley_hidden as $arr => $text) {
			$smiley_hidden .= ($arr == count($smiley)-1) ? "$text" : "$text,";
		}
	}
	return "
    emoticonsRoot : '".IMAGES."emotes/',
    emoticons: {
        dropdown: {
        $smiley_
        },
        more: {
        $smiley_more_
        },
        hidden: {
        $smiley_hidden_
        }
    }
    ";
}

?>
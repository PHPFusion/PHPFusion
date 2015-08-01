<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_select.php
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
/**
 * Note on Tags Support
 * $options['tags'] = default $input_value must not be multidimensional array but only as $value = array(TRUE,'2','3');
 * For tagging - set tags and multiple to TRUE
 * @param       $label
 * @param       $input_name
 * @param       $options ['input_id']
 * @param array $option_array
 * @param bool  $input_value
 * @param array $options
 * @return string
 */
function form_select($input_name, $label = "", $input_value, array $options = array()) {
	global $defender, $locale;
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$default_options = array('options' => array(),
		'required' => FALSE,
		'regex' => '',
		'input_id' => $input_name,
		'placeholder' => $locale['choose'],
		'deactivate' => FALSE,
		'safemode' => FALSE,
		'allowclear' => FALSE,
		'multiple' => FALSE,
		'width' => '250px',
		'keyflip' => FALSE,
		'tags' => FALSE,
		'jsonmode' => FALSE,
		'chainable' => FALSE,
		'maxselect' => FALSE,
		'error_text' => '',
		'class' => '',
		'inline' => FALSE,
		'tip' => '',
		'delimiter' => ',',
		'callback_check' => '',);
	$options += $default_options;
	if (empty($options['options'])) {
		$options['options'] = array('0' => $locale['no_opts']);
		$options['deactivate'] = 1;
	}
	if (!$options['width']) {
		$options['width'] = $default_options['width'];
	}
	if ($options['multiple']) {
		if ($input_value) {
			$input_value = construct_array($input_value, 0, $options['delimiter']);
		} else {
			$input_value = array();
		}
	}
	$allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true," : '';
	$error_class = $defender->inputHasError($input_name) ? "has-error " : "";
	$html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']."' ".($options['width'] && !$label ? "style='width: ".$options['width']." !important;'" : '').">\n";
	$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' label=\"".$options['tip']."\"></i>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 p-l-0")."'>\n" : "";
	if ($options['jsonmode'] || $options['tags']) {
		// json mode.
		$html .= "<div id='".$options['input_id']."-spinner' style='display:none;'>\n<img src='".IMAGES."loader.gif'>\n</div>\n";
		$html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' style='width: ".($options['width'] && $label ? $options['width'] : $default_options['width'])."'/>\n";
	} else {
		// normal mode
		$html .= "<select name='$input_name' id='".$options['input_id']."' style='width: ".($options['width'] ? $options['width'] : $default_options['width'])."' ".($options['deactivate'] ? " disabled" : "").($options['multiple'] ? " multiple" : "").">";
		$html .= ($options['allowclear']) ? "<option value=''></option>" : '';
		if (is_array($options['options'])) {
			foreach ($options['options'] as $arr => $v) { // outputs: key, value, class - in order
				$chain = '';
				$select = '';
				if ($options['keyflip']) { // flip mode = store array values
					$chain = $options['chainable'] ? "class='$v'" : '';
					if ($input_value !== '') {
						$select = ($input_value == $v) ? "selected" : "";
					}
					$html .= "<option value='$v' ".$chain." ".$select.">".$v."</option>";
				} else { // normal mode = store array keys
					$chain = ($options['chainable']) ? "class='$arr'" : '';
					$select = '';
					if ($input_value !== '') {
						$input_value = stripinput($input_value); // not sure if can turn FALSE to zero not null.
						$select = (isset($input_value) && $input_value == $arr) ? 'selected' : '';
					}
					$html .= "<option value='$arr' ".$chain." ".$select.">$v</option>";
				}
				unset($arr);
			} // end foreach
		}
		$html .= "</select>\n";
	}
	$html .= "<div id='".$options['input_id']."-help'></div>";
	$html .= ($options['inline']) ? "</div>\n" : '';
	$html .= "</div>\n";
	if ($options['required']) {
		$html .= "<input class='req' id='dummy-".$options['input_id']."' type='hidden'>\n"; // for jscheck
	}
	// Generate Defender Tag
	$input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;
	$defender->add_field_session(array('input_name' => $input_name,
									 'title' => trim($title, '[]'),
									 'id' => $options['input_id'],
									 'type' => 'dropdown',
									 'regex' => $options['regex'],
									 'required' => $options['required'],
									 'safemode' => $options['safemode'],
									 'error_text' => $options['error_text'],
									 'callback_check' => $options['callback_check']));
	// Initialize Select2
	// Select 2 Multiple requires hidden DOM.
	if ($options['jsonmode'] == FALSE) {
		// not json mode (normal)
		$max_js = '';
		if ($options['multiple'] && $options['maxselect']) {
			$max_js = "maximumSelectionSize : ".$options['maxselect'].",";
		}
		$tag_js = '';
		if ($options['tags']) {
			$tag_value = json_encode($options['options']);
			$tag_js = ($tag_value) ? "tags: $tag_value" : "tags: []";
		}
		if ($options['required']) {
			add_to_jquery("
			var init_value = $('#".$options['input_id']."').select2('val');
			if (init_value) { $('dummy-".$options['input_id']."').val(init_value);	} else { $('dummy-".$options['input_id']."').val('');	}
			$('#".$options['input_id']."').select2({
				".($options['placeholder'] ? "placeholder: '".$options['placeholder']."'," : '')."
				".$max_js."
				".$allowclear."
				".$tag_js."
			}).bind('change', function(e) {	$('#dummy-".$options['input_id']."').val($(this).val()); });
			");
		} else {
			add_to_jquery("
			$('#".$options['input_id']."').select2({
				".($options['placeholder'] ? "placeholder: '".$options['placeholder']."'," : '')."
				".$max_js."
				".$allowclear."
				".$tag_js."
			});
			");
		}
	} else {
		// json mode
		add_to_jquery("
                var this_data = [{id:0, text: '".$options['placeholder']."'}];
                $('#".$options['input_id']."').select2({
                placeholder: '".$options['placeholder']."',
                data: this_data
                });
            ");
	}
	// For Multiple Callback.
	if (is_array($input_value) && $options['multiple']) { // stores as value;
		$vals = '';
		foreach ($input_value as $arr => $val) {
			$vals .= ($arr == count($input_value)-1) ? "'$val'" : "'$val',";
		}
		add_to_jquery("$('#".$options['input_id']."').select2('val', [$vals]);");
		// For Tags */
		/* foreach ($input_value as $id => $text) {
			$select_array[] = $keyflip ? array('id' => "$text", 'text' => "$text") : array('id' => "$id", 'text' => "$text");
		}
		if (!isset($select_array)) {
			$select_array = array();
		}
		$encoded = json_encode($select_array);
		add_to_jquery("$('#".$options['input_id']."').select2('data', $encoded);"); */
	}
	// alert('Selected value is '+$('#".$options['input_id']."').select2('val'));
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
	}
	return $html;
}

function form_user_select($input_name, $label = "", $input_value = FALSE, array $options = array()) {
	global $locale, $defender;
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$default_options = array('required' => FALSE,
		'regex' => '',
		'input_id' => $input_name,
		'placeholder' => $locale['sel_user'],
		'deactivate' => FALSE,
		'safemode' => FALSE,
		'allowclear' => FALSE,
		'multiple' => FALSE,
		'width' => '250px',
		'keyflip' => FALSE,
		'tags' => FALSE,
		'jsonmode' => FALSE,
		'chainable' => FALSE,
		'maxselect' => 1,
		'error_text' => '',
		'class' => '',
		'inline' => FALSE,
		'tip' => '',
		'delimiter' => ',',
		'callback_check' => '',
		'file' => '',);
	$options += $default_options;
	if (!$options['width']) {
		$options['width'] = $default_options['width'];
	}
	$allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true" : '';
	$length = "minimumInputLength: 1,";
	$error_class = $defender->inputHasError($input_name) ? "has-error " : "";
	$html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']."'>\n";
	$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 p-l-0" : '')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."</label>\n" : '';
	$html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9" : "col-sm-12")." p-l-0'>\n" : "";
	$html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width:100%;' ".($options['deactivate'] ? 'disabled' : '')." />";
	if ($options['deactivate']) {
		$html .= form_hidden($input_name, "", $input_value, array("input_id" => $options['input_id']));
	}
	$html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
	$html .= $options['inline'] ? "</div>\n" : '';
	$html .= "</div>\n";
	$path = $options['file'] ? $options['file'] : INCLUDES."search/users.json.php";
	if (!empty($input_value)) {
		// json mode.
		$encoded = $options['file'] ? $options['file'] : user_search($input_value);
	} else {
		$encoded = json_encode(array());
	}
	$defender->add_field_session(array('input_name' => $input_name,
									 'title' => $title,
									 'id' => $options['input_id'],
									 'type' => 'dropdown',
									 'required' => $options['required'],
									 'safemode' => $options['safemode'],
									 'error_text' => $options['error_text']));
	add_to_jquery("
                function avatar(item) {
                    if(!item.id) {return item.text;}
                    var avatar = item.avatar;
                    var level = item.level;
                    return '<table><tr><td style=\"\"><img style=\"height:25px;\" class=\"img-rounded\" src=\"".IMAGES."avatars/' + avatar + '\"/></td><td style=\"padding-left:10px; padding-right:10px;\"><div><strong>' + item.text + '</strong></div>' + level + '</div></td></tr></table>';
                }

                $('#".$options['input_id']."').select2({
                $length
                multiple: true,
                maximumSelectionSize: ".$options['maxselect'].",
                placeholder: '".$options['placeholder']."',
                ajax: {
                url: '$path',
                dataType: 'json',
                data: function (term, page) {
                        return {q: term};
                      },
                      results: function (data, page) {
                      	console.log(page);
                        return {results: data};
                      }
                },
                formatSelection: avatar,
                escapeMarkup: function(m) { return m; },
                formatResult: avatar,
                ".$allowclear."
                })".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
            ");
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />");
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js'></script>");
	}
	return $html;
}

/* Returns Json Encoded Object used in form_select_user */
function user_search($user_id) {
	$encoded = json_encode(array());
	$user_id = stripinput($user_id);
	$result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM ".DB_USERS." WHERE user_status='0' AND user_id='$user_id'");
	if (dbrows($result) > 0) {
		while ($udata = dbarray($result)) {
			$user_id = $udata['user_id'];
			$user_avatar = ($udata['user_avatar']) ? $udata['user_avatar'] : "noavatar50.png";
			$user_name = $udata['user_name'];
			$user_level = getuserlevel($udata['user_level']);
			$user_opts[] = array('id' => "$user_id",
				'text' => "$user_name",
				'avatar' => "$user_avatar",
				"level" => "$user_level");
		}
		if (!isset($user_opts)) {
			$user_opts = array();
		}
		$encoded = json_encode($user_opts);
	}
	return $encoded;
}

// Returns a full hierarchy nested dropdown.
function form_select_tree($input_name, $label = "", $input_value = FALSE, array $options = array(), $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = FALSE, $data = FALSE) {
	global $defender, $locale;
	$title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
	$default_options = array('required' => FALSE,
		'regex' => '',
		'input_id' => $input_name,
		'placeholder' => $locale['choose'],
		'deactivate' => FALSE,
		'safemode' => FALSE,
		'allowclear' => FALSE,
		'multiple' => FALSE,
		'width' => '250px',
		'keyflip' => FALSE,
		'tags' => FALSE,
		'jsonmode' => FALSE,
		'chainable' => FALSE,
		'maxselect' => FALSE,
		'error_text' => '',
		'class' => '',
		'inline' => FALSE,
		'tip' => '',
		'delimiter' => ',',
		'callback_check' => '',
		'file' => '',
		'parent_value' => $locale['root'],
		'add_parent_opts' => FALSE,
		'disable_opts' => '',
		'hide_disabled' => FALSE,
		'no_root' => FALSE,
		'show_current' => FALSE,
		'query' => '',);
	$options += $default_options;
	if (!$options['width']) {
		$options['width'] = $default_options['width'];
	}
	$allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true" : '';
	$multiple = $options['multiple'] ? 'multiple' : '';
	$disable_opts = '';
	if ($options['disable_opts']) {
		$disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
	}
	/* Child patern */
	$opt_pattern = str_repeat("&#8212;", $level);
	$error_class = $defender->inputHasError($input_name) ? "has-error " : "";
	if (!$level) {
		$level = 0;
		if (!isset($index[$id])) {
			$index[$id] = array('0' => $locale['no_opts']);
			//$options['deactivate'] = 1;
		}
		$html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class.$options['class']."' ".($options['inline'] && $options['width'] && !$label ? "style='width: ".$options['width']." !important;'" : '').">\n";
		$html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 p-l-0" : 'col-xs-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' label=\"".$options['tip']."\"></i>" : '')."</label>\n" : '';
		$html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")." p-l-0'>\n" : "";
	}
	if ($level == 0) {
		$html = & $html;
		add_to_jquery("
		$('#".$options['input_id']."').select2({
		placeholder: '".$options['placeholder']."',
		$allowclear
		});
		");
		$html .= "<select name='$input_name' style='".($options['width'] ? "width: ".$options['width']." " : 'min-width:250px;')."' id='".$options['input_id']."' class='".$options['class']."' ".($options['deactivate'] == 1 ? "disabled" : '')." $multiple>";
		$html .= $options['allowclear'] ? "<option value=''></option>" : '';
		if ($options['no_root'] !== 1) { // api options to remove root from selector. used in items creation.
			$this_select = '';
			if ($input_value !== NULL) {
				if ($input_value !== '') {
					$this_select = 'selected';
				}
			}
			$html .= ($options['add_parent_opts'] == 1) ? "<option value='0' ".$this_select.">$opt_pattern ".$locale['parent']."</option>\n" : "<option value='0' ".$this_select." >$opt_pattern ".$options['parent_value']."</option>\n";
		}
		$index = dbquery_tree($db, $id_col, $cat_col, $options['query']);
		$data = dbquery_tree_data($db, $id_col, $cat_col, $options['query']);
	}
	if (!$id) {
		$id = 0;
	}
	if (isset($index[$id])) {
		foreach ($index[$id] as $key => $value) {
			//$hide = $disable_branch && $value == $self_id ? 1 : 0;
			$html = & $html;
			$name = $data[$value][$name_col];
			$name = PHPFusion\QuantumFields::parse_label($name);
			$select = ($input_value !== "" && ($input_value == $value)) ? 'selected' : '';
			$disabled = $disable_opts && in_array($value, $disable_opts) ? TRUE : FALSE;
			$hide = $disabled && $options['hide_disabled'] ? TRUE : FALSE;
			// do a disable for filter_opts item.
			$html .= (!$hide) ? "<option value='$value' ".$select." ".($disable_opts && in_array($value, $disable_opts) ? 'disabled' : '')." >$opt_pattern $name ".($options['show_current'] && $self_id == $value ? '(Current Item)' : '')."</option>\n" : '';
			if (isset($index[$value]) && (!$hide)) {
				$html .= form_select_tree($input_name, $label, $input_value, $options, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level+TRUE, $index, $data);
			}
		}
	}
	if (!$level) {
		$html = & $html;
		$html .= "</select>";
		$html .= (($options['required'] == 1 && $defender->inputHasError($input_name)) || $defender->inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
		$html .= ($options['inline']) ? "</div>\n" : '';
		$html .= "</div>\n";
		if ($options['required']) {
			$html .= "<input class='req' id='dummy-".$options['input_id']."' type='hidden'>\n"; // for jscheck
		}
		$defender->add_field_session(array('input_name' => $input_name,
										 'title' => $title,
										 'id' => $options['input_id'],
										 'type' => 'dropdown',
										 'required' => $options['required'],
										 'safemode' => $options['safemode'],
										 'error_text' => $options['error_text']));
	}
	if (!defined("SELECT2")) {
		define("SELECT2", TRUE);
		add_to_footer("<script src='".DYNAMICS."assets/select2/select2.min.js' /></script>\n");
		add_to_head("<link href='".DYNAMICS."assets/select2/select2.css' rel='stylesheet' />\n");
	}
	return $html;
}


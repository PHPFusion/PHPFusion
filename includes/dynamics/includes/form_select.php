<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_select.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Takács Ákos (Rimelek)
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
 * Select2 dynamics plugin version 3.5 (stable)
 *
 * Note on Tags Support
 * $options['tags'] = default $input_value must not be multidimensional array but only as $value = array(TRUE,'2','3');
 * For tagging - set both tags and multiple to TRUE
 *
 * @param       $label
 * @param       $input_name
 * @param       $options ['input_id']
 * @param array $options
 * @param bool  $input_value
 * @param array $options
 *
 * @return string
 *
 * @package dynamics/select2
 */

function form_select($input_name, $label = "", $input_value, array $options = array()) {

    $locale = fusion_get_locale();

    $defender = \defender::getInstance();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $default_options = array(
        'options'        => array(),
        'required'       => FALSE,
        'regex'          => '',
        'input_id'       => $input_name,
        'placeholder'    => $locale['choose'],
        'deactivate'     => FALSE,
        'safemode'       => FALSE,
        'allowclear'     => FALSE,
        'multiple'       => FALSE,
        'width'          => '',
        'inner_width'    => '250px',
        'keyflip'        => FALSE,
        'tags'           => FALSE,
        'jsonmode'       => FALSE,
        'chainable'      => FALSE,
        'max_select'     => FALSE,
        'error_text'     => $locale['error_input_default'],
        'class'          => '',
        'inline'         => FALSE,
        'tip'            => '',
        'ext_tip'        => '',
        'delimiter'      => ',',
        'callback_check' => '',
        "stacked"        => "",
        'onchange'       => '',
    );

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
    // always trim id
    $options['input_id'] = trim($options['input_id'], "[]");
    $allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true," : '';

    $error_class = "";
    if ($defender->inputHasError($input_name)) {
        $error_class = "has-error ";
        if (!empty($options['error_text'])) {
            $new_error_text = $defender->getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
        }
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' ".($options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 p-l-0')."' for='".$options['input_id']."'>".$label.($options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '')."
	".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	</label>\n" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 p-l-0")."'>\n" : "";
    if ($options['jsonmode'] || $options['tags']) {
        // json mode.
        $html .= "<div id='".$options['input_id']."-spinner' style='display:none;'>\n<img src='".fusion_get_settings('siteurl')."images/loader.svg'>\n</div>\n";
        $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' style='width: ".($options['width'] ? $options['inner_width'] : $default_options['width'])."'/>\n";
    } else {
        // normal mode
        $html .= "<select name='$input_name' id='".$options['input_id']."' style='width: ".($options['inner_width'] ? $options['inner_width'] : $default_options['inner_width'])."'".($options['deactivate'] ? " disabled" : "").($options['onchange'] ? ' onchange="'.$options['onchange'].'"' : '').($options['multiple'] ? " multiple" : "").">\n";
        $html .= ($options['allowclear']) ? "<option value=''></option>\n" : '';
        /**
         * Have an array that looks like this in 'options' key
         * array('text' => 'Parent Text', 'children' => array(1 => 'Child A' , 2 => 'Child B'));
         */
        if (is_array($options['options'])) {
            foreach ($options['options'] as $arr => $v) { // outputs: key, value, class - in order
                $select = '';
                if (is_array($v) && isset($v['text'])) {
                    $html .= "<optgroup label='".$v['text']."'>\n";
                    if (isset($v['children'])) {
                        foreach ($v['children'] as $key => $v2) {
                            if ($options['keyflip']) { // flip mode = store array values
                                $chain = $options['chainable'] ? "class='$v2'" : '';
                                if ($input_value !== '') {
                                    $select = ($input_value == $v2) ? " selected" : "";
                                }
                                $html .= "<option value='$v2'".$chain.$select.">".$key."</option>\n";
                            } else { // normal mode = store array keys
                                $chain = ($options['chainable']) ? "class='$key'" : '';
                                $select = '';
                                if ($input_value !== '') {
                                    $input_value = stripinput($input_value); // not sure if can turn FALSE to zero not null.
                                    $select = (isset($input_value) && $input_value == $key) ? ' selected' : '';
                                }
                                $html .= "<option value='$key'".$chain.$select.">$v2</option>\n";
                            }
                            unset($key);
                        }
                    }
                    $html .= "</optgroup>\n";
                } else {
                    if ($options['keyflip']) { // flip mode = store array values
                        $chain = $options['chainable'] ? "class='$v'" : '';
                        if ($input_value !== '') {
                            $select = ($input_value == $v) ? " selected" : "";
                        }
                        $html .= "<option value='$v'".$chain.$select.">".$v."</option>\n";
                    } else { // normal mode = store array keys
                        $chain = ($options['chainable']) ? "class='$arr'" : '';
                        $select = '';
                        if ($input_value !== '') {
                            $input_value = stripinput($input_value); // not sure if can turn FALSE to zero not null.
                            $select = (isset($input_value) && $input_value == $arr) ? ' selected' : '';
                        }
                        $html .= "<option value='$arr'".$chain.$select.">$v</option>\n";
                    }
                    unset($arr);
                }
            }
        }
        $html .= "</select>\n";
    }
    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>".$options['ext_tip']."</i></div>" : "";
    $html .= $defender->inputHasError($input_name) && !$options['inline'] ? "<br/>" : "";
    $html .= $defender->inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= ($options['inline'] && $label) ? "</div>\n" : '';
    $html .= "</div>\n";
    if ($options['required']) {
        $html .= "<input class='req' id='dummy-".$options['input_id']."' type='hidden'>\n"; // for jscheck
    }
    // Generate Defender Tag
    $input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;
    $defender->add_field_session(array(
        'input_name'     => $input_name,
        'title'          => trim($title, '[]'),
        'id'             => $options['input_id'],
        'type'           => 'dropdown',
        'regex'          => $options['regex'],
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'error_text'     => $options['error_text'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
    ));
    // Initialize Select2
    // Select 2 Multiple requires hidden DOM.
    if ($options['jsonmode'] == FALSE) {
        // not json mode (normal)
        $max_js = '';
        if ($options['multiple'] && $options['max_select']) {
            $max_js = "maximumSelectionSize : ".$options['max_select'].",";
        }
        $tag_js = '';
        if ($options['tags']) {
            $tag_value = json_encode($options['options']);
            $tag_js = ($tag_value) ? "tags: $tag_value" : "tags: []";
        }
        if ($options['required']) {
            \PHPFusion\OutputHandler::addToJQuery("
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
            \PHPFusion\OutputHandler::addToJQuery("
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
        \PHPFusion\OutputHandler::addToJQuery("
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
            $vals .= ($arr == count($input_value) - 1) ? "'$val'" : "'$val',";
        }
        \PHPFusion\OutputHandler::addToJQuery("$('#".$options['input_id']."').select2('val', [$vals]);");
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
    return $html;
}

/**
 * Selector for registered user
 *
 * @param        $input_name
 * @param string $label
 * @param bool   $input_value - user id
 * @param array  $options
 *
 * @return string
 */
function form_user_select($input_name, $label = "", $input_value = FALSE, array $options = array()) {
    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $default_options = array(
        'required'       => FALSE,
        'regex'          => '',
        'input_id'       => $input_name,
        'placeholder'    => $locale['sel_user'],
        'deactivate'     => FALSE,
        'safemode'       => FALSE,
        'allowclear'     => FALSE,
        'multiple'       => FALSE,
        'inner_width'    => '250px',
        'width'          => '100%',
        'keyflip'        => FALSE,
        'tags'           => FALSE,
        'jsonmode'       => FALSE,
        'chainable'      => FALSE,
        'max_select'     => 1,
        'error_text'     => '',
        'class'          => '',
        'inline'         => FALSE,
        'tip'            => '',
        'ext_tip'        => '',
        'delimiter'      => ',',
        'callback_check' => '',
        'file'           => '',
        'allow_self'     => FALSE,
    );

    $options += $default_options;
    $options['input_id'] = trim($options['input_id'], "[]");
    $allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true," : '';
    $length = "minimumInputLength: 1,";
    $error_class = "";

    if (defender::inputHasError($input_name)) {
        $error_class = "has-error ";
        $new_error_text = defender::getErrorText($input_name);
        if (!empty($new_error_text)) {
            $options['error_text'] = $new_error_text;
        }
        addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
    }

    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' style='width:".$options['width']."'>\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? 'col-xs-12 col-sm-3 p-l-0' : 'col-xs-12 p-l-0')."' for='".$options['input_id']."'>$label ".($options['required'] == TRUE ? "<span class='required'>*</span>" : '')."</label>\n" : '';
    $html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9" : "col-sm-12")."'>\n" : "";
    $html .= "<input ".($options['required'] ? "class='req'" : '')." type='hidden' name='$input_name' id='".$options['input_id']."' data-placeholder='".$options['placeholder']."' style='width:".$options['inner_width']."'".($options['deactivate'] ? ' disabled' : '')."/>\n";
    if ($options['deactivate']) {
        $html .= form_hidden($input_name, '', $input_value, array("input_id" => $options['input_id']));
    }
    $html .= (defender::inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : '');
    $html .= $options['ext_tip'] ? "<br/>\n<div class='m-t-10 tip'><i>".$options['ext_tip']."</i></div>" : "";
    $html .= $options['inline'] ? "</div>\n" : '';

    $html .= "</div>\n";
    $root_prefix = fusion_get_settings("site_seo") == 1 ? fusion_get_settings('siteurl')."includes/" : INCLUDES;
    $root_img = fusion_get_settings("site_seo") == 1 ? fusion_get_settings('siteurl') : '';
    $path = $options['file'] ? $options['file'] : $root_prefix."dynamics/assets/users/users.json.php".($options['allow_self'] ? "?allow_self=true" : "");
    if (!empty($input_value)) {
        // json mode.
        $encoded = $options['file'] ? $options['file'] : user_search($input_value);
    } else {
        $encoded = json_encode(array());
    }
    defender::getInstance()->add_field_session(array(
        'input_name' => $input_name,
        'title'      => $title,
        'id'         => $options['input_id'],
        'type'       => 'dropdown',
        'required'   => $options['required'],
        'safemode'   => $options['safemode'],
        'error_text' => $options['error_text']
    ));
    \PHPFusion\OutputHandler::addToJQuery("
		function avatar(item) {
			if(!item.id) {return item.text;}
			var avatar = item.avatar;
			var level = item.level;
			return '<table><tr><td style=\"\"><img style=\"height:25px;\" class=\"img-rounded\" src=\"".$root_img.IMAGES."avatars/' + avatar + '\"/></td><td style=\"padding-left:10px; padding-right:10px;\"><div><strong>' + item.text + '</strong></div>' + level + '</div></td></tr></table>';
		}
		$('#".$options['input_id']."').select2({
		$length
		multiple: true,
		maximumSelectionSize: ".$options['max_select'].",
		placeholder: '".$options['placeholder']."',
		ajax: {
		url: '$path',
		dataType: 'json',
		data: function (term, page) {
				return {q: term};
			  },
			  results: function (data, page) {
				//console.log(page);
				return {results: data};
			  }
		},
		formatSelection: avatar,
		escapeMarkup: function(m) { return m; },
		formatResult: avatar,
		".$allowclear."
		})".(!empty($encoded) ? ".select2('data', $encoded );" : '')."
	");

    return $html;
}

/* Returns Json Encoded Object used in form_select_user */
function user_search($user_id) {
    $encoded = json_encode(array());
    $user_id = stripinput($user_id);
    $result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM ".DB_USERS." WHERE user_status=:status AND user_id=:id", [':status' => 0, ':id' => $user_id]);
    if (dbrows($result) > 0) {
        while ($udata = dbarray($result)) {
            $user_id = $udata['user_id'];
            $user_avatar = ($udata['user_avatar']) ? $udata['user_avatar'] : "noavatar50.png";
            $user_name = $udata['user_name'];
            $user_level = getuserlevel($udata['user_level']);
            $user_opts[] = array(
                'id'     => $user_id,
                'text'   => $user_name,
                'avatar' => $user_avatar,
                "level"  => $user_level
            );
        }
        if (!isset($user_opts)) {
            $user_opts = array();
        }
        $encoded = json_encode($user_opts);
    }

    return $encoded;
}

/**
 * Select2 hierarchy
 * Returns a full hierarchy nested dropdown.
 *
 * @param        $input_name
 * @param string $label
 * @param bool   $input_value
 * @param array  $options
 * @param        $db       - your db
 * @param        $name_col - the option text to show
 * @param        $id_col   - unique id
 * @param        $cat_col  - parent id
 *                         ## The rest of the Params are used by the function itself -- no need to handle ##
 * @param bool   $self_id  - not required
 * @param bool   $id       - not required
 * @param bool   $level    - not required
 * @param bool   $index    - not required
 * @param bool   $data     - not required
 *
 * @return string
 */
function form_select_tree($input_name, $label = "", $input_value = FALSE, array $options = array(), $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = FALSE, $data = FALSE) {
    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $default_options = array(
        'required'        => FALSE,
        'regex'           => '',
        'input_id'        => $input_name,
        'placeholder'     => $locale['choose'],
        'deactivate'      => FALSE,
        'safemode'        => FALSE,
        'allowclear'      => FALSE,
        'multiple'        => FALSE,
        'width'           => '',
        'inner_width'     => '250px',
        'keyflip'         => FALSE,
        'tags'            => FALSE,
        'jsonmode'        => FALSE,
        'chainable'       => FALSE,
        'max_select'      => FALSE,
        'error_text'      => $locale['error_input_default'],
        'class'           => '',
        'inline'          => FALSE,
        'tip'             => '',
        'delimiter'       => ',',
        'callback_check'  => '',
        'file'            => '',
        'parent_value'    => $locale['root'],
        'add_parent_opts' => FALSE,
        'disable_opts'    => '',
        'hide_disabled'   => FALSE,
        'no_root'         => FALSE,
        'show_current'    => FALSE,
        'query'           => '',
        'full_query'      => '',
    );
    $options += $default_options;
    $options['input_id'] = trim($options['input_id'], "[]");
    if ($options['multiple']) {
        if ($input_value) {
            $input_value = construct_array($input_value, 0, $options['delimiter']);
        } else {
            $input_value = array();
        }
    }
    if (!$options['width']) {
        $options['width'] = $default_options['width'];
    }
    $allowclear = ($options['placeholder'] && $options['multiple'] || $options['allowclear']) ? "allowClear:true" : '';
    $disable_opts = '';
    if ($options['disable_opts']) {
        $disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
    }
    /* Child patern */
    $opt_pattern = str_repeat("&#8212;", $level);

    if (!$level) {
        $level = 0;
        if (!isset($index[$id])) {
            $index[$id] = array('0' => $locale['no_opts']);
        }

        $error_class = '';
        if (\defender::inputHasError($input_name)) {
            $error_class = "has-error ";
            if (!empty($options['error_text'])) {
                $new_error_text = \defender::getErrorText($input_name);
                if (!empty($new_error_text)) {
                    $options['error_text'] = $new_error_text;
                }
                addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
            }
        }

        $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' ".($options['inline'] && $options['width'] && !$label ? "style='width: ".$options['width']."'" : '').">\n";
        $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 p-l-0" : 'col-xs-12 p-l-0')."' for='".$options['input_id']."'>".$label.($options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' label=".$options['tip']."></i>" : '')."</label>\n" : '';
        $html .= ($options['inline']) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12")."'>\n" : "";
    }
    if ($level == 0) {
        $html = &$html;
        add_to_jquery("
		$('#".$options['input_id']."').select2({
		placeholder: '".$options['placeholder']."',
		$allowclear
		});
		");
        if (is_array($input_value) && $options['multiple']) { // stores as value;
            $vals = '';
            foreach ($input_value as $arr => $val) {
                $vals .= ($arr == count($input_value) - 1) ? "'$val'" : "'$val',";
            }
            add_to_jquery("$('#".$options['input_id']."').select2('val', [$vals]);");
        }

        $html .= "<select name='$input_name' id='".$options['input_id']."' style='width: ".($options['inner_width'] ? $options['inner_width'] : $default_options['inner_width'])."'".($options['deactivate'] ? " disabled" : "").($options['multiple'] ? " multiple" : "").">";
        $html .= $options['allowclear'] ? "<option value=''></option>\n" : '';
        if ($options['no_root'] == FALSE) { // api options to remove root from selector. used in items creation.
            $this_select = '';
            if ($input_value !== NULL) {
                if ($input_value !== '') {
                    $this_select = 'selected';
                }
            }
            $html .= ($options['add_parent_opts'] == TRUE) ? "<option value='0' ".$this_select.">$opt_pattern ".$locale['parent']."</option>\n" : "<option value='0' ".$this_select." >$opt_pattern ".$options['parent_value']."</option>\n";
        }

        $index = dbquery_tree($db, $id_col, $cat_col, $options['query'], $options['full_query']);
        if (!empty($index)) {
            $data = dropdown_select($db, $id_col, $name_col, $cat_col, implode(',', flatten_array($index)), $options['query'], $options['full_query']);
        }
    }

    if (!$id) {
        $id = 0;
    }

    if (isset($index[$id]) && !empty($data)) {
        foreach ($index[$id] as $key => $value) {
            // value is the array
            //$hide = $disable_branch && $value == $self_id ? 1 : 0;
            $html = &$html;
            $name = $data[$value][$name_col];
            //print_p($data[$value]);

            $name = PHPFusion\QuantumFields::parse_label($name);
            $select = ($input_value !== "" && ($input_value == $value)) ? 'selected' : '';
            $disabled = $disable_opts && in_array($value, $disable_opts) ? TRUE : FALSE;
            $hide = $disabled && $options['hide_disabled'] ? TRUE : FALSE;
            // do a disable for filter_opts item.
            $html .= (!$hide) ? "<option value='$value' ".$select." ".($disable_opts && in_array($value, $disable_opts) ? 'disabled' : '')." >$opt_pattern $name ".($options['show_current'] && $self_id == $value ? '(Current Item)' : '')."</option>\n" : '';
            if (isset($index[$value]) && (!$hide)) {
                $html .= form_select_tree($input_name, $label, $input_value, $options, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level + TRUE, $index, $data);
            }
        }
    }
    if (!$level) {
        $html = &$html;
        $html .= "</select>\n";
        $html .= (($options['required'] == 1 && \defender::inputHasError($input_name)) || \defender::inputHasError($input_name)) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
        $html .= ($options['inline']) ? "</div>\n" : '';
        $html .= "</div>\n";
        if ($options['required']) {
            $html .= "<input class='req' id='dummy-".$options['input_id']."' type='hidden'>\n"; // for jscheck
        }
        $input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;
        \defender::add_field_session(
            array(
                'input_name'     => $input_name,
                'title'          => trim($title, '[]'),
                'id'             => $options['input_id'],
                'type'           => 'dropdown',
                'regex'          => $options['regex'],
                'required'       => $options['required'],
                'safemode'       => $options['safemode'],
                'error_text'     => $options['error_text'],
                'callback_check' => $options['callback_check'],
                'delimiter'      => $options['delimiter'],
            )
        );
    }

    return $html;
}

/*
 * Optimized performance by adding a self param to implode to fetch only certain rows
 */
function dropdown_select($db, $id_col, $name_col, $cat_col, $index_values, $filter = '', $query_replace = '') {
    $data = array();
    $query = "SELECT $id_col, $name_col, $cat_col FROM ".$db." ".($filter ? $filter." AND " : 'WHERE')." $id_col IN ($index_values) ORDER BY $name_col ASC";
    if (!empty($query_replace)) {
        $query = $query_replace;
    }
    $result = dbquery($query);
    while ($row = dbarray($result)) {
        $id = $row[$id_col];
        $data[$id] = $row;
    }

    return $data;
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_buttons.php
| Author: Frederick MC Chan (Chan)
| Co-Author: Tyler Hurlbut
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

function form_button($input_name, $title, $input_value, array $options = array()) {
    $html = "";

    $input_value = stripinput($input_value);

    $default_options = array(
        'input_id'    => $input_name,
        'input_value' => $input_name,
        'class'       => "btn-default",
        'icon'        => "",
        'deactivate'  => FALSE,
        'type'        => "submit",
        'block'       => FALSE,
        'alt'         => $title,
        'data'        => [],
    );

    $options = $options + $default_options;

    if ($options['block']) {
        $options['class'] = $options['class']." btn-block";
    }

    array_walk($options['data'], function ($a, $b) use (&$options_data) {
        $options_data[] = "data-$b='$a'";
    }, $options_data);

    if ($options['type'] == 'link') {
        $html .= "<a id='".$options['input_id']."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled ' : '')."btn ".$options['class']." button' href='".$input_name."' data-value='".$input_value."' ".(!empty($options_data) ? implode(' ', $options_data) : '').($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']." m-r-10'></i>" : '').$title."</a>";
    } elseif ($options['type'] == 'button') {
        $html .= "<button id='".$options['input_id']."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled ' : '')."btn ".$options['class']." button' name='".$input_name."' value='".$input_value."' type='button' ".(!empty($options_data) ? implode(' ', $options_data) : '').($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']." m-r-10'></i>" : '').$title."</button>\n";
    } else {
        $html .= "<button id='".$options['input_id']."' title='".$options['alt']."' class='".($options['deactivate'] ? 'disabled ' : '')."btn ".$options['class']." button' name='".$input_name."' value='".$input_value."' type='submit' ".(!empty($options_data) ? implode(' ', $options_data) : '').($options['deactivate'] ? "disabled='disabled'" : '')." >".($options['icon'] ? "<i class='".$options['icon']." m-r-10'></i>" : '').$title."</button>\n";
    }

    return $html;
}

/**
 * Button Groups
 * @param        $input_name
 * @param string $label
 * @param        $input_value
 * @param array  $options
 * @return string
 */
function form_btngroup($input_name, $label = "", $input_value, array $options = array()) {
    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_value = (isset($input_value) && (!empty($input_value))) ? stripinput($input_value) : "";


    $default_options = array(
        'options'        => array($locale['disable'], $locale['enable']),
        'input_id'       => $input_name,
        'class'          => "btn-default",
        'icon'           => "",
        "multiple"       => FALSE,
        "delimiter"      => ",",
        'deactivate'     => FALSE,
        'error_text'     => "",
        'inline'         => FALSE,
        'safemode'       => FALSE,
        'required'       => FALSE,
        'callback_check' => '',
    );

    $options += $default_options;

    $error_class = "";
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

    $html = "<div id='".$options['input_id']."-field' class='form-group ".$error_class." clearfix'>\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : 'col-xs-12 col-sm-12 col-md-12 col-lg-12 p-l-0')."' for='".$options['input_id']."'>".$label.($options['required'] == 1 ? "<span class='required'>&nbsp;*</span>" : '')."</label>\n" : '';
    $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 ".($label ? "col-sm-9 col-md-9 col-lg-9" : "col-sm-12 p-l-0")."'>\n" : "";
    $html .= "<div class='btn-group' id='".$options['input_id']."'>";
    $i = 1;
    if (!empty($options['options']) && is_array($options['options'])) {
        foreach ($options['options'] as $arr => $v) {
            $active = '';
            if ($input_value == $arr) {
                $active = "active";
            }
            $html .= "<button type='button' data-value='$arr' class='btn ".$options['class']." ".((count($options['options']) == $i ? 'last-child' : ''))." $active'>".$v."</button>\n";
            $i++;
        }
    }
    $html .= "</div>\n";
    $html .= "<input name='$input_name' type='hidden' id='".$options['input_id']."-text' value='$input_value' />\n";

    $html .= \defender::inputHasError($input_name) ? "<div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= $options['inline'] ? "</div>\n" : '';
    $html .= "</div>\n";

    $input_name = ($options['multiple']) ? str_replace("[]", "", $input_name) : $input_name;

    \defender::getInstance()->add_field_session(array(
                                     'input_name'     => $input_name,
                                     'title'          => trim($title, '[]'),
                                     'id'             => $options['input_id'],
                                     'type'           => 'dropdown',
                                     'required'       => $options['required'],
                                     'callback_check' => $options['callback_check'],
                                     'safemode'       => $options['safemode'],
                                     'error_text'     => $options['error_text'],
                                     'delimiter'      => $options['delimiter'],
                                 ));
    add_to_jquery("
	$('#".$options['input_id']." button').bind('click', function(e){
		$('#".$options['input_id']." button').removeClass('active');
		$(this).toggleClass('active');
		value = $(this).data('value');
		$('#".$options['input_id']."-text').val(value);
	});
	");

    return $html;
}

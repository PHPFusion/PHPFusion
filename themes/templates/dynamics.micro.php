<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: dynamics.micro.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

// Support file for compatibility between v8 and v9

if (!function_exists('openform')) {
    function openform($form_name, $method, $action_url, array $options = []) {
        $method = (strtolower($method) == 'post') ? 'post' : 'get';
        $default_options = [
            'form_id'   => $form_name,
            'class'     => '',
            'enctype'   => FALSE,
            'inline'    => FALSE,
            'on_submit' => '',
        ];
        $options += $default_options;

        $html = "<form name='".$form_name."' id='".$options['form_id']."' method='".$method."' action='".$action_url."' class='".($options['inline'] ? "form-inline " : '').($options['class'] ? $options['class'] : 'm-0')."'".($options['enctype'] ? " enctype='multipart/form-data'" : '').($options['on_submit'] ? " onSubmit='".$options['on_submit']."'" : '').">\n";

        return $html;
    }
}

if (!function_exists('closeform')) {
    function closeform() {
        return "</form>\n";
    }
}

if (!function_exists('form_text')) {
    function form_text($input_name, $label = '', $input_value = '', array $options = []) {
        $id = trim($input_name, "[]");

        $default_options = [
            'type'               => 'text',
            'required'           => FALSE,
            'label_icon'         => '',
            'feedback_icon'      => '',
            'safemode'           => FALSE,
            'regex'              => '',
            'regex_error_text'   => '',
            'callback_check'     => FALSE,
            'input_id'           => $id,
            'placeholder'        => '',
            'deactivate'         => FALSE,
            'width'              => '',
            'inner_width'        => '',
            'class'              => '',
            'inner_class'        => '',
            'inline'             => FALSE,
            'min_length'         => 1,
            'max_length'         => 200,
            'number_min'         => 0,
            'number_max'         => 0,
            'number_step'        => 1,
            'icon'               => '',
            'autocomplete_off'   => FALSE,
            'tip'                => '',
            'ext_tip'            => '',
            'append_button'      => '',
            'append_value'       => '',
            'append_form_value'  => '',
            'append_size'        => '',
            'append_class'       => 'btn-default',
            'append_type'        => 'submit',
            'prepend_id'         => "p-".$id."-prepend",
            'append_id'          => "p-".$id."-append",
            'prepend_button'     => '',
            'prepend_value'      => '',
            'prepend_form_value' => '',
            'prepend_size'       => '',
            'prepend_class'      => 'btn-default',
            'prepend_type'       => 'submit',
            'error_text'         => '',
            'delimiter'          => ',',
            'stacked'            => '',
            'group_size'         => '',
            'password_strength'  => FALSE,
            'data'               => [],
            'append_html'        => '',
            'censor_words'       => TRUE,

        ];

        $options += $default_options;

        $valid_types = [
            'text', 'number', 'password', 'email', 'url', 'color', 'date', 'datetime', 'datetime-local', 'month', 'range', 'search', 'tel', 'time', 'week'
        ];

        $options['type'] = in_array($options['type'], $valid_types) ? $options['type'] : 'text';

        $html = "<div id='".$options['input_id']."-field' class='form-group".($options['inline'] ? ' overflow-hide' : '').($options['class'] ? ' '.$options['class'] : '')."'>";
        $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
        $html .= ($options['inline'] && $label) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>\n" : "";
        $html .= "<input type='".$options['type']."' ".(!empty($options_data) ? implode(' ', $options_data) : '')." class='form-control textbox ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";
        $html .= ($options['inline'] && $label) ? "</div>\n" : "";
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('form_button')) {
    function form_button($input_name, $title, $input_value, array $options = []) {
        $input_value = stripinput($input_value);

        $default_options = [
            'input_id'    => $input_name,
            'input_value' => $input_name,
            'class'       => 'btn-default',
            'icon_class'  => 'm-r-10',
            'icon'        => '',
            'deactivate'  => FALSE,
            'type'        => 'submit',
            'block'       => FALSE,
            'alt'         => $title,
            'data'        => []
        ];

        $options = $options + $default_options;

        if ($options['block']) {
            $options['class'] = $options['class'].' btn-block';
        }

        $html = '<button type="'.(!empty($options['type']) ? $options['type'] : 'submit').'" id="'.$options['input_id'].'" name="'.$input_name.'" value="'.$input_value.'" class="button btn '.$options['class'].($options['deactivate'] ? ' disabled ' : '').'">'.($options['icon'] ? ' <i class="'.$options['icon'].' '.$options['icon_class'].'"></i>' : '').$title.'</button>';

        return $html;
    }
}

if (!function_exists('parse_textarea')) {
    function parse_textarea($text, $smileys = TRUE, $bbcode = TRUE, $decode = TRUE, $default_image_folder = IMAGES, $add_line_breaks = FALSE) {
        $text = $decode == TRUE ? html_entity_decode(stripslashes($text), ENT_QUOTES, fusion_get_locale('charset')) : $text;
        $text = $decode == TRUE ? html_entity_decode($text, ENT_QUOTES, fusion_get_locale('charset')) : $text; // decode for double encoding.
        $text = $smileys == TRUE ? parsesmileys($text) : $text;
        $text = $bbcode == TRUE ? parseubb($text) : $text;
        $text = fusion_parse_user($text);
        $text = $add_line_breaks ? nl2br($text) : $text;
        $text = descript($text);

        return (string)$text;
    }
}

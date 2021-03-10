<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: form_main.php
| Author: Frederick MC Chan (Chan)
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
 * @param string $form_name
 * @param string $method     - 'post' or 'get'
 * @param string $action_url - form current uri (defaults: FORM_REQUEST)
 * @param array  $options    :
 *                           form_id = default as form_name
 *                           class = default empty
 *                           enctype = true or false , set true to allow file upload
 *                           max_tokens = store into session number of tokens , default as 1.
 *
 * @return string
 */
function openform($form_name, $method, $action_url = FORM_REQUEST, array $options = []) {

    $method = (strtolower($method) == 'post') ? 'post' : 'get';

    $default_options = [
        'form_id'    => $form_name,
        'class'      => '',
        'enctype'    => FALSE,
        'max_tokens' => fusion_get_settings('form_tokens'),
        'inline'     => FALSE,
        'on_submit'  => '',
        'honeypot'   => TRUE,
    ];

    $options += $default_options;

    if (!$action_url) {
        $action_url = FORM_REQUEST;
    }

    $class = $options['class'];

    if (!fusion_safe()) {
        $class .= " warning";
    }

    $html = "<form name='".$form_name."' id='".$options['form_id']."' method='".$method."' action='".$action_url."' class='".($options['inline'] ? "form-inline " : '').($class ? $class : 'm-0')."'".($options['enctype'] ? " enctype='multipart/form-data'" : '').($options['on_submit'] ? " onSubmit='".$options['on_submit']."'" : '').">\n";

    if ($method == 'post') {
        $token = fusion_get_token($options['form_id'], $options['max_tokens']);
        $html .= "<input type='hidden' name='fusion_token' value='".$token."' />\n";
        $html .= "<input type='hidden' name='form_id' value='".$options['form_id']."' />\n";
        if ($options['honeypot']) {
            $input_name = 'fusion_'.random_string();
            $html .= "<input type='hidden' name='$input_name' value=''>\n";
            Defender::getInstance()->addHoneypot([
                'honeypot'   => $options['form_id'].'_honeypot',
                'input_name' => $input_name,
                'form_name'  => $form_name,
                'type'       => 'honeypot',
            ]);
        }
    }

    return (string)$html;
}

/**
 * @return string
 */
function closeform() {
    return (string)"</form>\n";
}

function clean_input_name($value) {
    $re = '/\[(.*?)\]/m';
    return preg_replace($re, '', $value);
}

function clean_input_value($value) {
    if (is_string($value)) {
        return descript($value);
    }
    if (is_array($value)) {
        return array_map('descript', $value);
    }
    return '';
}


function load_select2_script() {
    static $loaded = FALSE;
    if ($loaded === FALSE) {
        /**
         * @return string
         * @see load_select2_script()
         */
        function select2csspath() {
            return DYNAMICS."assets/select2/select2.css";
        }

        $select2_locale_path = DYNAMICS."assets/select2/select2_locale_".fusion_get_locale('select2').".js";
        fusion_load_script(DYNAMICS."assets/select2/select2.js");

        if (is_file($select2_locale_path)) {
            fusion_load_script($select2_locale_path);
        }

        /**
         * @uses  select2csspath()
         */
        fusion_add_hook("fusion_core_styles", "select2csspath");

        $loaded = TRUE;
    }
}

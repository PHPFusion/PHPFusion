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
 * The function should be able used to replace conventional <form> tags to provide an enhanced feature to your application.
 *
 * @param string                      $form_name  Form ID.
 * @param string                      $method     Possible value: post, get.
 * @param array|mixed|string|string[] $action_url Form current uri.
 * @param array                       $options
 *
 * @return string
 */
function openform($form_name, $method = 'post', string $action_url = FORM_REQUEST, array $options = []) {

    $method = (strtolower($method) == 'post') ? 'POST' : 'GET';

    $default_options = [
        'form_id'    => $form_name,
        'class'      => '', // CSS class properties.
        'enctype'    => FALSE, // Set true for allowing multipart.
        'max_tokens' => fusion_get_settings('form_tokens'),
        'inline'     => FALSE, // Set true for making form inline.
        'on_submit'  => '', // Adds javascript function on form submit.
        'honeypot'   => TRUE, // Enables honeypots to counter botting.
        'validate'   => FALSE,
    ];

    $options += $default_options;

    if (!$action_url) {
        $action_url = FORM_REQUEST;
    }

    $class = $options['class'];

    $validation = '';
    if ($options['validate']) {
        $class .= ' needs-validation';
        $validation = 'validation=""';
    }

    if (!fusion_safe()) {
        $class .= " warning";
    }

    $html = "<form name='".$form_name."' id='".$options['form_id']."' method='".$method."' action='".$action_url."' role='form' class='".($options['inline'] ? "form-inline " : '').(!empty($class) ? $class : 'm-0')."'".($options['enctype'] ? " enctype='multipart/form-data'" : '').($options['on_submit'] ? " onSubmit='".$options['on_submit']."'" : '')." ".$validation.">\n";

    if ($method == 'POST') {
        $token = fusion_get_token($options['form_id'], $options['max_tokens']);
        $html .= "<input type='hidden' name='fusion_token' value='{$token}'>";
        $html .= "<input type='hidden' name='form_id' value='{$options['form_id']}'>";
        if ($options['honeypot']) {
            $input_name = 'fusion_'.random_string();
            $html .= "<input type='hidden' name='$input_name' value=''>";

            Defender::getInstance()->addHoneypot([
                'honeypot'   => $options['form_id'].'_honeypot',
                'input_name' => $input_name,
                'form_name'  => $form_name,
                'type'       => 'honeypot',
            ]);
        }
    }

    return dynamics_render_component_template('form_main', $html);
}

/**
 * @return string
 */
function closeform() {
    return dynamics_render_component_template('form_main', "</form>\n");
}

/**
 * @param mixed $value
 *
 * @return array|string
 */
function clean_input_name($value) {
    $re = '/\[(.*?)\]/m';
    return preg_replace($re, '', $value);
}

/**
 * @param $value
 *
 * @return array|string
 */
function clean_input_value($value) {
    if (!is_float($value)) {
        if (is_string($value)) {
            return stripinput($value);
        }
        if (is_array($value)) {
            return array_map('stripinput', $value);
        }
    }

    return $value;
}

/**
 * Load Select2
 */
function load_select2_script() {
    static $loaded = FALSE;
    if ($loaded === FALSE) {

        /**
         * @return string
         * @see load_select2_script()
         */
        function select2csspath() {
            return DYNAMICS."assets/select2/select2.min.css";
        }

        $select2_locale_path = LOCALE.LOCALESET."includes/dynamics/assets/select2/select2_locale_".fusion_get_locale('select2').".js";

        fusion_load_script(DYNAMICS."assets/select2/select2.min.js");

        if (is_file($select2_locale_path)) {
            fusion_load_script($select2_locale_path);
        }

        /**
         * @uses select2csspath()
         */
        fusion_add_hook("fusion_core_styles", "select2csspath");

        $loaded = TRUE;
    }
}

/**
 * Set input notification
 * @param        $key
 * @param string $message
 */
function fusion_input_error($key, $message = '') {
    Defender::setInputError($key);
    if ($message) {
        Defender::setErrorText($key, $message);
    }
}

/**
 * Clears session drafts for IDE-style textareas
 * * @param string|bool $field_id Specific ID to clear, or TRUE to clear all
 */

function clear_text_session($field_id = '') {
	if (isset($_SESSION['form_drafts'][$field_id])) {
        // Clear only the specific field
        unset($_SESSION['form_drafts'][$field_id]);
    } else {
		// Clear everything
		unset($_SESSION['form_drafts']);
	}
}

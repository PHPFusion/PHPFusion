<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumHelper.php
| Author: PHPFusion Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\Quantum;

/**
 * Class QuantumHelper
 * Static functions for UserFields with Quantum
 *
 * @package PHPFusion\Quantum
 */
class QuantumHelper {

    /**
     * Get the field title from filename
     *
     * @param string $filename
     *
     * @return string
     */
    public static function filenameToTitle($filename) {

        $field_name = explode("_", $filename);
        $field_title = "";
        for ($i = 0; $i <= count($field_name) - 3; $i++) {
            $field_title .= ($field_title) ? "_" : "";
            $field_title .= $field_name[$i];
        }

        return $field_title;
    }

    /**
     * Improvise plugin folder location
     *
     * @param $data
     * @param $options
     *
     * @return mixed
     */
    public static function resolvePluginFolder($data, $options) {

        // /includes or /infusions is supported
        if (!empty($options['plugin_folder']) && is_array($options['plugin_folder']) && $data['field_type'] == 'file') {

            foreach ($options['plugin_folder'] as $plugin_folder) {

                $plugin_dir = rtrim($plugin_folder, '/').'/';

                // Check whether the plugin folder is /includes/user_fields/
                if (is_file($plugin_dir.$data['field_name'].'_include.php')) {

                    $options['plugin_folder'] = $plugin_dir;

                    $options['plugin_locale_folder'] = LOCALE.LOCALESET.'user_fields/';

                } else if ($folder_list = makefilelist($plugin_dir, '.|..|index.php', TRUE, 'folders')) {

                    foreach ($folder_list as $dir) {

                        if (is_file($plugin_dir.$dir.'/user_fields/'.$data['field_name'].'_include.php')) {

                            $options['plugin_folder'] = $plugin_dir.$dir.'/user_fields/';

                            $lang_dir = $plugin_dir.$dir.'/locale/English/';
                            if (is_dir($plugin_dir.$dir.'/locale/'.LANGUAGE.'/')) {
                                $lang_dir = $plugin_dir.$dir.'/locale/'.LANGUAGE.'/';
                            }

                            $options['plugin_locale_folder'] = $lang_dir;

                        }
                    }
                }
            }
        }

        return $options;
    }

    public static function getDynamicsType() {

        $locale = fusion_get_locale();

        return [
            'file'        => $locale['fields_0500'],
            'textbox'     => $locale['fields_0501'],
            'select'      => $locale['fields_0502'],
            'textarea'    => $locale['fields_0503'],
            'checkbox'    => $locale['fields_0504'],
            'toggle'      => $locale['fields_0505'],
            'datepicker'  => $locale['fields_0506'],
            'colorpicker' => $locale['fields_0507'],
            'upload'      => $locale['fields_0508'],
            'hidden'      => $locale['fields_0509'],
            'address'     => $locale['fields_0510'],
            'contact'     => $locale['fields_0516'],
            'tags'        => $locale['fields_0511'],
            'location'    => $locale['fields_0512'],
            'number'      => $locale['fields_0513'],
            'email'       => $locale['fields_0514'],
            'url'         => $locale['fields_0515'],
        ];
    }

    /**
     * @param $type
     * @param $method
     * @param $data
     * @param $options
     *
     * @return mixed|string
     */
    public static function displayUserFields($method, $data, $callback_data, $options) {

        // Sets callback data automatically.
        $option_list = $data['field_options'] ? explode(',', $data['field_options']) : [];

        // Format Callback Data
        $field_value = ($callback_data[$data['field_name']] ?? "");

        if (check_post($data['field_name']) && !$options['hide_value']) {
            //$field_value = form_sanitizer($_POST[$data['field_name']], '', $data['field_name']);
            $field_value = sanitizer($data['field_name'], "", $data['field_name']);

        } else if ($options['hide_value']) {
            $field_value = "";
        }

        $field_label = ($options['show_title'] ? self::parseLabel($data['field_title']) : "");

        if ($data['field_type'] == 'file') {
            if (!is_array($options['plugin_folder'])) {

                $field_file_path = $options['plugin_folder'].$data['field_name']."_include.php";

                if (is_file($field_file_path)) {

                    $user_data = $callback_data;

                    $profile_method = $method;

                    $locale = fusion_get_locale('', $options['plugin_locale_folder'].$data['field_name'].'.php');

                    include $field_file_path;

                    if ($method == 'input') {
                        if (isset($user_fields)) {
                            return $user_fields;
                        }
                    } else if ($method == 'display' && !empty($user_fields['value'])) {
                        return $user_fields;
                    }
                }
            } else {
                return '<div class="alert alert-warning"><strong>'.self::parseLabel($data['field_title']).' - '.fusion_get_locale('field_0205').'</strong></div>';
            }
        }

        if ($method == 'input') {

            if (in_array($data['field_type'], ['textbox', 'number', 'url', 'email'])) {

                if ($data['field_type'] == 'textbox') {
                    $data['field_type'] = 'text';
                }

                $options['type'] = $data['field_type'];

                return form_text($data['field_name'], $field_label, $field_value, $options);

            } else if (in_array($data['field_type'], ['select', 'tags'])) {

                if ($data['field_type'] == 'select') {
                    $options += ['options' => $option_list, 'allowclear' => FALSE, 'width' => '100%', 'inner_width' => '100%', 'keyflip' => TRUE];
                } else if ($data['field_type'] == 'tags') {
                    $options += ['options' => $option_list, 'tags' => TRUE, 'multiple' => TRUE, 'width' => '100%', 'inner_width' => '100%'];
                }

                return form_select($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'location') {

                $options += ['width' => '100%'];

                return form_location($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'textarea') {

                return form_textarea($data['field_name'], $field_label, $field_value, $options);

            } else if (in_array($data['field_type'], ['checkbox', 'toggle'])) {

                if ($data['field_type'] == 'toggle') {
                    $options['toggle'] = 1;
                }

                return form_checkbox($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'datepicker') {
                $options["date_format_php"] = "d-m-Y";
                $options["date_format_js"] = "DD-MM-YYYY";

                return form_datepicker($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'colorpicker') {

                return form_colorpicker($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'upload') {

                return form_fileinput($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'hidden') {

                return form_hidden($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'address') {

                return form_geo($data['field_name'], $field_label, $field_value, $options);

            } else if ($data['field_type'] == 'contact') {

                return form_contact($data['field_name'], $field_label, $field_value, $options);
            }

        } else if ($method == 'display' && $field_value) {

            if ($data['field_type'] == 'datepicker') {

                $field_value = showdate('shortdate', $field_value);

            } else if ($data['field_type'] == 'contact') {

                $field_value = implode('|', $field_value);

            } else if ($data['field_type'] == 'address') {

                if (!is_array($field_value)) {

                    $field_value = explode("|", $field_value);
                    if (count($field_value) === 6) {
                        [$address, $address2, $country, $region, $city, $postcode] = $field_value;
                        $addresses = [
                            [$address, $address2],
                            [$city, $region, $country],
                            [$postcode]
                        ];
                        foreach ($addresses as $adds) {
                            $values[] = implode(",", $adds);
                        }
                        $field_value = implode(",<br>", $values);
                    }
                }
            }

            return [
                'title' => $field_label,
                'value' => $field_value
            ];
        }

        return '';

    }

    /**
     * Parse the correct label language. Requires to be serialized $value.
     *
     * @param string $value Serialized
     *
     * @return string
     * NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
     */
    public static function parseLabel($value) {
        if (self::isSerialized($value)) {
            $value = unserialize(stripslashes($value)); // if anyone can give me an @unserialize($value) withotu E_NOTICE. I'll drop is_serialized function.

            return (string)(isset($value[LANGUAGE])) ? $value[LANGUAGE] : '';
        } else {
            return (string)$value;
        }
    }

    /**
     * @param string    $value
     * @param bool|null $result
     *
     * @return bool
     */
    public static function isSerialized($value, &$result = NULL) {

        // A bit of a give away this one
        if (!is_string($value)) {
            return FALSE;
        }
        // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
        // invalid string, or it could return FALSE if the string is serialized
        // FALSE, eliminate that possibility.
        if ('b:0;' === $value) {
            $result = FALSE;

            return TRUE;
        }
        $length = strlen($value);
        $end = '';
        if (isset($value[0])) {
            switch ($value[0]) {
                case 's':
                    if ('"' !== $value[$length - 2]) {
                        return FALSE;
                    }
                    break;
                case 'b':
                case 'i':
                case 'd':
                    // This looks odd, but it is quicker than isset()ing
                    $end .= ';';
                case 'a':
                case 'O':
                    $end .= '}';
                    if (':' !== $value[1]) {
                        return FALSE;
                    }
                    switch ($value[2]) {
                        case 0:
                        case 1:
                        case 2:
                        case 3:
                        case 4:
                        case 5:
                        case 6:
                        case 7:
                        case 8:
                        case 9:
                            break;
                        default:
                            return FALSE;
                    }
                case 'N':
                    $end .= ';';
                    if ($value[$length - 1] !== $end[0]) {
                        return FALSE;
                    }
                    break;
                default:
                    return FALSE;
            }
        }

        if (($result = unserialize(stripslashes($value))) === FALSE) {
            $result = NULL;

            return FALSE;
        }

        return TRUE;
    }

    /**
     * Multiple locale fields input
     *
     * @param string $input_name
     * @param string $title
     * @param mixed  $input_value
     * @param array  $options
     *
     * @return string
     */
    public static function quantumMultilocaleFields(string $input_name, string $title, string $input_value, array $options = []) {

        $locale = fusion_get_locale();

        $language_opts = fusion_get_enabled_languages();

        $input_value = self::isSerialized($input_value) ? unserialize(stripslashes($input_value)) : $input_value;

        $options += [
            'function'    => !empty($options['textarea']) && $options['textarea'] == 1 ? 'form_textarea' : 'form_text',
            // only 2 fields type need a multiple locale logically
            'required'    => !empty($options['required']) && $options['required'] == 1 ? '1' : '0',
            'placeholder' => !empty($options['placeholder']) ? $options['placeholder'] : '',
            'deactivate'  => !empty($options['deactivate']) && $options['deactivate'] == 1 ? '1' : '0',
            'width'       => !empty($options['width']) ? $options['width'] : '100%',
            'class'       => !empty($options['class']) ? $options['class'] : '',
            'inline'      => !empty($options['inline']) ? $options['inline'] : '',
            'max_length'  => !empty($options['max_length']) ? $options['max_length'] : '200',
            'error_text'  => !empty($options['error_text']) ? $options['error_text'] : '',
            'safemode'    => !empty($options['safemode']) && $options['safemode'] == 1 ? '1' : '0',
            'icon'        => !empty($options['icon']) ? $options['icon'] : '',
            'input_id'    => !empty($options['input_id']) ? $options['input_id'] : $input_name,
        ];

        $required = $options['required'];

        $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $title ? 'row ' : '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'>";
        $html .= ($title) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."'>$title ".($options['required'] == 1 ? "<span class='required'>*</span>" : '')."</label>" : '';
        $html .= ($options['inline'] && $title) ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "";
        $main_html = '';
        $sub_html = '';
        foreach ($language_opts as $lang => $langNames) {
            $options['field_title'] = $title." (".$langNames.")";
            $options['input_id'] = $input_name."-".$lang;
            if ($lang == LANGUAGE) {
                $options['required'] = $required;
                $options['prepend_value'] = $langNames;
                // Fix this
                $main_html .= $options['function']($input_name."[$lang]", "",
                    $input_value[$lang] ?? $input_value,
                    $options);
            } else {
                $options['required'] = 0;
                $options['prepend_value'] = $langNames;
                $sub_html .= $options['function']($input_name."[$lang]", "", $input_value[$lang] ?? '', $options);
            }
        }

        $html .= $main_html.$sub_html;

        if (count($language_opts) > 1) {
            $html .= "<div class='dropdown m-b-15'>";
            $html .= "<button id='lang_dropdown' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false' class='dropdown-toggle btn btn-sm btn-default' type='button'>".$locale['add_language']." <span class='caret'></span></button>";
            $html .= "<ul class='dropdown-menu' aria-labelledby='lang_dropdown'>";
            foreach ($language_opts as $Lang => $LangName) {
                if ($Lang !== LANGUAGE) {
                    $html .= "<li><a data-add='$Lang' data-input='$input_name' data-locale='$LangName' class='pointer data-add'><i class='fa fa-plus-circle fa-fw'></i> $LangName</a></li>";
                    add_to_jquery("$('#".$input_name."-".$Lang."-field').hide();");
                }
            }
            $html .= "</ul>";
            $html .= "</div>";
            if (!defined('QUANTUM_MULTILOCALE_FIELDS')) {
                define('QUANTUM_MULTILOCALE_FIELDS', TRUE);
                add_to_jquery(/** @lang JavaScript */ "
                $('.data-add').on('click', function(e) {
                    var lang = $(this).data('add');
                    var langNames = $(this).data('locale');
                    var inputName = $(this).data('input');
                    var dom = $('#'+ inputName +'-' + lang + '-field');
                    if ( dom.is(':visible') ) {
                        dom.hide();
                        $(this).html('<i class=\"fa fa-plus-circle fa-fw\"></i>'+langNames);
                    } else {
                        dom.show();
                        $(this).html('<i class=\"fa fa-minus-circle fa-fw\"></i>'+langNames);
                    }
                    e.stopPropagation();
                });
                ");
            }
        }
        $html .= ($options['inline'] && $title) ? "</div>" : "";
        $html .= "</div>";

        return $html;
    }

    /**
     * @param array  $data
     * @param string $input_name
     *
     * @return array|bool|mixed|string|null
     */
    public static function fusionGetLocale($data, $input_name) {

        $language_opts = fusion_get_enabled_languages();

        if (check_post($input_name)) {
            return self::serializeFields($input_name);

        } else {
            if (isset($data[$input_name])) {

                if (self::isSerialized($data[$input_name])) {
                    return unserialize(stripslashes($data[$input_name]));

                } else {
                    $value = [];
                    foreach ($language_opts as $lang) {
                        $value[$lang] = $data[$input_name];
                    }

                    return $value;
                }
            } else {
                return NULL;
            }
        }
    }

    /**
     * Short serialization function.
     *
     * @param string $input_name
     *
     * @return bool|string
     */
    public static function serializeFields($input_name) {

        if ($input = post([$input_name])) {

            $field_var = [];
            foreach ($input as $language => $value) {
                $field_var[$language] = form_sanitizer($value);
            }

            return serialize($field_var);
        }

        return FALSE;
    }

    /**
     * Single array output match against $db - use get_structureData before to populate $fields
     */
    public static function dynamicsFieldInfo($type, $default_value) {

        $info = [
            'textbox'     => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'select'      => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'textarea'    => "TEXT NOT NULL",
            'tags'        => "TEXT NOT NULL",
            'contact'     => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
            'checkbox'    => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'toggle'      => "TINYINT(3) NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'datepicker'  => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'location'    => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'colorpicker' => "VARCHAR(10) NOT NULL DEFAULT '".$default_value."'",
            'upload'      => "VARCHAR(100) NOT NULL DEFAULT '".$default_value."'",
            'hidden'      => "VARCHAR(50) NOT NULL DEFAULT '".$default_value."'",
            'address'     => "TEXT NOT NULL",
            'number'      => "INT(10) UNSIGNED NOT NULL DEFAULT '".(isnum($default_value) ? $default_value : 0)."'",
            'email'       => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
            'url'         => "VARCHAR(200) NOT NULL DEFAULT '".$default_value."'",
        ];

        return $info[$type];
    }

    /**
     * @param int $field_id
     *
     * @return false|int
     */
    public static function validateField($field_id) {

        if (isnum($field_id)) {
            return dbcount("(field_id)", DB_USER_FIELDS, "field_id=:id", [':id' => (int)$field_id]);
        }

        return 0;
    }

    /**
     * @param int $field_cat_id
     *
     * @return false|int
     */
    public static function validateFieldCat($field_cat_id) {

        if (isnum($field_cat_id)) {
            return dbcount("(field_cat_id)", DB_USER_FIELD_CATS, "field_cat_id=:cid", [':cid' => (int)$field_cat_id]);
        }

        return 0;
    }
}

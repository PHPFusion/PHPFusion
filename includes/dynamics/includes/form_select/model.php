<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
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
 * Project-owned, progressively enhanced combobox select.
 *
 * The native select remains the form value source. JavaScript adds local search,
 * multiple-selection chips, tags, and normalized remote results without
 * allowing endpoint-provided markup into the document.
 *
 * Note on Tags Support
 * $options['tags'] = default $input_value must not be multidimensional array but only as $value = array(TRUE,'2','3');
 * For tagging - set both tags and multiple to TRUE
 * Remote example:
 * form_select('country', 'Country', '', [
 *     'ajax' => [
 *         'url' => '/countries.json.php',
 *         'minimum_input_length' => 1,
 *         'load_on_open' => TRUE,
 *     ],
 *     'flag' => TRUE,
 * ]);
 *
 * @param        $input_name
 * @param string $label
 * @param        $input_value
 * @param array  $options
 *
 * Setting up a select chain
 *
 * There will be 2 select box, Parent Select and Child Select
 * Parent Select has options value of: array(1 => 'Parent A', 2 => 'Parent B')
 * Parent Child has options value of: array(3 => 'Child A', 4 => 'Child B');
 * The way that these two select chain to each other is via $options['chain_index'] with a value of:
 * array(3 => 1, 4 => 1); ***
 * The above array is the 'id of child A' => 'id of parent of A'
 * key - current option value (i.e. +60)
 * value - parent value that this option value is chained against (i.e. Malaysia)
 *
 * Implementation
 * 1. Do nothing for Parent Select
 * 2. In Child Select add:
 * $options['chainable'] - set to true
 * $options['chain_to_id'] - unique input_id of the Parent Select
 * $options['chain_index'] - the chain array (see ***)
 *
 * Example code
 * form_select('parent', 'Parent Sample', $parent_callback_value, ['options'=>$parent_opts]);
 * form_select('child', 'Child Sample', $child_callback_value, ['options' => $child_opts, 'chainable'=>TRUE, 'chain_to_id'=>'parent', 'chain_index'=>$chain_index_opts]);
 *
 * @return string
 *
 * @package dynamics/form_select
 */
function form_select_escape_attribute($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function form_select_escape_text($value): string {
    $decoded = html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function form_select_value_is_selected($input_value, $candidate): bool {
    $candidate = (string)$candidate;

    if (is_array($input_value)) {
        foreach ($input_value as $value) {
            if ((string)$value === $candidate) {
                return TRUE;
            }
        }

        return FALSE;
    }

    return $input_value !== NULL && (string)$input_value === $candidate;
}

function form_select_option_data_attributes(array $option): string {
    $attributes = [];

    foreach ($option as $key => $value) {
        if (in_array($key, ['text', 'children'], TRUE) || !preg_match('/^[a-z][a-z0-9_-]*$/i', (string)$key)) {
            continue;
        }

        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } elseif (!is_scalar($value) && $value !== NULL) {
            continue;
        }

        $attributes[] = 'data-'.strtolower((string)$key).'="'.form_select_escape_attribute($value ?? '').'"';
    }

    return $attributes ? ' '.implode(' ', $attributes) : '';
}

function form_select_combobox_config(array $options): array {
    $remote = $options['ajax'] ?: $options['remote'];
    if (is_string($remote)) {
        $remote = ['url' => $remote];
    }
    if (!is_array($remote)) {
        $remote = [];
    }

    $remote_config = [];
    if (!empty($remote['url'])) {
        $method = strtoupper((string)($remote['method'] ?? 'GET'));
        $remote_config = [
            'url'                => (string)$remote['url'],
            'method'             => in_array($method, ['GET', 'POST'], TRUE) ? $method : 'GET',
            'queryParam'         => (string)($remote['query_param'] ?? $remote['queryParam'] ?? 'q'),
            'delay'              => max(0, (int)($remote['delay'] ?? 250)),
            'minimumInputLength' => max(0, (int)($remote['minimum_input_length'] ?? $remote['minimumInputLength'] ?? 0)),
            'loadOnOpen'         => (bool)($remote['load_on_open'] ?? $remote['loadOnOpen'] ?? TRUE),
            'resultsKey'         => (string)($remote['results_key'] ?? $remote['resultsKey'] ?? 'results'),
            'params'             => is_array($remote['params'] ?? NULL) ? $remote['params'] : [],
        ];
    }

    $strings = array_merge([
        'search'      => 'Search options...',
        'resetSearch' => 'Reset search',
        'clear'       => 'Clear',
        'selected'    => 'selected',
        'empty'       => 'No options found.',
        'typeMore'    => 'Type to search.',
        'loading'     => 'Loading options...',
        'error'       => 'Unable to load options. Try again.',
        'create'      => 'Create',
        'remove'      => 'Remove',
    ], is_array($options['strings']) ? $options['strings'] : []);

    return [
        'placeholder'     => html_entity_decode((string)$options['placeholder'], ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'label'           => html_entity_decode((string)($options['combobox_label'] ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        'floatingLabel'   => (bool)$options['floating_label'],
        'required'        => (bool)$options['required'],
        'multiple'        => (bool)$options['multiple'],
        'tags'            => (bool)$options['tags'],
        'allowClear'      => (bool)($options['allowclear'] || $options['allow_clear']),
        'searchThreshold' => max(0, (int)$options['display_search_count']),
        'maxSelect'       => $options['max_select'] === FALSE ? 0 : max(0, (int)$options['max_select']),
        'delimiter'       => (string)$options['delimiter'],
        'remote'          => $remote_config,
        'flagBaseUrl'     => !empty($options['flag']) ? (string)($options['flag_path'] ?: IMAGES.'small_flag/') : '',
        'avatarBaseUrl'   => (string)$options['avatar_path'],
        'initialItems'    => is_array($options['initial_items']) ? $options['initial_items'] : [],
        'strings'         => $strings,
    ];
}

function form_select_combobox_attribute(array $options): string {
    $json = json_encode(
        form_select_combobox_config($options),
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );

    return form_select_escape_attribute($json ?: '{}');
}

function load_form_select_combobox_assets(): void {
    static $loaded = FALSE;

    if (!$loaded) {
        fusion_load_script(DYNAMICS.'includes/form_select/component.css', 'css');
        fusion_load_script(DYNAMICS.'includes/form_select/component.js');
        $loaded = TRUE;
    }
}

function form_select($input_name, $label, $input_value, $options = []) {

    $locale = fusion_get_locale();
    
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    
    $default_options = [
        'options'              => [],
        'required'             => FALSE,
        'regex'                => '',
        'input_id'             => $input_name,
        'placeholder'          => $locale['choose'],
        'deactivate'           => FALSE,
        'safemode'             => FALSE,
        'allowclear'           => FALSE,
        'allow_clear'          => FALSE,
        'multiple'             => FALSE,
        'width'                => '',
        'inner_width'          => '100%',
        'inner_class'          => '',
        'keyflip'              => FALSE,
        'tags'                 => FALSE,
        'jsonmode'             => FALSE,
        'chainable'            => FALSE,      // Set to True to Enable Chaining
        'chain_to_id'          => '',         // Set which select it has to be chained to.
        'db'                   => '',         // Specify which DB to map
        'id_col'               => '',         // Chain Primary Key Column Name
        'cat_col'              => '',         // Chain Category Column Name
        'title_col'            => '',         // Chain Title Column Name
        'title_append_id'      => FALSE, // appends ID column info into the option text
        'custom_query'         => '',         // SQL query replacements
        'value_filter'         => [ 'col' => '', 'value' => NULL ], // Specify if building opts has to specifically match these conditions
        'optgroup'             => FALSE,      // Enable the option group output - if db, id_col, cat_col, and title_col is specified.
        'option_pattern'       => "&#8212;",
        'display_search_count' => "5",
        'max_select'           => FALSE,
        'error_text'           => $locale['error_input_default'],
        'class'                => '',
        'label_class'          => '',
        'floating_label'       => FALSE,
        'inline'               => FALSE,
        'tip'                  => '',
        'ext_tip'              => '',
        'delimiter'            => ',',
        'callback_check'       => '',
        'stacked'              => '',
        'onchange'             => '',
        'combobox_disabled'    => FALSE,
        'parent_value'         => $locale['root'],
        'add_parent_opts'      => FALSE,
        'disable_opts'         => '',
        'hide_disabled'        => FALSE,
        'no_root'              => FALSE,
        'disable_all_parents'  => FALSE,
        'show_current'         => FALSE,
        'db_cache'             => TRUE,
        'data'                 => [],
        'modal_parent'         => '',
        'ajax'                 => [],
        'remote'               => [],
        'flag'                 => FALSE,
        'flag_path'            => '',
        'avatar_path'          => '',
        'initial_items'        => [],
        'strings'              => [],
    ];
    $options += $default_options;

    $input_value = clean_input_value($input_value);
    
    // this must be string.
    if ($options['multiple']) {
        // Handles post and need to override
        $input_name_var = str_replace([ '[', ']' ], [], $input_name);
        if (check_post([$input_name_var])) {
            $input_value = implode($options['delimiter'], post([ $input_name_var ]));
        } elseif (is_array($input_value)) {
            $input_value = implode($options['delimiter'], $input_value);
        }
    } else {
        if (check_post($input_name)) {
            $input_value = post($input_name);
        }
    }


    if ( $options['allowclear'] || $options['allow_clear'] ) {
        $options['allow_clear'] = TRUE;
        $options['allowclear'] = TRUE;
    }

    $disable_opts = '';
    if ( $options['disable_opts'] ) {
        $disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
    }
    $list = [];

    static $select_db = [];

    // New DB Caching Function.
    if ( $options['db'] && $options['id_col'] && $options['title_col'] || $options['custom_query'] ) {

        // Cache result
        $cache = ! $options['custom_query'];

        if ( empty($select_db[ $options['db'] ]) || ( ! $cache or $options['db_cache'] === FALSE ) ) {

            if ( ! empty($options['cat_col']) ) {

                $select_db[ $options['db'] ] = dbquery_tree_full($options['db'], $options['id_col'], $options['cat_col'], "ORDER BY " . $options['cat_col'] . " ASC, " . $options['id_col'] . " ASC, " . $options['title_col'] . " ASC", ( $options['custom_query'] ? : "" ));

            }
            else {
                // if there is a custom query
                if ( $options['custom_query'] ) {

                    $select_result = dbquery($options['custom_query']);
                }
                else {
                    $select_result = dbquery("SELECT * FROM " . $options['db'] . " ORDER BY " . $options['id_col'] . ", " . $options['title_col']);
                }

                // then make into hierarchy
                if ( dbrows($select_result) ) {

                    while ( $data = dbarray($select_result) ) {
                        $list[0][ $data[ $options['id_col'] ] ] = $data;
                    }

                    if ( empty($options['db']) ) {
                        $options['db'] = random_string(6);
                    }

                    $select_db[ $options['db'] ] = $list;

                }
            }
            /*
             * Build opt functions
             */
            if ( ! function_exists('get_form_select_opts') ) {
                // @todo: implement all options settings inherited from dbquery_select_hierarchy
                function get_form_select_opts($data, $options, $id = 0, $level = 0) {

                    $list = [];
                    //array('text' => 'Parent Text', 'children' => array(1 => 'Child A' , 2 => 'Child B'));
                    if ( ! empty($data[ $id ]) ) {
                        foreach ( $data[ $id ] as $key => $value ) {
                            // Displays defined pattern
                            $pattern = "";
                            if ( $options['option_pattern'] ) {
                                $pattern = str_repeat($options['option_pattern'], $level) . " ";
                            }
                            // Build List
                            if ( ! empty($options['value_filter']['col']) && ( ! empty($options['value_filter']['value']) || $options['value_filter']['value'] !== NULL ) ) {
                                if ( isset($value[ $options['value_filter']['col'] ]) && $value[ $options['value_filter']['col'] ] == $options['value_filter']['value'] ) {
                                    $list[ $key ] = $pattern . html_entity_decode($value[ $options['title_col'] ]);
                                    if ( $options['title_append_id'] ) {
                                        $list[ $key ] .= " (ID:" . $value[ $options['id_col'] ] . ")";
                                    }
                                }
                            }
                            else {
                                $list[ $key ] = $pattern . html_entity_decode($value[ $options['title_col'] ]);

                                if ( $options['title_append_id'] ) {
                                    $list[ $key ] .= " (ID:" . $value[ $options['id_col'] ] . ")";
                                }

                            }
                            // Build Child
                            if ( isset($data[ $key ]) ) {

                                $child = get_form_select_opts($data, $options, $key, $level + 1);

                                if ( $options['optgroup'] ) {
                                    $list[ $key ] = [
                                        'text'     => $list[ $key ],
                                        'children' => $child,
                                    ];
                                }
                                else {
                                    $list = ( ! empty($list) ) ? $list + $child : $child;
                                }
                            }
                        }
                    }
                    else {
                        // the list does not start with a root
                        foreach ( array_keys($data) as $id ) {
                            foreach ( $data[ $id ] as $key => $value ) {
                                // Displays defined pattern
                                $pattern = "";
                                if ( $options['option_pattern'] ) {
                                    $pattern = str_repeat($options['option_pattern'], $level) . " ";
                                }
                                // Build List
                                if ( ! empty($options['value_filter']['col']) && ( ! empty($options['value_filter']['value']) || $options['value_filter']['value'] !== NULL ) ) {
                                    if ( isset($value[ $options['value_filter']['col'] ]) && $value[ $options['value_filter']['col'] ] == $options['value_filter']['value'] ) {
                                        $list[ $key ] = $pattern . html_entity_decode($value[ $options['title_col'] ]);
                                    }
                                }
                                else {
                                    $list[ $key ] = $pattern . html_entity_decode($value[ $options['title_col'] ]);
                                }

                                if ( $options['title_append_id'] ) {
                                    $list[ $key ] .= " (ID:" . $value[ $options['id_col'] ] . ")";
                                }

                                // Build Child
                                if ( isset($data[ $key ]) ) {
                                    $child = get_form_select_opts($data, $options, $key, $level + 1);
                                    if ( $options['optgroup'] ) {
                                        $list[ $key ] = [
                                            'text'     => $list[ $key ],
                                            'children' => $child,
                                        ];
                                    }
                                    else {
                                        $list = ( ! empty($list) ) ? $list + $child : $child;
                                    }
                                }
                            }
                        }
                    }

                    return (array)$list;
                }
            }
            /**
             * Build Chainable Reference
             * array key    current id
             *      value   parent id
             */
            if ( ! function_exists('get_form_select_chain_index') ) {
                function get_form_select_chain_index($data, $options) {

                    $list = [];
                    if ( ! empty($data) ) {
                        $data = flatten_array($data);
                        foreach ( $data as $value ) {
                            $list[ $value[ $options['id_col'] ] ] = $value[ $options['cat_col'] ];
                        }
                    }

                    return $list;
                }
            }
        }
        // Automatic build chain index.
        if ( $options['chainable'] && $options['chain_to_id'] && empty($options['chain_index']) ) {
            $options['chain_index'] = get_form_select_chain_index($select_db[ $options['db'] ], $options);
        }

        if ( ! empty($select_db[ $options['db'] ]) ) {
            // Build options and override declared options
            $options['options'] = get_form_select_opts($select_db[ $options['db'] ], $options);
        }
        else {
            $options['options'] = [ '0' => $locale['no_opts'] ];
            $options['deactivate'] = 1;
        }
    }
    else if ( empty($options['options']) && empty($options['ajax']) && empty($options['remote']) && !$options['tags'] && !$options['jsonmode'] ) {
        $options['options'] = [ '0' => $locale['no_opts'] ];
        $options['deactivate'] = 1;
    }

    // Build a chain index and initialize the JS chain plugin
    // $options['chainable']    - true
    // $options['chain_to_id']  - parent id
    // $options['chain_index'] - a list of array of current id and the parent id as value (see get_form_select_chain_index function how it's done)
    if ( $options['chainable'] && $options['chain_to_id'] && ! empty($options['chain_index']) ) {
        fusion_load_script(DYNAMICS . "assets/chainselect/jquery.chained.js");
        add_to_jquery("$('#" . $options['input_id'] . "').chained('#" . $options['chain_to_id'] . "');");
    }

    // Optgroup with Hierarchy
    if ( ! function_exists('form_select_build_optgroup') ) {
        function form_select_build_optgroup($array, $input_value, $options) {

            $html = '';
            $disable_opts = '';
            if ( $options['disable_opts'] ) {
                $disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
            }

            foreach ( $array as $arr => $value ) {

                // where options is more than one value, pass to data attributes.
                $data_attributes = is_array($value) ? form_select_option_data_attributes($value) : '';

                $select = "";
                $chain = ( isset($options['chain_index'][ $arr ]) ? " class='" . form_select_escape_attribute($options['chain_index'][ $arr ]) . "' " : "" );
                $text_value = $value['text'] ?? $value;
                // if you have data attributes, you must have text key
                if ( ! empty($text_value) && ! is_array($text_value) ) {
                    if ( $options['keyflip'] ) { // flip mode = store array values
                        if ( $input_value !== '' ) {
                            $select = form_select_value_is_selected($input_value, $text_value) ? " selected" : "";
                        }
                        $disabled = $disable_opts && in_array($arr, $disable_opts);
                        $hide = $disabled && $options['hide_disabled'];
                        $item = ( ! $hide ? "<option" . $data_attributes . " value='".form_select_escape_attribute($text_value)."'" . $chain . $select . ( $disabled ? ' disabled' : '' ) . ">" . form_select_escape_text($text_value) . " " . ( $options['show_current'] && form_select_value_is_selected($input_value, $text_value) ? '(Current Item)' : '' ) . "</option>" : "" );

                    }
                    else {
                        if ( $input_value !== '' ) {
                            $select = form_select_value_is_selected($input_value, $arr) ? ' selected' : '';
                        }
                        $disabled = $disable_opts && in_array($arr, $disable_opts);
                        $hide = $disabled && $options['hide_disabled'];
                        $item = ( ! $hide ? "<option" . $data_attributes . " value='".form_select_escape_attribute($arr)."'" . $chain . $select . ( $disabled ? ' disabled' : '' ) . ">" . form_select_escape_text($text_value) . ( $options['show_current'] && form_select_value_is_selected($input_value, $arr) ? ' (Current Item)' : '' ) . "</option>" : "" );
                        //$item = "<option value='$arr'".$chain.$select.">$text_value</option>";
                    }
                    if ( isset($value['children']) ) {

                        $opt_html = "<optgroup label='" . form_select_escape_attribute($value['text']) . "'>";
                        $opt_html .= $item;
                        $opt_html .= form_select_build_optgroup($value['children'], $input_value, $options);
                        $opt_html .= "</optgroup>";

                        $html .= $opt_html;
                    }
                    else {
                        $html .= $item;
                    }
                }

            }

            return dynamics_render_component_template('form_select', $html);
        }
    }

    if ( ! $options['width'] ) {
        $options['width'] = $default_options['width'];
    }

    if ( $options['multiple'] ) {
        if ( $input_value !== NULL ) {
            $input_value = explode($options['delimiter'], $input_value);
        }
        else {
            $input_value = [];
        }
    }

    // always trim id
    $options['input_id'] = trim($options['input_id'], "[]");
    $options['combobox_label'] = trim(strip_tags((string)$label));
    $combobox_enabled = $options['combobox_disabled'] === FALSE;
    $combobox_attribute = $combobox_enabled ? " data-dynamics-combobox='".form_select_combobox_attribute($options)."'" : '';

    $error_class = "";
    if ( Defender::inputHasError($input_name) ) {
        $error_class = " has-error ";
        if ( ! empty($options['error_text']) ) {
            $new_error_text = Defender::getErrorText($input_name);
            if ( ! empty($new_error_text) ) {
                $options['error_text'] = $new_error_text;
            }
            addnotice("danger", $options['error_text']);
        }
    }

    $html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ( $options['inline'] && $label ? 'row' : '' ) . $error_class . ' ' . $options['class'] . "' " . ( $options['width'] ? "style='width: " . $options['width'] . "'" : '' ) . ">";
    $html .= ( $label ) ? "<label class='form-label {$options['label_class']}" . ( $options['inline'] ? " col-xs-12 col-sm-12 col-md-3 col-lg-3" : '' ) . "' for='" . $options['input_id'] . "'>" . $label . ( $options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '' ) . "
    " . ( $options['tip'] ? "<i class='pointer fa fa-question-circle' title='" . $options['tip'] . "'></i>" : '' ) . "
    </label>" : '';

    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>" : "";

    if ( $options['jsonmode'] ) {
        // json mode.
        $options['width'] = '250px';

        $html .= "<div id='" . $options['input_id'] . "-spinner' style='display:none;'><img alt='spinner' src='" . fusion_get_settings('siteurl') . "images/loader.svg'></div>";
        $serialized_value = is_array($input_value) ? implode($options['delimiter'], $input_value) : (string)$input_value;
        $html .= "<input " . ( $options['required'] ? "class='req'" : '' ) . " type='hidden' name='".form_select_escape_attribute($input_name)."' id='" . form_select_escape_attribute($options['input_id']) . "' value='".form_select_escape_attribute($serialized_value)."' style='width: " . form_select_escape_attribute($options['inner_width']) . "'".$combobox_attribute."/>";

    }
    else {

        // normal mode
        $inner_class = 'form-select';
        if ( $options['inner_class'] ) {
            $inner_class .= ' '.$options['inner_class'];
        }

        $multiple = $options['multiple'] || $options['tags'] ? " multiple" : '';


        $html .= "<select class='".form_select_escape_attribute($inner_class)."' name='".form_select_escape_attribute($input_name)."' id='" . form_select_escape_attribute($options['input_id']) . "' style='width: " . form_select_escape_attribute(!empty($options['inner_width']) ? $options['inner_width'] : $default_options['inner_width']) . "'" . ( $options['deactivate'] ? " disabled" : "" ) . ( $options['onchange'] ? ' onchange="' . form_select_escape_attribute($options['onchange']) . '"' : '' ) . $combobox_attribute . " {$multiple}>";

        if ( $options['combobox_disabled'] ) {
            $html .= ( $options['allowclear'] ) ? "<option value='' disabled selected hidden>" . form_select_escape_text($options['placeholder']) . "</option>" : '';
        }
        else {
            $html .= ( $options['allowclear'] ) ? "<option value=''></option>" : '';
        }

        // add parent value
        if ( $options['no_root'] == FALSE && ! empty($options['cat_col']) || $options['add_parent_opts'] === TRUE ) { // api options to remove root from selector. used in items creation.
            $this_select = '';
            if ( $input_value !== NULL ) {
                if ( $input_value !== '' ) {
                    $this_select = 'selected';
                }
            }
            $html .= "<option value='0' " . $this_select . " >" . form_select_escape_text($options['parent_value']) . "</option>";
        }

        /**
         * Supported Formatting
         * ---------------------
         * Have an array that looks like this in 'options' key
         * array('text' => 'Parent Text', 'children' => array(1 => 'Child A' , 2 => 'Child B'));
         * or
         * array(1 => 'Option A', 2 => 'Option B');
         */
        if ( is_array($options['options']) ) {
            // Test if this is an optgroup
            $test_array = $options['options'];
            foreach ( $test_array as $v ) {
                if ( isset($v['text']) ) {
                    $options['optgroup'] = TRUE;
                    break;
                }
            }
            if ( $options['optgroup'] ) {
                $html .= form_select_build_optgroup($options['options'], $input_value, $options);
            }
            else {

                foreach ( $options['options'] as $arr => $v ) { // outputs: key, value, class - in order
                    $select = '';
                    $chain = '';
                    // Chain method always bind to option's array key
                    if ( isset($options['chain_index'][ $arr ]) ) {
                        $chain = " class='" . form_select_escape_attribute($options['chain_index'][ $arr ]) . "' ";
                    }

                    // do a disable for filter_opts item.
                    if ( $options['keyflip'] ) { // flip mode = store array values
                        if ( $input_value !== '' ) {
                            $select = form_select_value_is_selected($input_value, $v) ? " selected" : "";
                        }
                        $disabled = $disable_opts && in_array($arr, $disable_opts);
                        $hide = $disabled && $options['hide_disabled'];
                        $html .= ( ! $hide ? "<option value='".form_select_escape_attribute($v)."'" . $chain . $select . ( $disabled ? ' disabled' : '' ) . " >" . form_select_escape_text($v) . " " . ( $options['show_current'] && form_select_value_is_selected($input_value, $v) ? '(Current Item)' : '' ) . "</option>" : "" );
                    }
                    else {
                        if ( $input_value !== '' ) {
                            //$input_value = stripinput($input_value); // not sure if can turn FALSE to zero not null.
                            $select = form_select_value_is_selected($input_value, $arr) ? ' selected' : '';
                        }
                        $disabled = $disable_opts && in_array($arr, $disable_opts);
                        $hide = $disabled && $options['hide_disabled'];
                        $html .= ( ! $hide ? "<option value='".form_select_escape_attribute($arr)."'" . $chain . $select . ( $disabled ? ' disabled' : '' ) . ">" . form_select_escape_text($v) . " " . ( $options['show_current'] && form_select_value_is_selected($input_value, $arr) ? '(Current Item)' : '' ) . "</option>" : "" );
                    }
                }
            }
        }
        if ($options['tags'] && is_array($input_value)) {
            $available_values = array_map('strval', array_keys($options['options']));
            if ($options['keyflip']) {
                $available_values = array_map('strval', array_values($options['options']));
            }
            foreach ($input_value as $tag) {
                if ((string)$tag !== '' && !in_array((string)$tag, $available_values, TRUE)) {
                    $html .= "<option value='".form_select_escape_attribute($tag)."' selected>".form_select_escape_text($tag)."</option>";
                }
            }
        }
        $html .= "</select>";
    }

    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<div class='help-block'>" . $options['ext_tip'] . "</div>" : "";
    $html .= Defender::inputHasError($input_name) && ! $options['inline'] ? "<br/>" : "";
    $html .= Defender::inputHasError($input_name) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
    $html .= $options['inline'] && $label ? "</div>" : '';
    $html .= "</div>";

    if ( $options['required'] ) {
        $html .= "<input class='req' id='dummy-" . $options['input_id'] . "' type='hidden'>"; // for jscheck
    }
    // Generate Defender Tag
    $input_name = ( $options['multiple'] ) ? str_replace("[]", "", $input_name) : $input_name;
    
    Defender::add_field_session([
                                    'input_name'     => clean_input_name($input_name),
                                    'title'          => clean_input_name($title),
                                    'id'             => $options['input_id'],
                                    'type'           => 'dropdown',
                                    'regex'          => $options['regex'],
                                    'required'       => $options['required'],
                                    'safemode'       => $options['safemode'],
                                    'error_text'     => $options['error_text'],
                                    'callback_check' => $options['callback_check'],
                                    'delimiter'      => $options['delimiter'],
                                ]);
    if ($combobox_enabled) {
        load_form_select_combobox_assets();
    }

    return dynamics_render_component_template('form_select', $html);
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
function form_user_select($input_name, $label = "", $input_value = FALSE, array $options = []) {

    $locale = fusion_get_locale();

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $input_value = clean_input_value($input_value);

    $default_options = [
        'required'          => FALSE,
        'regex'             => '',
        'input_id'          => $input_name,
        'placeholder'       => $locale['sel_user'],
        'deactivate'        => FALSE,
        'safemode'          => FALSE,
        'allowclear'        => FALSE,
        'multiple'          => FALSE,
        'inner_width'       => '100%',
        'width'             => '',
        'keyflip'           => FALSE,
        'tags'              => FALSE,
        'jsonmode'          => FALSE,
        'chainable'         => FALSE,
        'max_select'        => 1,
        'error_text'        => '',
        'class'             => '',
        'stacked'           => '',
        'inline'            => FALSE,
        'tip'               => '',
        'ext_tip'           => '',
        'delimiter'         => ',',
        'callback_check'    => '',
        'file'              => '',
        'allow_self'        => FALSE,
        'callback_function' => '',
        'image_path'        => IMAGES . "avatars/",
        'label_class'       => '',
        'strings'           => [],
    ];

    $options += $default_options;

    $options['input_id'] = trim($options['input_id'], "[]");

    $error_class = "";

    $root_prefix = fusion_get_settings("site_seo") == 1 ? fusion_get_settings('siteurl') . "includes/" : INCLUDES;
    $root_img = fusion_get_settings("site_seo") == 1 && ! defined('ADMIN_PANEL') ? fusion_get_settings('siteurl') : '';
    $path = ! empty($options['file']) ? $options['file'] : $root_prefix . "dynamics/assets/users/users.json.php" . ( $options['allow_self'] ? "?allow_self=true" : "" );

    if ( ! empty($input_value) ) {
        $encoded = $options['callback_function'] && is_callable($options['callback_function']) ? $options['callback_function']($input_value) : user_search($input_value, $options['delimiter']);
    }
    else {
        $encoded = [];
    }
    if (is_string($encoded)) {
        $decoded = json_decode($encoded, TRUE);
        $initial_items = is_array($decoded) ? $decoded : [];
    } else {
        $initial_items = is_array($encoded) ? $encoded : [];
    }

    $combobox_options = [
        'placeholder'          => $options['placeholder'],
        'multiple'             => TRUE,
        'tags'                 => FALSE,
        'allowclear'           => $options['allowclear'],
        'allow_clear'          => $options['allowclear'],
        'display_search_count' => 0,
        'max_select'           => $options['max_select'],
        'delimiter'            => $options['delimiter'],
        'ajax'                 => [
            'url'                  => $path,
            'minimum_input_length' => 1,
            'load_on_open'         => FALSE,
        ],
        'remote'               => [],
        'flag'                 => FALSE,
        'flag_path'            => '',
        'avatar_path'          => $root_img.$options['image_path'],
        'initial_items'        => $initial_items,
        'strings'              => $options['strings'],
    ];
    $combobox_attribute = form_select_combobox_attribute($combobox_options);

    if ( Defender::inputHasError($input_name) ) {
        $error_class = "has-error ";
        $new_error_text = Defender::getErrorText($input_name);
        if ( ! empty($new_error_text) ) {
            $options['error_text'] = $new_error_text;
        }
        addnotice("danger", $options['error_text']);
    }

    $html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ( $options['inline'] && $label ? 'row ' : '' ) . $error_class . $options['class'] . "' style='width:" . $options['width'] . "'>";

    $html .= ( $label ) ? "<label class='form-label {$options['label_class']}" . ( $options['inline'] ? " col-xs-12 col-sm-12 col-md-3 col-lg-3" : '' ) . "' for='" . $options['input_id'] . "'>" . $label . ( $options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '' ) . "
    " . ( $options['tip'] ? "<i class='pointer fa fa-question-circle' title='" . $options['tip'] . "'></i>" : '' ) . "
    </label>" : '';

    $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>" : "";

    $serialized_value = is_array($input_value) ? implode($options['delimiter'], $input_value) : (string)$input_value;
    $html .= "<input " . ( $options['required'] ? "class='req'" : '' ) . " type='hidden' name='".form_select_escape_attribute($input_name)."' id='" . form_select_escape_attribute($options['input_id']) . "' value='".form_select_escape_attribute($serialized_value)."' data-placeholder='" . form_select_escape_attribute($options['placeholder']) . "' data-dynamics-combobox='".$combobox_attribute."' style='width:" . form_select_escape_attribute($options['inner_width']) . "'" . ( $options['deactivate'] ? ' disabled' : '' ) . "/>";
    if ( $options['deactivate'] ) {
        $html .= form_hidden($input_name, '', $input_value, [ "input_id" => $options['input_id'] ]);
    }

    $html .= $options['stacked'];
    $html .= $options['ext_tip'] ? "<br/><div class='m-t-10 tip'><i>" . $options['ext_tip'] . "</i></div>" : "";
    $html .= Defender::inputHasError($input_name) && ! $options['inline'] ? "<br/>" : "";
    $html .= Defender::inputHasError($input_name) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";

    $html .= $options['inline'] && $label ? "</div>" : '';

    $html .= "</div>";

    Defender::getInstance()->add_field_session([
                                                   'input_name' => clean_input_name($input_name),
                                                   'title'      => $title,
                                                   'id'         => $options['input_id'],
                                                   'type'       => 'dropdown',
                                                   'required'   => $options['required'],
                                                   'safemode'   => $options['safemode'],
                                                   'error_text' => $options['error_text'],
                                               ]);

    load_form_select_combobox_assets();

    return dynamics_render_component_template('form_select', $html);
}

/* Returns Json Encoded Object used in form_select_user */
function user_search($users, $delimiter) {

    $user_opts = [];

    $users = explode($delimiter, $users);

    if ( ! empty($users) ) {
        foreach ( $users as $user ) {
            $result = dbquery("SELECT user_id, user_name, user_avatar, user_level FROM " . DB_USERS . " WHERE user_status=:status AND user_id=:id", [ ':status' => 0, ':id' => $user ]);
            if ( dbrows($result) > 0 ) {
                while ( $udata = dbarray($result) ) {
                    $user_avatar = ! empty($udata['user_avatar']) ? $udata['user_avatar'] : "no-avatar.jpg";
                    $user_name = $udata['user_name'];
                    $user_level = getuserlevel($udata['user_level']);
                    $user_opts[] = [
                        'id'     => $udata['user_id'],
                        'text'   => $user_name,
                        'avatar' => $user_avatar,
                        "level"  => $user_level,
                    ];
                }
            }
        }
    }

    return json_encode($user_opts);
}

/**
 * Searchable hierarchy select.
 * Returns a full hierarchy nested dropdown.
 *
 * @param        $input_name
 * @param string $label
 * @param string $input_value
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
function form_select_tree($input_name, $label, $input_value, array $options, $db, $name_col, $id_col, $cat_col, $self_id = FALSE, $id = FALSE, $level = FALSE, $index = [], $data = FALSE) {

    $html = '';
    $locale = fusion_get_locale();
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $default_options = [
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
        'combobox_disabled' => FALSE,
        'allow_clear'       => FALSE,
        'floating_label'    => FALSE,
        'display_search_count' => 5,
        'ajax'              => [],
        'remote'            => [],
        'flag'              => FALSE,
        'flag_path'         => '',
        'avatar_path'       => '',
        'initial_items'     => [],
        'strings'           => [],
    ];
    $options += $default_options;

    $options['input_id'] = trim($options['input_id'], "[]");
    if ( $options['multiple'] ) {
        if ( $input_value ) {
            $input_value = explode('|', $input_value);
        }
        else {
            $input_value = [];
        }
    }
    if ( ! $options['width'] ) {
        $options['width'] = $default_options['width'];
    }
    $disable_opts = '';
    if ( $options['disable_opts'] ) {
        $disable_opts = is_array($options['disable_opts']) ? $options['disable_opts'] : explode(',', $options['disable_opts']);
    }
    /* Child patern */
    $opt_pattern = str_repeat("&#8212;", $level);

    if ( ! $level ) {
        $level = 0;
        if ( ! isset($index[ $id ]) ) {
            $index[ $id ] = [ '0' => $locale['no_opts'] ];
        }

        $error_class = '';
        if ( Defender::inputHasError($input_name) ) {
            $error_class = "has-error ";
            if ( ! empty($options['error_text']) ) {
                $new_error_text = Defender::getErrorText($input_name);
                if ( ! empty($new_error_text) ) {
                    $options['error_text'] = $new_error_text;
                }
                addnotice("danger", $options['error_text']);
            }
        }

        $html = "<div id='" . $options['input_id'] . "-field' class='form-group " . ( $options['inline'] && $label ? 'row ' : '' ) . $error_class . $options['class'] . "' " . ( $options['inline'] && $options['width'] && ! $label ? "style='width: " . $options['width'] . "'" : '' ) . ">";
        $html .= ( $label ) ? "<label class='control-label " . ( $options['inline'] ? 'col-xs-12 col-sm-12 col-md-3 col-lg-3' : '' ) . "' for='" . $options['input_id'] . "'>" . $label . ( $options['required'] == TRUE ? "<span class='required'>&nbsp;*</span>" : '' ) . " " . ( $options['tip'] ? "<i class='pointer fa fa-question-circle' title=" . $options['tip'] . "></i>" : '' ) . "</label>" : '';
        $html .= $options['inline'] && $label ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>" : "";
    }
    if ( $level == 0 ) {
        $options['combobox_label'] = trim(strip_tags((string)$label));
        $combobox_enabled = $options['combobox_disabled'] === FALSE;
        $combobox_attribute = $combobox_enabled ? " data-dynamics-combobox='".form_select_combobox_attribute($options)."'" : '';
        $html .= "<select class='form-select' name='$input_name' id='" . $options['input_id'] . "' style='width: " . ( ! empty($options['inner_width']) ? $options['inner_width'] : $default_options['inner_width'] ) . "'" . ( $options['deactivate'] ? " disabled" : "" ) . ( $options['multiple'] ? " multiple" : "" ) . $combobox_attribute . ">";
        $html .= $options['allowclear'] ? "<option value=''></option>" : '';
        if ( $options['no_root'] == FALSE ) { // api options to remove root from selector. used in items creation.
            $this_select = '';
            if ( $input_value !== NULL ) {
                if ( $input_value !== '' ) {
                    $this_select = 'selected';
                }
            }
            $html .= ( $options['add_parent_opts'] == TRUE ) ? "<option value='0' " . $this_select . ">$opt_pattern " . $locale['parent'] . "</option>" : "<option value='0' " . $this_select . " >$opt_pattern " . $options['parent_value'] . "</option>";
        }

        $index = dbquery_tree($db, $id_col, $cat_col, $options['query'], $options['full_query']);
        if ( ! empty($index) ) {
            $data = dropdown_select($db, $id_col, $name_col, $cat_col, implode(',', flatten_array($index)), $options['query'], $options['full_query']);
        }
    }

    if ( ! $id ) {
        $id = 0;
    }

    if ( isset($index[ $id ]) && ! empty($data) ) {
        foreach ( $index[ $id ] as $value ) {
            // value is the array
            //$hide = $disable_branch && $value == $self_id ? 1 : 0;
            $name = $data[ $value ][ $name_col ];

            $name = PHPFusion\QuantumFields::parseLabel($name);
            $select = form_select_value_is_selected($input_value, $value) ? 'selected' : '';
            $disabled = $disable_opts && in_array($value, $disable_opts);
            $hide = $disabled && $options['hide_disabled'];
            // do a disable for filter_opts item.
            $html .= ( ! $hide ) ? "<option value='$value' " . $select . " " . ( $disable_opts && in_array($value, $disable_opts) ? 'disabled' : '' ) . " >$opt_pattern $name " . ( $options['show_current'] && $self_id == $value ? '(Current Item)' : '' ) . "</option>" : '';
            if ( isset($index[ $value ]) && ( ! $hide ) ) {
                $html .= form_select_tree($input_name, $label, $input_value, $options, $db, $name_col, $id_col, $cat_col, $self_id, $value, $level + TRUE, $index, $data);
            }
        }
    }
    if ( ! $level ) {
        $html .= "</select>";
        $html .= ( ( $options['required'] == 1 && Defender::inputHasError($input_name) ) || Defender::inputHasError($input_name) ) ? "<div id='" . $options['input_id'] . "-help' class='label label-danger p-5 display-inline-block'>" . $options['error_text'] . "</div>" : "";
        $html .= $options['inline'] && $label ? "</div>" : '';
        $html .= "</div>";
        if ( $options['required'] ) {
            $html .= "<input class='req' id='dummy-" . $options['input_id'] . "' type='hidden'>"; // for jscheck
        }
        $input_name = ( $options['multiple'] ) ? str_replace("[]", "", $input_name) : $input_name;
        Defender::add_field_session(
            [
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
            ]
        );
    }

    if (!$level && empty($options['combobox_disabled'])) {
        load_form_select_combobox_assets();
    }

    return dynamics_render_component_template('form_select', $html);
}

/*
 * Optimized performance by adding a self param to implode to fetch only certain rows
 */
function dropdown_select($db, $id_col, $name_col, $cat_col, $index_values, $filter = '', $query_replace = '') {

    $data = [];
    $query = "SELECT $id_col, $name_col, $cat_col FROM " . $db . " " . ( $filter ? $filter . " AND " : 'WHERE' ) . " $id_col IN ($index_values) ORDER BY $name_col ASC";
    if ( ! empty($query_replace) ) {
        $query = $query_replace;
    }
    $result = dbquery($query);
    while ( $row = dbarray($result) ) {
        $id = $row[ $id_col ];
        $data[ $id ] = $row;
    }

    return $data;
}

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: form_datepicker.php
| Author: Frederick MC Chan (Chan)
| Credits:  eternicode @ http://bootstrap-datepicker.readthedocs.org/en/latest/
| Docs: http://bootstrap-datepicker.readthedocs.org/en/release/options.html
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
 * Input to save date using datepicker
 * Datetimepicker documentation - http://eonasdan.github.io/bootstrap-datetimepicker/Options/
 *
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *                <ul>
 *                <li><strong>class</strong> (string): Empty string by default.
 *                The value of attribute class of the input.</li>
 *                <li><strong>date_format</strong> (string): dd-mm-yyyy by default.
 *                Date format for datepicker plugin.</li>
 *                <li><strong>deactivate</strong> (boolean): FALSE by default.
 *                You can pass TRUE and turn off the javascript datepicker plugin</li>
 *                <li><strong>error_text</strong> (string): empty string by default.
 *                An error message</li>
 *                <li><strong>fieldicon_off</strong> (boolean): FALSE by default.
 *                If TRUE, the calendar icon will be not displayed in the input.</li>
 *                <li><strong>inline</strong> (boolean): FALSE by default.
 *                TRUE if the input should be an inline element.</li>
 *                <li><strong>input_id</strong> (string): $input name by default.
 *                The value of attribute id of input.</li>
 *                <li><strong>required</strong> (boolean): FALSE by default</li>
 *                <li><strong>type</strong> (string): timestamp by default.
 *                Valid types:
 *                <ul>
 *                <li>date: The date will be saved as mysql date.</li>
 *                <li>timestamp: A timestamp will be saved as an integer</li>
 *                </ul>
 *                </li>
 *                <li><strong>week_start</strong> (int): 0 by default.
 *                An integer between 0 and 6. It is the same as
 *                the attribute weekStart of datepicker.</li>
 *                <li><strong>width</strong> (string): 250px by default.
 *                A valid value for CSS width</li>
 *                </ul>
 *
 * Callback Usages !important
 * ==========================
 * Configuration when type is 'timestamp'
 * Token used for $options['date_format_php'] is the <a href="http://php.net/manual/en/function.date.php">PHP token equivalent.</a>
 * Token used for $options['date_format_js'] must be formatted with <a href="http://momentjs.com/docs/#/displaying/">moment.js.</a>
 * Both token must match each other to parse the callback properly.
 *
 * Currently, only user birthdate in `entire project` uses date format.
 *
 * Joining 2 datepickers (Start and End)
 * =======================================
 * In Start Datepicker, add $options['join_to_id'] with End Datepicker's input_id
 * In End Datepicker, add $options['join_from_id'] with Start Datepicker's input_id
 *
 * @return string
 */


function form_datepicker($input_name, $label = '', $input_value = '', array $options = array()) {
    // there was no sanitization?
    $locale = fusion_get_locale();

    if (!defined('DATEPICKER')) {
        define('DATEPICKER', TRUE);
        add_to_head("<link href='".DYNAMICS."assets/datepicker/css/bootstrap-datetimepicker.min.css' rel='stylesheet' />");
        add_to_footer("<script src='".DYNAMICS."assets/datepicker/js/moment.min.js'></script>");

        if (file_exists(DYNAMICS."assets/datepicker/locale/tooltip/".$locale['datepicker'].".js")) {
            $lang = $locale['datepicker'];
        } else {
            $lang = 'en-gb';
        }
        add_to_footer("<script src='".DYNAMICS."assets/datepicker/locale/tooltip/".$lang.".js'></script>");
        add_to_footer("<script src='".DYNAMICS."assets/datepicker/js/bootstrap-datetimepicker.min.js'></script>");
        add_to_footer("<script src='".DYNAMICS."assets/datepicker/locale/".$locale['datepicker'].".js'></script>");

    }

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));

    $input_name = stripinput($input_name);

    $default_options = array(
        'input_id'                => $input_name,
        'required'                => FALSE,
        'placeholder'             => '',
        'deactivate'              => FALSE,
        'width'                   => '',
        'inner_width'             => '250px',
        'class'                   => '',
        'inline'                  => FALSE,
        'error_text'              => $locale['error_input_default'],
        'date_format_js'          => $locale['datepicker_js'],
        'date_format_php'         => $locale['datepicker_php'],
        'delimiter'               => '-',
        'fieldicon_off'           => FALSE,
        'filtered_dates'          => array(), // must be an array
        'include_filtered_dates'  => (boolean)FALSE, // if TRUE, then only days filtered are selectable
        'weekend'                 => array(), // 0 for Sunday, 1 for Monday, 6 for Saturday
        'disable_weekend'         => (boolean)FALSE, // if true, all weekend will be non-selectable
        'type'                    => 'timestamp',
        'tip'                     => '',
        'showTime'                => (boolean)FALSE,
        'week_start'              => fusion_get_settings('week_start'),
        'join_to_id'              => '',
        'join_from_id'            => '',
        'debug'                   => '',
    );

    $options += $default_options;

    if (!empty($input_value)) {
        if ($options['type'] == "timestamp") {
            $input_value = date($options['date_format_php'], isnum($input_value) ? $input_value : strtotime(str_replace('-','/', $input_value)));
        } elseif ($options['type'] == "date") {
            if (stristr($input_value, $options['delimiter'])) {
                $input_value = explode($options['delimiter'], $input_value);
                if (count($input_value) == 3) {
                    $params = array(
                        'year'  => $input_value[0],
                        'month' => $input_value[1],
                        'day'   => $input_value[2]
                    );
                    if (checkdate($params['month'], $params['day'], $params['year'])) {
                        $input_value = (implode("-", $params)." 00:00:00");
                    }
                }
            }
        }
    } else {
        $input_value = "";
    }

    if (!$options['width']) {
        $options['width'] = $default_options['width'];
    }

    // Format disabled or enabled dates as JS array
    $dateFilter = array();
    if (!empty($options['filtered_dates']) && is_array($options['filtered_dates'])) {
        $date_filtered = "";
        $dateFilter[0] = "disabledDates: ";
        if ($options['include_filtered_dates'] == TRUE) {
            $dateFilter[0] = "enabledDates: ";
        }
        foreach ($options['filtered_dates'] as $key => $value) {
            $date_filtered[] = date("m/d/Y", $value);
        }
        $dateFilter[1] = (string)"['".implode("','", $date_filtered)."']";
    }

    // Format for Weekend
    $weekendFilter = array();
    if ($options['disable_weekend']) {
        $weekendFilter[0] = "daysOfWeekDisabled: ";
        $weekendFilter[1] = (!empty($options['weekend']) && is_array($options['weekend'])) ? "[".implode(",", $options['weekend'])."]" : "[0,6]";
    }

    if (!in_array($options['type'], array('date', 'timestamp'))) {
        $options['type'] = $default_options['type'];
    }

    $options['week_start'] = (int)$options['week_start'];

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

    $input_id = $options['input_id'] ?: $default_options['input_id'];
    $html = "<div id='$input_id-field' class='form-group clearfix ".$error_class.$options['class']."'>\n";
    $html .= ($label) ? "<label class='control-label".($options['inline'] ? " col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span> " : '').($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."</label>\n" : '';
    $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : "";
    $html .= "<div class='input-group date'".($options['width'] ? " style='width: ".$options['width']."'" : '').">\n";
    $html .= "<input type='text' name='".$input_name."' id='".$input_id."' value='".$input_value."' class='form-control textbox' style='width:".($options['inner_width'] ? $options['inner_width'] : $default_options['inner_width']).";'".($options['placeholder'] ? " placeholder='".$options['placeholder']."'" : '')."/>\n";
    $html .= "<span class='input-group-addon ".($options['fieldicon_off'] ? 'display-none' : '')."'><i class='fa fa-calendar'></i></span>\n";
    $html .= "</div>\n";
    $html .= ($options['required'] == 1 && \defender::inputHasError($input_name)) || \defender::inputHasError($input_name) ? "<div id='".$input_id."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= $options['inline'] ? "</div>\n" : "";
    $html .= "</div>\n";

    \defender::getInstance()->add_field_session(array(
            'input_name'  => $input_name,
            'type'        => $options['type'],
            'title'       => $title,
            'id'          => $input_id,
            'required'    => $options['required'],
            'safemode'    => TRUE,
            'error_text'  => $options['error_text'],
            "delimiter"   => $options['delimiter'],
            'date_format' => $options['date_format_php'],
    ));

    if (!$options['deactivate']) {

        /**
         * Bind to and from together
         */
        $bindingJs = "";
        if (!empty($options['join_from_id'])) {
            $bindingJs = "
                var fromVal = $('#".$options['join_from_id']."').val();
                var toVal = $('#".$input_id."').val();
                if (fromVal) {
                    $('#$input_id-field .input-group.date').data('DateTimePicker').minDate(fromVal);
                }
                if (toVal) {
                    $('#".$options['join_from_id']."-field .input-group.date').data('DateTimePicker').maxDate(toVal);
                }
                $('#".$options['join_from_id']."-field .input-group.date').on('dp.change', function(e) {
                    $('#$input_id-field .input-group.date').data('DateTimePicker').minDate(e.date);
                });
                $('#$input_id-field .input-group.date').on('dp.change', function(e) {
                    $('#".$options['join_from_id']."-field .input-group.date').data('DateTimePicker').maxDate(e.date);
                });
            ";
        }

        add_to_jquery("
            $('#$input_id-field .input-group.date').datetimepicker({
                locale: moment.locale('".$locale['datepicker']."', {
                week: { dow: ".$options['week_start']." }
            }),
            showTodayButton: true,
            showClear: true,
            showClose: true,
            allowInputToggle: true,
            ".($options['showTime'] == TRUE ? "sideBySide: true," : "")."
            ".(!empty($dateFilter) ? $dateFilter[0].$dateFilter[1]."," : "")."
            ".(!empty($weekendFilter) ? $weekendFilter[0].$weekendFilter[1]."," : "")."
            format: '".$options['date_format_js']."',
            ".(!empty($options['join_from_id']) ? "useCurrent: false" : "")."
            });
            ".$bindingJs."
        ");
    }

    return (string)$html;
}

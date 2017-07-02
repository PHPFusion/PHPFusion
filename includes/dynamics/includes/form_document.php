<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Project File: Form API - Address Input Based
| Filename: form_document.php
| Author: Chubatyj Vitalij (Rizado)
| Co-Author: Chan (Frederick MC Chan)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
function form_document($input_name, $label = '', $input_value = FALSE, array $options = array()) {
    $locale = fusion_get_locale();
    $settings = fusion_get_settings();

    if (!defined('DATEPICKER')) {
        define('DATEPICKER', TRUE);
        add_to_head("<link href='".DYNAMICS."assets/datepicker/css/datepicker3.css' rel='stylesheet' />");
        add_to_head("<script src='".DYNAMICS."assets/datepicker/js/bootstrap-datepicker.js'></script>");
    }

    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    $label = (isset($label) && (!empty($label))) ? $label : "";
    $input_name = (isset($input_name) && (!empty($input_name))) ? stripinput($input_name) : "";
    $input_id = (isset($options['input_id']) && (!empty($options['input_id']))) ? stripinput($options['input_id']) : "";
    // NOTE (remember to parse readback value as of '|' seperator)
    if (isset($input_value) && (!empty($input_value))) {
        if (!is_array($input_value)) {
            $input_value = construct_array($input_value, "", "|");
            if ($input_value[4] != "1900-01-01") {
                $input_value[4] = date('d-m-Y', strtotime($input_value[4]));
            } else {
                $input_value[4] = "";
            }
            if ($input_value[5] != "1900-01-01") {
                $input_value[5] = date('d-m-Y', strtotime($input_value[5]));
            } else {
                $input_value[5] = "";
            }
        }
    } else {
        $input_value['0'] = "";
        $input_value['1'] = "";
        $input_value['2'] = "";
        $input_value['3'] = "";
        $input_value['4'] = "";
        $input_value['5'] = "";
    }

    $options += array(
        'required'     => FALSE,
        'placeholder'  => '',
        'deactivate'   => FALSE,
        'width'        => '100%',
        'class'        => '',
        'inline'       => '',
        'tip'          => '',
        'error_text'   => !empty($options['error_text']) ? $options['error_text'] : $locale['doc_type_error'],
        'error_text_2' => !empty($options['error_text_2']) ? $options['error_text_2'] : $locale['doc_series_error'],
        'error_text_3' => !empty($options['error_text_3']) ? $options['error_text_3'] : $locale['doc_number_error'],
        'error_text_4' => !empty($options['error_text_4']) ? $options['error_text_4'] : $locale['doc_authority_error'],
        'error_text_5' => !empty($options['error_text_5']) ? $options['error_text_5'] : $locale['doc_issue_error'],
        'error_text_6' => !empty($options['error_text_6']) ? $options['error_text_6'] : '',
        'safemode'     => FALSE,
        'date_format'  => !empty($options['date_format']) ? $options['date_format'] : 'dd-mm-yyyy',
        'week_start'   => !empty($options['week_start']) && isnum($options['week_start']) ? $options['week_start'] : isset($settings['week_start']) && isnum($settings['week_start']) ? $settings['week_start'] : 0
    );

    $error_key = array(
        0 => $options['error_text'],
        1 => $options['error_text_2'],
        2 => $options['error_text_3'],
        3 => $options['error_text_4'],
        4 => $options['error_text_5'],
        5 => $options['error_text_6'],
    );

    $error_class = "";
    for ($i = 0; $i <= 5; $i++) {
        if (\defender::inputHasError($input_name[$i])) {
            $error_class = "has-error ";
            addNotice("danger", "<strong>$title</strong> - ".$error_key[$i]);
        }
    }

    $html = "<div id='$input_id-field' class='form-group clearfix ".($options['inline'] ? 'display-block overflow-hide ' : '').$error_class.$options['class']."' >\n";
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-3 col-md-3 col-lg-3 p-l-0" : '')."' for='$input_id'>".$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')."
	".($options['tip'] ? "<i class='pointer fa fa-question-circle' title='".$options['tip']."'></i>" : '')."
	</label>\n" : '';
    $html .= $options['inline'] ? "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9'>\n" : '';
    $html .= "<div class='row'>\n";
    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_type' value='".$input_value['0']."' placeholder='".$locale['doc_type'].($options['required'] ? ' *' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[0])) || \defender::inputHasError($input_name[0])) ? "<div id='".$input_id."-doc_type-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-4 col-sm-4 col-md-4 col-lg-3 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_series' value='".$input_value['1']."' placeholder='".$locale['doc_series'].($options['required'] ? ' *' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[1])) || \defender::inputHasError($input_name[1])) ? "<div id='".$input_id."-doc_series-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-8 col-sm-8 col-md-8 col-lg-6 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_number' value='".$input_value['2']."' placeholder='".$locale['doc_number'].($options['required'] ? ' *' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[2])) || \defender::inputHasError($input_name[2])) ? "<div id='".$input_id."-doc_number-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12 m-b-10'>\n";
    $html .= "<input type='text' name='".$input_name."[]' class='form-control' id='".$input_id."-doc_authority' value='".$input_value['3']."' placeholder='".$locale['doc_authority'].($options['required'] ? ' *' : '')."' ".($options['deactivate'] == "1" ? "readonly" : '')." />\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[3])) || \defender::inputHasError($input_name[3])) ? "<div id='".$input_id."-doc_authority-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n";
    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
    $html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
    $html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-doc_date_issue' value='".$input_value[4]."' class='form-control textbox' placeholder='".$locale['doc_date_issue'].($options['required'] ? ' *' : '')."' />\n";
    $html .= "<span class='input-group-addon '><i class='fa fa-calendar'></i></span>\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[4])) || \defender::inputHasError($input_name[4])) ? "<div id='".$input_id."-doc_issue-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n</div>\n";
    $html .= "<div class='col-xs-12 col-sm-12 col-md-12 col-lg-5'>\n";
    $html .= "<div class='input-group date' ".($options['width'] ? "style='width:".$options['width'].";'" : '').">\n";
    $html .= "<input type='text' name='".$input_name."[]' id='".$input_id."-doc_date_expire' value='".$input_value[5]."' class='form-control textbox' placeholder='".$locale['doc_date_expire']."' />\n";
    $html .= "<span class='input-group-addon '><i class='fa fa-calendar'></i></span>\n";
    $html .= (($options['required'] == 1 && \defender::inputHasError($input_name[5])) || \defender::inputHasError($input_name[5])) ? "<div id='".$input_id."-doc_expire-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div>" : "";
    $html .= "</div>\n</div>\n";
    $html .= "</div>\n"; // close inner row
    $html .= ($options['inline']) ? "</div>\n" : "";
    $html .= "</div>\n";
    \defender::getInstance()->add_field_session(array(
                                     'input_name' => $input_name,
                                     'type'       => 'document',
                                     'label'      => $title,
                                     'id'         => $input_id,
                                     'required'   => $options['required'],
                                     'safemode'   => $options['safemode'],
                                     'error_text' => $options['error_text']
                                 ));
    if ($options['deactivate'] !== 1) {
        add_to_jquery("
            $('#$input_id-field .input-group.date').datepicker({
            format: '".$options['date_format']."',
            todayBtn: 'linked',
            autoclose: true,
            weekStart: ".$options['week_start'].",
            todayHighlight: true
            });
        ");
    }

    return $html;
}

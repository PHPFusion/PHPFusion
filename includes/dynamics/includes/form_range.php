<?php

/**
 * @param        $input_name
 * @param string $label
 * @param string $input_value
 * @param array  $options
 *
 * @return string
 */
function form_range($input_name, $label = "", $input_value = "", array $options = []) {
    
    $locale = fusion_get_locale();
    
    $title = $label ? stripinput($label) : ucfirst(strtolower(str_replace("_", " ", $input_name)));
    
    $input_id = trim(str_replace("[", "-", $input_name), "]");
    
    $input_value = clean_input_value($input_value);
    
    $default_options = [
        'type'            => 'text',
        'required'        => FALSE,
        'label_icon'      => '',
        'safemode'        => FALSE,
        'callback_check'  => FALSE,
        'input_id'        => $input_id,
        'placeholder'     => '',
        'deactivate'      => FALSE,
        'width'           => '',
        'inner_width'     => '',
        'class'           => '',
        'inner_class'     => '',
        'inline'          => FALSE,
        'min'             => 1,
        'max'             => 2,
        'step'            => 1,
        'icon'            => '',
        'tip'             => '',
        'ext_tip'         => '',
        'error_text'      => '',
        'delimiter'       => ',',
        'stacked'         => '',
        'data'            => [],
        'descript'        => TRUE,
        'append_html'     => '',
        'display_percent' => FALSE,
        'range_buttons'   => FALSE,
    ];
    
    $options += $default_options;
    
    $options['type'] = 'number';
    
    if (!empty($options['data'])) {
        array_walk($options['data'], function ($a, $b) use (&$options_data) {
            $options_data[] = "data-$b='$a'";
        }, $options_data);
    }
    
    // Error messages based on settings
    $options['error_text'] = empty($options['error_text']) ? $locale['error_input_default'] : $options['error_text'];
    
    $error_class = "";
    if (\Defender::inputHasError($input_name)) {
        $error_class = " has-error";
        if (!empty($options['error_text'])) {
            $new_error_text = \Defender::getErrorText($input_name);
            if (!empty($new_error_text)) {
                $options['error_text'] = $new_error_text;
            }
            
            addnotice("danger", $options['error_text']);
        }
    }
    
    $min = ((!empty($options['min']) || $options['min'] === "0") && isnum($options['min']) ? "min='".$options['min']."' " : '');
    
    $max = ((!empty($options['max']) || $options['max'] === "0") && isnum($options['max']) ? "max='".$options['max']."' " : '');
    
    $step = $options['step'] ? "step='".$options['step']."' " : '';
    
    $html = "<div id='".$options['input_id']."-field' class='form-group ".($options['inline'] && $label ? 'row ' : '').($error_class ?? '').($options['class'] ? ' '.$options['class'] : '').($options['icon'] ? ' has-feedback' : '')."'".($options['width'] && !$label ? " style='width: ".$options['width']."'" : '').">";
    
    $html .= ($label) ? "<label class='control-label ".($options['inline'] ? "col-xs-12 col-sm-12 col-md-3 col-lg-3" : '')."' for='".$options['input_id']."'>".$options['label_icon'].$label.($options['required'] ? "<span class='required'>&nbsp;*</span>" : '')." ".($options['tip'] ? "<i class='pointer far fa-question-circle' data-toggle='tooltip' title='".$options['tip']."'></i>" : '')."</label>" : '';
    
    $html .= ($options['inline'] && $label ? "<div class='col-xs-12 col-sm-12 col-md-9 col-lg-9'>" : "");
    
    $html .= "<input type='range' ".(!empty($options_data) ? implode(' ', $options_data) : '')." ".$min.$max.$step."class='form-range ".($options['inner_class'] ? " ".$options['inner_class']." " : '')."' ".($options['inner_width'] ? "style='width:".$options['inner_width'].";'" : '')." name='".$input_name."' id='".$options['input_id']."' value='".$input_value."'".($options['placeholder'] ? " placeholder='".$options['placeholder']."' " : '')."".($options['autocomplete_off'] ? " autocomplete='off'" : '')." ".($options['deactivate'] ? 'readonly' : '').">";
    
    $html .= "<div class='form-range-pct'><div id='".$options['input_id']."_pct' class='range-text'>0%</div></div>";
    
    if ($options['max'] - $options['min'] && $options['range_buttons']) {
        
        $range = [
            ($options['max'] * 25 / 100),
            ($options['max'] * 50 / 100),
            ($options['max'] * 70 / 100),
            ($options['max'] * 100 / 100),
        ];
        
        $html .= '<div class="flex flex-row">
        <button type="button" data-value="'.$range[0].'" class="btn btn-xs btn-range btn-default">25%</button>
        <button type="button" data-value="'.$range[1].'" class="btn btn-xs btn-range btn-default">50%</button>
        <button type="button" data-value="'.$range[2].'" class="btn btn-xs btn-range btn-default">75%</button>
        <button type="button" data-value="'.$range[3].'" class="btn btn-xs btn-range btn-default">Max</button>
        </div>';
        
        add_to_jquery("
        let slider_".$options['input_id']." = document.querySelector('#".$options['input_id']."'),
        pct_".$options['input_id']." = document.querySelector('#".$options['input_id']."_pct');
        
        
        slider_".$options['input_id'].".oninput = () => {
            let val = slider_".$options['input_id'].".value,
            percent = Math.round(val / ".$options['max']." * 100) +'%';
            pct_".$options['input_id'].".textContent = ".($options['display_percent'] ? 'percent' : 'val').";
        };
        
        $('.btn-range').on('click', function(e) {
            let percent = $(this).data('value');
            slider_".$options['input_id'].".value = percent;
            pct_".$options['input_id'].".textContent = percent;
        });
        ");
        
    }
    
    
    add_to_jquery("");
    
    
    $html .= $options['stacked'];
    
    $html .= $options['ext_tip'] ? "<br/>\n<span class='tip'><i>".$options['ext_tip']."</i></span>" : "";
    
    $html .= (\Defender::inputHasError($input_name) ? "<div class='input-error".((!$options['inline'] || $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value']) ? " display-block" : "")."'><div id='".$options['input_id']."-help' class='label label-danger p-5 display-inline-block'>".$options['error_text']."</div></div>" : "");
    
    $html .= $options['append_html'];
    
    $html .= (($options['inline'] && $label) ? "</div>" : '');
    
    $html .= "</div>";
    
    // Add input settings in the SESSION
    \Defender::add_field_session([
        'input_name'     => clean_input_name($input_name),
        'title'          => clean_input_name($title),
        'id'             => $options['input_id'],
        'type'           => 'number',
        'required'       => $options['required'],
        'safemode'       => $options['safemode'],
        'regex'          => $options['regex'],
        'callback_check' => $options['callback_check'],
        'delimiter'      => $options['delimiter'],
        'descript'       => $options['descript']
    ]);
    
    // Live Regex Error Check
    //if ($options['regex'] && $options['regex_error_text']) {
    //
    //    add_to_jquery("
    //    $('#".$options['input_id']."').blur(function(ev) {
    //        var Inner_Object = $(this).parent('div').find('.label-danger');
    //        var Outer_Object = $(this).parent('div').find('.input-error');
    //        if (!$(this).val().match(/".$options['regex']."/g) && $(this).val()) {
    //            var ErrorText = '".$options['regex_error_text']."';
    //            var ErrorDOM = '<div class=\'input-error spacer-xs\'><div class=\'label label-danger p-5\'>'+ ErrorText +'</div></div>';
    //            if (Inner_Object.length > 0) {
    //                object.html(ErrorText);
    //            } else {
    //                $(this).after(function() {
    //                    return ErrorDOM;
    //                });
    //            }
    //        } else {
    //           Outer_Object.remove();
    //        }
    //    });
    //    ");
    //}
    //
    return $html;
}

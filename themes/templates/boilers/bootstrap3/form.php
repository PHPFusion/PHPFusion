<?php

use PHPFusion\Template;

class Form {
    
    /**
     * Renders form input
     *
     * @param $input_name
     * @param $label
     * @param $input_value
     * @param $options
     *
     * @return string
     */
    public static function form_input( $input_name, $label, $input_value, $options ) {
        
        $tpl = Template::getInstance( 'field-'.$options['input_id'] );
        $tpl->set_template( __DIR__.DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.'form_input.html' );
        $tpl->set_tag( "input_name", $input_name );
        
        // input id
        $tpl->set_tag( "input_name", $input_name );
        $tpl->set_tag( "input_id", $options['input_id'] );
        $tpl->set_tag( "input_type", $options['type'] );
        // form-group css class
        $grp_class = ( $options['class'] ? ' '.$options['class'] : '' );
        $grp_class .= ( $options['inline'] ? ' clearfix' : '' );
        $grp_class .= ( !empty( $options['icon'] ) ? ' has-feedback' : '' );
        $grp_class .= ( $options['error_class'] ? ' '.$options['error_class'] : '' );
        
        $tpl->set_tag( "group_class", $grp_class );
        
        // form-group css style
        $grp_inline = ( $options['width'] && !$label ? ' style="width: '.$options['width'].'"' : '' );
        $tpl->set_tag( "group_inline_css", $grp_inline );
        
        $is_inline = $options['inline'] && $label ? TRUE : FALSE;
        // i want it false, then no need to push a grid.
        
        if ( $label ) {
            $control_label = [
                'label_grid'     => ( $is_inline ? ' {[col(100,100,20,20)]}' : ' display-block' ),
                'label_icon'     => ( $options['label_icon'] ) ?: '',
                'label_text'     => $label,
                'label_required' => $options['required'] ? '<span class="required">*</span>' : '',
                'label_tip'      => ( $options['tip'] ? ' <i class="pointer fa fa-question-circle" title="'.$options['tip'].'"></i>' : '' )
            ];
            $tpl->set_block( 'control_label', $control_label );
        }
        
        if ( $is_inline ) {
            $tpl->set_block( 'inline_start' );
            $tpl->set_block( 'inline_end' );
        }
        
        $is_text_type = ( in_array( $options['type'], [ 'text', 'number', 'email', 'url', 'password' ] ) ? TRUE : FALSE );
        $is_grouped = FALSE;
        
        if ( $is_text_type ) {
            $is_grouped = ( $options['append_button'] || $options['prepend_button'] || $options['append_value'] || $options['prepend_value'] ) ? TRUE : FALSE;
            if ( $is_grouped ) {
                $tpl->set_block( 'group_start', [
                    'group_size'  => ( $options['group_size'] ? ' input-group-'.$options['group_size'] : '' ),
                    'group_width' => ( $options['width'] ? ' style="width:'.$options['width'].'"' : '' )
                ] );
                $tpl->set_block( 'group_end' );
            }
            $is_prepend_button = $options['prepend_button'] && $options['prepend_type'] && $options['prepend_form_value'] && $options['prepend_class'] && $options['prepend_value'] ? TRUE : FALSE;
            $is_append_button = ( $options['append_button'] && $options['append_type'] && $options['append_form_value'] && $options['append_class'] && $options['append_value'] ? TRUE : FALSE );
            if ( $is_prepend_button ) {
                $tpl->set_block( 'input_prepend_button', [
                    'prepend_id'    => $options['prepend_button_id'],
                    'prepend_name'  => $options['prepend_button_name'],
                    'prepend_type'  => $options['prepend_type'],
                    'prepend_value' => $options['prepend_form_value'],
                    'prepend_size'  => $options['prepend_size'] ? ' '.$options['prepend_size'] : '',
                    'prepend_class' => $options['prepend_class'] ? ' '.$options['prepend_class'] : '',
                    'prepend_text'  => $options['prepend_value'],
                ] );
            } else if ( $options['prepend_value'] ) {
                $tpl->set_block( 'input_prepend', [
                    'prepend_id'   => $options['prepend_id'],
                    'prepend_text' => $options['prepend_value'],
                ] );
            }
            
            if ( $is_append_button ) {
                $tpl->set_block( 'input_append_button', [
                    'prepend_id'    => $options['append_button_id'],
                    'prepend_name'  => $options['append_button_name'],
                    'prepend_type'  => $options['append_type'],
                    'prepend_value' => $options['append_form_value'],
                    'prepend_size'  => $options['append_size'] ? ' '.$options['append_size'] : '',
                    'prepend_class' => $options['append_class'] ? ' '.$options['append_class'] : '',
                    'prepend_text'  => $options['append_value'],
                ] );
            } else if ( $options['append_value'] ) {
                $tpl->set_block( 'input_append', [
                    'prepend_id'   => $options['append_id'],
                    'prepend_text' => $options['append_value'],
                ] );
            }
            // Type Text Field
            $tpl->set_block( 'input_text', [
                'input_data'   => $options['options_data'] ? implode( ' ', $options['options_data'] ) : '',
                'min'          => $options['min'],
                'max'          => $options['max'],
                'step'         => $options['step'],
                'inner_class'  => ( $options['inner_class'] ? " ".$options['inner_class']." " : '' ),
                'inner_width'  => ( $options['inner_width'] ? " style='width:".$options['inner_width'].";'" : '' ),
                'max_length'   => $options['max_length'],
                'input_value'  => $input_value,
                'placeholder'  => ( $options['placeholder'] ? $options['placeholder'] : '' ),
                'autocomplete' => ( $options['autocomplete_off'] ? ' autocomplete="off"' : '' ),
                'readonly'     => $options['deactivate'] ? ' readonly' : '',
                'required'     => $options['required'] ? ' required' : '',
                'pwstrength'   => $options['password_strength'] ? '<div class="pwstrength_viewport_progress"></div>' : '' // do this for external plugin
            ] );
            
            if ( $options['feedback_icon'] && $options['icon'] ) {
                $tpl->set_block( "feedback", [
                    'icon' => $options['icon']
                ] );
            }
            
        } else if ( $options['type'] == 'dropdown' ) {
            
            $config = [
                'input_name'     => $input_name,
                'input_id'       => $options['input_id'],
                'input_class'    => $options['input_class'],
                'input_width'    => $options['input_width'],
                'readonly'       => $options['deactivate'] ? ' disabled' : '',
                'onchange'       => $options['onchange'] ? ' onchange="'.$options['onchange'].'"' : '',
                'multiple'       => $options['multiple'] ? ' multiple' : '',
                'class'          => $options['select2_disabled'] == TRUE ? " class='form-control'" : "",
                'allowclear'     => $options['allowclear'],
                'parent_opts'    => $options['parent_opts'],
                'options'        => $options['options_options'],
                'required_input' => $options['dropdown_required_input'],
                'required'       => $options['required'] ? ' required' : '',
            ];
            
            $tpl->set_block( 'input_dropdown', $config );
            
        } else if ( $options['type'] == 'custom' ) {
            $tpl->set_block( 'input_custom', [
                'input_field' => $options['input_field']
            ] );
        }
        
        
        if ( $options['stacked'] ) {
            $tpl->set_block( "stacked", [ 'content' => $options['stacked'] ] );
        }
        if ( $options['ext_tip'] ) {
            $tpl->set_block( "tip", [ 'tip_text' => $options['ext_tip'] ] );
        }
        
        if ( \Defender::inputHasError( $input_name ) ) {
            $tpl->set_block( "error_message", [
                'error_class' => ( !$is_inline or $is_grouped ? ' display-block' : '' ),
                'input_id'    => $options['input_id'],
                'error_text'  => $options['error_text']
            ] );
        }
        
        if ( !empty( $options['append_html'] ) ) {
            $tpl->set_block( "append_html", [ 'content' => $options['append_html'] ] );
        }
        
        return $tpl->get_output();
    }
    
    /**
     * Checkbox, Radio, Toggle Switch, Toggle Button
     *
     * @param $input_name
     * @param $label
     * @param $input_value
     * @param $options
     *
     * @return string
     * @throws ReflectionException
     */
    public static function form_checkbox( $input_name, $label, $input_value, $options ) {
    
        //print_p($options);
        // support inline if there are multiple options only.
        $template = '
        <div id="{%input_id%}-field" class="{%input_class%} clearfix">
            <label {%label_class%}for="{%input_id%}" data-checked="{%data_value%}"{%style%}>
            {%pre_checkbox%}
            {%label%}
            {%post_checkbox%}
            </label>
            {stacked.{
            <!--fusion stacked information-->{%content%}
            }}
            {ext_tip.{
            <br/><span class="tip"><i>{%tip_text%}</i></span>
            }}
            {error_message.{
            <div class="input-error{%error_class%}">
                <div id="{%input_id%}-help" class="label label-danger p-5 display-inline-block">{%error_text%}</div>
            </div>
            }}
        </div>
        ';
        
        $button_template = '
        <span class="button-checkbox">
        <button type="button" class="btn btn-{%button_class%} {%class%}" data-color="{%button_class%}">'.$label.'</button>
        <input name="{%input_name%}" id="{%input_id%}" type="checkbox" value="{%input_value%}" class="hidden">
        </span>
        ';
        
        //print_p($options);
        if ( \Defender::inputHasError( $input_name ) ) {
            $wrapper_class[] = "has-error ";
            if ( !empty( $options['error_text'] ) ) {
                $new_error_text = \Defender::getErrorText( $input_name );
                if ( !empty( $new_error_text ) ) {
                    $options['error_text'] = $new_error_text;
                }
                //addNotice("danger", "<strong>$title</strong> - ".$options['error_text']);
            }
        }
        
        $tpl = Template::getInstance( 'field-'.$options['input_id'] );
        
        $tpl->set_text( $template );
        
        $tpl->set_tag( 'style', '' );
    
        if ( $options['type'] == 'button' ) {
            
            $tpl->set_text( $button_template );
            $tpl->set_tag( 'button_class', $options['button_class'] );
            $tpl->set_tag( 'class', $options['class'] );
            if ( !defined( 'btn-checkbox-js' ) ) {
                define( 'btn-checkbox-js', TRUE );
                add_to_jquery( "
        	$('.button-checkbox').each(function () {
            // Settings
            var widget = $(this),
            button = widget.find('button'),
            checkbox = widget.find('input:checkbox'),
            color = button.data('color'),
            settings = {
                on: {
                    icon: 'glyphicon glyphicon-check fa-fw'
                },
                off: {
                    icon: 'glyphicon glyphicon-unchecked fa-fw'
                }
            };
        // Event Handlers
        button.on('click', function () {
            checkbox.prop('checked', !checkbox.is(':checked'));
            checkbox.triggerHandler('change');
            updateDisplay();
        });
        checkbox.on('change', function () {
            updateDisplay();
        });
        // Actions
        function updateDisplay() {
            var isChecked = checkbox.is(':checked');
            // Set the button's state
            button.data('state', (isChecked) ? \"on\" : \"off\");
            // Set the button's icon
            button.find('.state-icon').removeClass().addClass('state-icon ' + settings[button.data('state')].icon);
            // Update the button's color
            if (isChecked) {
                button.removeClass('btn-default').addClass('' + color + ' active');
            } else {
                button.removeClass('' + color + ' active').addClass('btn-default');
            }
        }
        // Initialization
        function init() {
            updateDisplay();
            // Inject the icon if applicable
            if (button.find('.state-icon').length == 0) {
                button.prepend('<i class=\"state-icon ' + settings[button.data('state')].icon + ' \"></i>');
            }
        }
        init();
        });
        " );
            }
            
        } else {
            
            // calculate all possible class
            $wrapper_class[] = $options['type'] == 'radio' ? 'radio' : 'checkbox';
            $wrapper_class[] = $options['class'];
            if ( $options['inline'] ) {
                $wrapper_class[] = 'display-block overflow-hide';
            }
            
            $tpl->set_tag( "input_class", implode( ' ', $wrapper_class ) );
            
            if ( $options['inline'] ) {
                $tpl->set_tag( 'label_class', ' '.grid_column_size( 100, 100, 25, 25 ) );
            }
            
            $tpl->set_tag( 'data_value', ( !empty( $input_value ) ? 1 : 0 ) );
            
            if ( $options['inner_width'] ) {
                $tpl->set_tag( 'style', " style='width:".$options['inner_width']."'" );
            }
            
            if ( !empty( $label ) ) {
                if ( $options['required'] ) {
                    $label = $label.'<span class="required">&nbsp;*</span>';
                }
                if ( $options['tip'] ) {
                    $label = $label.'<i class="pointer fa fa-question-circle text-lighter" title="{%title%}"></i>';
                }
            }
            
            if ( !empty( $options['ext_tip'] ) ) {
                $tpl->set_block( 'ext_tip', [ 'tip_text' => $options['ext_tip'] ] );
            }
            
            if ( \Defender::inputHasError( $input_name ) ) {
                $tpl->set_block( "error_message", [
                    'error_class' => ( !$options['inline'] ? ' display-block' : '' ),
                    'input_id'    => $options['input_id'],
                    'error_text'  => $options['error_text']
                ] );
            }
            
            if ( $options['stacked'] ) {
                $tpl->set_block( 'stacked', $options['stacked'] );
            }
            
            $on_label = $options['toggle_text'][1];
            $off_label = $options['toggle_text'][0];
            
            if ( $options['keyflip'] ) {
                $on_label = $options['toggle_text'][0];
                $off_label = $options['toggle_text'][1];
            }
    
            $label_class[] = 'control-label';
            $label_class[] = $options['class'];
            $prepend = "";
            $append = "";
            if ( !$options['reverse_label'] ) {
                $label_class[] = 'p-l-0';
                $prepend = "<span class='m-t-5 m-l-30'>";
                $append = "</span>";
            }
            
            
            $checkbox = $prepend."<input id='".$options['input_id']."' ".( $options['toggle'] ? "data-on-text='".$on_label."' data-off-text='".$off_label."'" : "" )." name='$input_name' value='".$options['value']."' type='".$options['type']."'".( $options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '' ).( $input_value == $options['value'] ? ' checked' : '' ).( $options['deactivate'] ? ' disabled' : '' ).">".$append;
            
            if ( !empty( $options['options'] ) && is_array( $options['options'] ) ) {
                $options['toggle'] = FALSE; // force toggle to be false if options existed
                $default_checked = FALSE;
                
                if ( !empty( $input_value ) ) {
                    if ( is_array( $input_value ) ) {
                        $option_value = $input_value;
                    } else {
                        $option_value = array_flip( explode( $options['delimiter'], (string)$input_value ) ); // require key to value
                    }
                }
                // for checkbox only
                // if there are options, and i want the options to be having input value.
                // options_value
                if ( $options['type'] == 'checkbox' && count( $options['options'] ) > 1 ) {
                    $input_value = [];
                    $default_checked = empty( $option_value ) ? TRUE : FALSE;
                    foreach ( array_keys( $options['options'] ) as $key ) {
                        $input_value[ $key ] = isset( $option_value[ $key ] ) ? ( !empty( $options['options_value'][ $key ] ) ? $options['options_value'][ $key ] : 1 ) : 0;
                    }
                }
                
                $checkbox = '';
    
                if ( $options['inline'] ) {
                    
                    $class_a = grid_column_size( 100, 100, 75, 75 );
                    $class_b = grid_column_size( 100, 100, 25, 25 );
                    
                    $col_a = $class_a;
                    $col_b = $class_b;
                    if ( $options['reverse_label'] ) {
                        $col_a = $class_b;
                        $col_b = $class_a;
                    }
                    
                    $checkbox .= "<div class='$col_a'>\n";
                    $label = "<div class='$col_b'>$label</div>";
                    $label_class[] = 'display-block';
                }
    
                foreach ( $options['options'] as $key => $value ) {
                    if ( $options['deactivate_key'] !== NULL && $options['deactivate_key'] == $key ) {
                        $checkbox .= form_hidden( $input_name, '', $key );
                    }
                    $checked = ( $options['deactivate'] || $options['deactivate_key'] === $key ? 'disabled' : '' ).( $options['onclick'] ? ' onclick="'.$options['onclick'].'"' : '' );
                    if ( $options['type'] == 'checkbox' && count( $options['options'] ) > 1 ) {
                        $checked = ( $input_value[ $key ] == TRUE || $default_checked && $key == FALSE ? ' checked' : '' );
                    } else {
                        $checked .= ( $input_value == $key || $default_checked && $key == FALSE ? ' checked' : '' );
                    }
                    $inner_width = '';
                    if ( $options['inner_width'] ) {
                        $inner_width = " style='width: ".$options['inner_width']." '";
                    }
    
                    $checkbox .= "<div class='".( $options['inline_options'] ? 'display-inline-block m-r-5' : 'm-b-10' )."'>\n";
                    $checkbox .= "<label class='m-r-20' data-label='$key' for='".$options['input_id']."-$key'$inner_width>";
                    $checkbox .= "<input id='".$options['input_id']."-$key' name='$input_name' value='$key' type='".$options['type']."' $checked />\n";
                    $checkbox .= $value;
                    $checkbox .= "</label>\n";
                    $checkbox .= "</div>\n";
                }
    
                if ( $options['inline'] ) {
                    $checkbox .= "</div>\n";
                }
            }
        
            //print_P($checkbox);
            $tpl->set_tag( 'label', $label );
            $tpl->set_tag( 'label_class', '' );
            if ( !empty( $label_class ) ) {
                $tpl->set_tag( 'label_class', 'class="'.implode( ' ', $label_class ).'" ' );
            }
            
            $tpl->set_tag( 'post_checkbox', $checkbox );
            $tpl->set_tag( 'pre_checkbox', '' );
            if ( $options['reverse_label'] ) {
                $tpl->set_tag( 'post_checkbox', '' );
                $tpl->set_tag( 'pre_checkbox', $checkbox );
            }
        }
        
        $tpl->set_tag( "input_name", $input_name );
        $tpl->set_tag( "input_id", $options['input_id'] );
        $tpl->set_tag( "input_type", $options['type'] );
        
        return $tpl->get_output();
    }
}

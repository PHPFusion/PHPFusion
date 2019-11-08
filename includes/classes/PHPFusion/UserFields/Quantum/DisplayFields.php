<?php
namespace PHPFusion\UserFields\Quantum;

use PHPFusion\Geomap;
use PHPFusion\UserFieldsQuantum;

class DisplayFields {
    
    private $class = NULL;
    
    public function __construct( UserFieldsQuantum $class ) {
        $this->class = $class;
    }
    
    /**
     * Display fields for each fieldDB record entry
     *
     * @param array  $data   The array of the user field.
     * @param        $callback_data
     * @param string $method input or display. In case of any other value
     *                       the method return FALSE. See the description of return for more details.
     * @param array  $options
     *                       <ul>
     *                       <li><strong>deactivate</strong> (boolean): FALSE by default.
     *                       disable fields</li>
     *                       <li><strong>debug</strong> (bolean): FALSE by default.
     *                       Show some information to debug.</li>
     *                       <li><strong>encrypt</strong> (boolean): FALSE by default.
     *                       encrypt field names</li>
     *                       <li><strong>error_text</strong> (string): empty string by default.
     *                       sets the field error text</li>
     *                       <li><strong>hide_value</strong> (boolean): FALSE by default.
     *                       input value is not shown on fields render</li>
     *                       <li><strong>inline</strong> (boolean): FALSE by default.
     *                       sets the field inline</li>
     *                       <li><strong>required</strong> (boolean): FALSE by default.
     *                       input must be filled when validate</li>
     *                       <li><strong>show_title</strong> (boolean): FALSE by default.
     *                       display field label</li>
     *                       <li><strong>placeholder</strong> (string): empty string by default.
     *                       helper text in field value</li>
     *                       <li><strong>plugin_folder</strong> (string): INCLUDES.'user_fields/' by default
     *                       The folder's path where the field's source files are.</li>
     *                       <li><strong>plugin_locale_folder</strong> (string): LOCALE.LOCALESET.'/user_fields/' by
     *                       default. The folder's path where the field's locale files are.</li>
     *                       </ul>
     *
     * @return array|bool|string
     *                       <ul>
     *                       <li>FALSE on failure</li>
     *                       <li>string if $method 'display'</li>
     *                       <li>array if $method is 'input'</li>
     *                       </ul>
     * @throws \ReflectionException
     */
    public function displayFields( array $data, $callback_data, $method = 'input', array $options = [] ) {
        
        unset( $callback_data['user_algo'] );
        unset( $callback_data['user_salt'] );
        unset( $callback_data['user_password'] );
        unset( $callback_data['user_admin_algo'] );
        unset( $callback_data['user_admin_salt'] );
        unset( $callback_data['user_admin_password'] );
        
        $data += [
            'field_required' => TRUE,
            'field_error'    => '',
            'field_default'  => ''
        ];
        
        $default_options = [
            'hide_value'  => FALSE,
            'encrypt'     => FALSE,
            'show_title'  => $method == "input" ? TRUE : FALSE,
            'deactivate'  => FALSE,
            'inline'      => FALSE,
            'error_text'  => $data['field_error'],
            'required'    => (bool)$data['field_required'],
            'placeholder' => $data['field_default'],
            'debug'       => FALSE
        ];
        
        $options += $default_options;
        
        // Sets callback data automatically.
        $option_list = $data['field_options'] ? explode( ',', $data['field_options'] ) : [];
        
        // Format Callback Data
        $field_value = isset( $callback_data[ $data['field_name'] ] ) ? $callback_data[ $data['field_name'] ] : '';
        
        $field_post_value = post( $data['field_name'] );
        
        if ( $field_post_value && !$options['hide_value'] ) {
            $field_value = $field_post_value;
        }
        
        if ( $options['hide_value'] ) {
            $field_value = '';
        }
    
        $field_label = $options['show_title'] ? fusion_parse_locale( $data['field_title'] ).':' : '';
        
        switch ( $data['field_type'] ) {
            
            case 'file':
                $user_data = $callback_data;
                $profile_method = $method;
                // missing
                
                if ( file_exists( $options['field_file'] ) ) {
                    // can access to $user_data;
                    // can access to $profile_method
                    include $options['field_file'];
                    
                    if ( $method == 'input' ) {
                        
                        if ( isset( $user_fields ) ) {
                            return $user_fields;
                        }
                        
                    } else if ( $method == 'display' && !empty( $user_fields['value'] ) ) {
                        
                        return $user_fields;
                    }
                }
                
                unset( $user_data );
                
                unset( $profile_method );
                
                unset( $locale );
                
                break;
            case 'textbox':
                if ( $method == 'input' ) {
                    return form_text( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'number':
                if ( $method == 'input' ) {
                    $options += [ 'type' => 'number' ];
                    
                    return form_text( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'url':
                if ( $method == 'input' ) {
                    $options += [ 'type' => 'url' ];
                    
                    return form_text( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value
                    ];
                }
                break;
            case 'email':
                if ( $method == 'input' ) {
                    $options += [ 'type' => 'email' ];
                    
                    return form_text( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value
                    ];
                }
                break;
            case 'select':
                if ( $method == 'input' ) {
    
                    $options += [ 'options' => $option_list, 'select_alt' => TRUE, 'optgroup' => FALSE, ];
    
                    return form_select( $data['field_name'], fusion_parse_locale( $data['field_title'] ), $field_value, $options );
                    
                } else if ( $method == 'display' && $field_value ) {
    
                    $options_value = explode( ",", $data['field_options'] );
                    
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => !empty( $options_value[ $field_value ] ) ? $options_value[ $field_value ] : $field_value,
                    ];
                }
                break;
            case 'tags':
                if ( $method == 'input' ) {
                    $options += [ 'options' => $option_list, 'tags' => TRUE, 'multiple' => TRUE, 'width' => '100%', 'inner_width' => '100%' ];
                    
                    return form_select( $data['field_name'],
                        $options['show_title'] ? fusion_parse_locale( $data['field_title'] ) : '',
                        $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'location':
                if ( $method == 'input' ) {
                    $options += [ 'width' => '100%' ];
                    $options['options'] = Geomap::get_Country();
                    return form_select( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'textarea':
                if ( $method == 'input' ) {
                    return form_textarea( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value
                    ];
                }
                break;
            case 'checkbox':
                if ( $method == 'input' ) {
                    return form_checkbox( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'datepicker':
                if ( $method == 'input' ) {
                    return form_datepicker( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => showdate( 'shortdate', $field_value )
                    ];
                }
                break;
            case 'colorpicker':
                if ( $method == 'input' ) {
                    return form_colorpicker( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
            case 'upload':
                if ( $method == 'input' ) {
                    return form_fileinput( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value
                    ];
                }
                break;
            case 'hidden':
                if ( $method == 'input' ) {
                    return form_hidden( $data['field_name'], self::parse_label( $data['field_title'] ), $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value
                    ];
                }
                break;
            case 'address':
                if ( $method == 'input' ) {
                    return form_geo( $data['field_name'], $field_label, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => implode( '|', $field_value )
                    ];
                }
                break;
            case 'toggle':
                $options['toggle'] = 1;
                $options['toggle_text'] = [ $this->locale['off'], $this->locale['on'] ];
                if ( $method == 'input' ) {
                    return form_checkbox( $data['field_name'], $field_label, $field_value, $options );
                } else if ( $method == 'display' && $field_value ) {
                    return [
                        'title' => fusion_parse_locale( $data['field_title'] ),
                        'value' => $field_value,
                    ];
                }
                break;
        }
        
        return FALSE;
    }
    
}

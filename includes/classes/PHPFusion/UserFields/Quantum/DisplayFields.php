<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: DisplayFields.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
namespace PHPFusion\UserFields\Quantum;

use PHPFusion\Geomap;
use PHPFusion\UserFieldsQuantum;

/**
 * Class DisplayFields
 *
 * @package PHPFusion\UserFields\Quantum
 */
class DisplayFields {

    private $class = NULL;

    /**
     * DisplayFields constructor.
     *
     * @param UserFieldsQuantum $class
     */
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
        $field_value = $this->getFieldValue( $callback_data, $data['field_name'], $options['hide_value'] );

        $field_label = $options['show_title'] ? fusion_parse_locale( $data['field_title'] ).':' : '';

        switch ( $data['field_type'] ) {
            case 'file':
                return $this->displayModule( $method, $field_value, $callback_data, $options );
                break;
            case 'textbox':
                return $this->displayText( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'number':
                return $this->displayText( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [ 'type' => 'number' ] );
                break;
            case 'url':
                return $this->displayText( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [ 'type' => 'url' ] );
                break;
            case 'email':
                return $this->displayText( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [ 'type' => 'email' ] );
                break;
            case 'select':
                return $this->displaySelect( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [ 'options' => array_combine( array_values( $option_list ), array_values( $option_list ) ) ] );
                break;
            case 'tags':
                return $this->displaySelect( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [
                        'options'     => $option_list,
                        'tags'        => TRUE,
                        'width'       => '100%',
                        'inner_width' => '100%'
                    ] );
                break;
            case 'location':
                return $this->displaySelect( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options + [
                        'options' => array_combine( array_values( Geomap::get_Country() ), array_values( Geomap::get_Country() ) ),
                    ] );
                break;
            case 'textarea':
                return $this->displayTextarea( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'checkbox':
                return $this->displayCheckbox( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'datepicker':
                return $this->displayDatepicker( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'colorpicker':
                return $this->displayColorpicker( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'upload':
                return $this->displayUpload( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'hidden':
                return $this->displayHidden( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'address':
                return $this->displayAddress( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
            case 'toggle':
                return $this->displayToggle( $method, $data['field_title'], $data['field_name'], $field_label, $field_value, $options );
                break;
        }

        return '';
    }

    /**
     * @param $callback_data
     * @param $field_name
     * @param $hide_value
     *
     * @return int|mixed|string
     */
    private function getFieldValue( $callback_data, $field_name, $hide_value ) {
        if ( !$hide_value ) {
            $field_value = isset( $callback_data[ $field_name ] ) ? $callback_data[ $field_name ] : '';
            if ( $field_post_value = post( $field_name ) ) {
                $field_value = $field_post_value;
            }
            return $field_value;
        }
        return '';
    }

    /**
     * @param       $profile_method
     * @param       $field_value
     * @param       $user_data
     * @param       $options
     *
     * @return array|string
     */
    private function displayModule( $profile_method, $field_value, $user_data, $options ) {

        if ( !empty( $options['field_file'] ) && file_exists( $options['field_file'] ) ) {

            $user_fields = '';
            include $options['field_file'];

            if ( $profile_method == 'input' ) {
                return $user_fields;
            }

            if ( $profile_method == 'display' && !empty( $user_fields['value'] ) ) {
                return $user_fields;
            }
            return '';
        }
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayColorpicker( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_colorpicker( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value,
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayDatepicker( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_datepicker( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => showdate( 'shortdate', $field_value )
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     * @throws \ReflectionException
     */
    private function displayCheckbox( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_checkbox( $field_name, $field_label, $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value,
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayTextarea( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_textarea( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value
            ];
        }
        return '';
    }


    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     * @throws \ReflectionException
     */
    private function displaySelect( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {

            return form_select( $field_name, fusion_parse_locale( $field_label ), $field_value, $options + [
                    'select_alt' => TRUE,
                    'options'    => $options['options']
                ] );

        } else if ( $method == 'display' && $field_value ) {

            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => !empty( $options['options'][ $field_value ] ) ? $options['options'][ $field_value ] : $field_value,
            ];
        }
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|mixed|string
     * @throws \ReflectionException
     */
    private function displayText( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_text( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value,
            ];
        }
        return '';
    }

    /**
     * Display Toggle Field
     *
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     * @throws \ReflectionException
     */
    private function displayToggle( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            $options['toggle'] = 1;
            $options['toggle_text'] = [ $this->locale['off'], $this->locale['on'] ];
            return form_checkbox( $field_name, $field_label, $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value,
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayAddress( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_geo( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => implode( ',', $field_value )
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayHidden( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_hidden( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value
            ];
        }
        return '';
    }

    /**
     * @param $method
     * @param $field_title
     * @param $field_name
     * @param $field_label
     * @param $field_value
     * @param $options
     *
     * @return array|string
     */
    private function displayUpload( $method, $field_title, $field_name, $field_label, $field_value, $options ) {
        if ( $method == 'input' ) {
            return form_fileinput( $field_name, fusion_parse_locale( $field_label ), $field_value, $options );
        }
        if ( $method == 'display' && $field_value ) {
            return [
                'title' => fusion_parse_locale( $field_title ),
                'value' => $field_value
            ];
        }
        return '';
    }

}

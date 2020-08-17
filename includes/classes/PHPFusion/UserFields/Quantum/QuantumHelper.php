<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: QuantumHelper.php
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
/**
 * Helper class for analytical functions
 * Class QuantumHelper
 */
class QuantumHelper {

    /**
     * Parse the correct label language. Requires serialized $value.
     *
     * @param $value - Serialized
     *
     * @return string
     *               NOTE: If your field does not parse properly, check your column length. Set it to TEXT NOT NULL.
     */
    public static function parseLabel( $value ) {
        if ( self::isSerialized( $value ) ) {
            $value = unserialize( $value ); // if anyone can give me a @unserialize($value) withotu E_NOTICE. I'll drop is_serialized function.
            return (string)( isset( $value[ LANGUAGE ] ) ) ? $value[ LANGUAGE ] : '';
        } else {
            return (string)$value;
        }
    }

    public static function isSerialized( $value, &$result = NULL ) {
        // Bit of a give away this one
        if ( !is_string( $value ) ) {
            return FALSE;
        }
        // Serialized FALSE, return TRUE. unserialize() returns FALSE on an
        // invalid string or it could return FALSE if the string is serialized
        // FALSE, eliminate that possibility.
        if ( 'b:0;' === $value ) {
            $result = FALSE;

            return TRUE;
        }
        $length = strlen( $value );
        $end = '';
        if ( isset( $value[0] ) ) {
            switch ( $value[0] ) {
                case 's':
                    if ( '"' !== $value[ $length - 2 ] ) {
                        return FALSE;
                    }
                case 'b':
                case 'i':
                case 'd':
                    // This looks odd but it is quicker than isset()ing
                    $end .= ';';
                case 'a':
                case 'O':
                    $end .= '}';
                    if ( ':' !== $value[1] ) {
                        return FALSE;
                    }
                    switch ( $value[2] ) {
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
                    if ( $value[ $length - 1 ] !== $end[0] ) {
                        return FALSE;
                    }
                    break;
                default:
                    return FALSE;
            }
        }
        if ( ( $result = @unserialize( $value ) ) === FALSE ) {
            $result = NULL;

            return FALSE;
        }

        return TRUE;
    }


    ### Setters ###

    public static function fusion_getlocale( $data, $input_name ) {
        $language_opts = fusion_get_enabled_languages();

        if ( post( $input_name ) ) {

            return self::serialize_fields( $input_name );

        } else {

            if ( isset( $data[ $input_name ] ) ) {
                if ( self::is_serialized( $data[ $input_name ] ) ) {
                    return unserialize( $data[ $input_name ] );
                } else {
                    $value = "";
                    foreach ( $language_opts as $lang ) {
                        $value[ $lang ] = $data[ $input_name ];
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
     * @param $input_name
     *
     * @return bool|string
     * @throws \Exception
     */
    public static function serialize_fields( $input_name ) {
        $post_input = post( [ $input_name ] );
        if ( $post_input ) {
            $field_var = [];
            foreach ( $post_input as $language => $value ) {
                $field_var[ $language ] = sanitizer( $value );
            }

            return serialize( $field_var );
        }

        return FALSE;
    }

}

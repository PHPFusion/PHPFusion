<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc
| Author : Frederick MC Chan (Chan)
| Co-Author: Dan C (JoiNNN)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use Defender\Validation;

/**
 * Class Defender
 */
class Defender {
    public static $input_errors = [];
    private static $debug = FALSE;
    private static $defender_instance = NULL;

    private static $input_name;
    private static $input_error_text = [];
    private static $page_hash = '';
    public $ref = [];

    // Declared by Form Sanitizer
    public $field = [];
    public $field_name = '';
    public $field_value = '';
    public $field_default = '';
    public $field_config = [
        'type'        => '',
        'value'       => '',
        //'default' => '',
        'name'        => '',
        //'id' => '',
        'safemode'    => '',
        'path'        => '',
        'thumbnail_1' => '',
        'thumbnail_2' => '',
    ];

    /**
     * Generates and return class instance
     * Eliminates global usage in functions
     * Instead of using  - `global $defender`, try `\defender->getInstance()`
     *
     * @return null|static
     */
    public static function getInstance() {

        if ( self::$defender_instance === NULL ) {
            self::$defender_instance = new static();
        }

        return self::$defender_instance;
    }

    /**
     * Serialize an array securely
     *
     * @param array $array
     *
     * @return string
     */
    public static function serialize( array $array = [] ) {

        $return_default = '';
        if ( is_array( $array ) ) {
            return base64_encode( serialize( $array ) );
        }

        return $return_default;
    }

    public static function encode( $value ) {

        return base64_encode( json_encode( $value ) );
    }

    public static function decode( $value ) {

        return json_decode( base64_decode( $value ), TRUE );
    }

    /**
     * Read serialized array
     *
     * @param $string - serialized string
     *
     * @return array|mixed
     */
    public static function unserialize( $string ) {

        $return_default = [];
        if ( !empty( $string ) ) {
            $array = unserialize( base64_decode( $string ) );
            if ( !empty( $array ) ) {
                return $array;
            }
        }

        return $return_default;
    }

    public static function add_field_session( array $array ) {

        $_SESSION['form_fields'][ self::pageHash() ][ $array['input_name'] ] = $array;
    }

    /**
     * Hash a token to prevent unauthorized access
     *
     * @return string
     */
    public static function pageHash() {

        if ( !defined( 'SECRET_KEY' ) ) {
            $chars = [ 'abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ', '123456789' ];
            $count = [ ( strlen( $chars[0] ) - 1 ), ( strlen( $chars[1] ) - 1 ) ];
            $key = '';
            for ( $i = 0; $i < 32; $i++ ) {
                $type = mt_rand( 0, 1 );
                $key .= substr( $chars[ $type ], mt_rand( 0, $count[ $type ] ), 1 );
            }

            define( 'SECRET_KEY', $key );
        }

        if ( empty( self::$page_hash ) ) {
            self::$page_hash = md5( SECRET_KEY );
        }

        return (string)self::$page_hash;
    }

    /**
     * Return the current document field session or sessions
     * Use for debug purposes
     *
     * @param string $input_name
     *
     * @return string
     */
    public function get_current_field_session( $input_name = '' ) {

        if ( $input_name && isset( $_SESSION['form_fields'][ self::pageHash() ][ $input_name ] ) ) {
            return $_SESSION['form_fields'][self::pageHash()][$input_name];
            //return session( [ 'form_fields', self::pageHash(), $input_name ] );
        } else {
            if ( $input_name ) {
                return 'The session for this field is not found';
            } else {
                //return $_SESSION['form_fields'];
                return $_SESSION['form_fields'][ self::pageHash() ];

                //return session( [ 'form_fields', self::pageHash() ] );
            }
        }
    }

    public static function unset_field_session() {

        session_remove( 'form_fields' );
    }

    static function sanitize_array( $array ) {

        foreach ( $array as $name => $value ) {
            $array[ stripinput( $name ) ] = trim( censorwords( stripinput( $value ) ) );
        }

        return (array)$array;
    }

    // Checks whether an input was marked as invalid

    /**
     * ID for Session
     * No $userName because it can be changed and tampered via Edit Profile.
     * Using IP address extends for guest
     *
     * @return mixed
     */
    public static function set_sessionUserID() {

        $userdata = fusion_get_userdata();

        return !empty( $userdata['user_id'] ) && !isset( $_POST['login'] ) ? (int)fusion_get_userdata( 'user_id' ) : str_replace( '.', '-', USER_IP );
    }

    /**
     * @return array
     */
    public static function getInputErrors() {

        return self::$input_errors;
    }

    /**
     * Set and override default field error text
     *
     * @param $input_name
     * @param $text
     */
    public static function setErrorText( $input_name, $text ) {

        self::$input_error_text[ $input_name ] = $text;
    }

    /**
     * Fetches the latest error text of this input
     * Important! Ensure your applications do not refresh screen for this error to show.
     * Usage fusion_safe(); for conditional redirect.
     *
     * @param $input_name
     *
     * @return null
     */
    public static function getErrorText( $input_name ) {

        if ( self::inputHasError( $input_name ) ) {
            return isset( self::$input_error_text[ $input_name ] ) ? self::$input_error_text[ $input_name ] : NULL;
        }

        return NULL;
    }

    /**
     * @param $input_name
     *
     * @return bool
     */
    public static function inputHasError( $input_name ) {

        if ( isset( self::$input_errors[ $input_name ] ) ) {
            return TRUE;
        }

        return FALSE;
    }

    public static function get_inputError() {

        return self::$input_errors;
    }

    /**
     * Generate a key
     *
     * @param $private_key
     *
     * @return string
     */
    public static function get_encrypt_key( $private_key ) {

        return $encryption_key = openssl_random_pseudo_bytes( 32, $private_key ); // 256 bits
    }

    /**
     * Encrypts a string securely with a private key
     *
     * @param string $string      The text to encrypt
     * @param string $private_key For better security use \Defender::get_encrypt_key to generate your private key
     *
     * Does not support array encrypt.
     *
     * @return string
     */
    public static function encrypt_string( $string, $private_key = 'php-fusion' ) {

        $ivlen = openssl_cipher_iv_length( $cipher = 'AES-128-CBC' );
        $iv = openssl_random_pseudo_bytes( 16, $ivlen ); // 128 bits
        $string = self::pkcs7_pad( $string, 16 );
        $ciphertext_raw = openssl_encrypt( $string, $cipher, $private_key, $options = OPENSSL_RAW_DATA, $iv );
        $hmac = hash_hmac( 'sha256', $ciphertext_raw, $private_key, $as_binary = TRUE );

        return base64_encode( $iv.$hmac.$ciphertext_raw );
    }

    private static function pkcs7_pad( $data, $size ) {

        $length = $size - strlen( $data ) % $size;

        return $data.str_repeat( chr( $length ), $length );
    }

    /**
     * Decrypts a string securely with a private key
     *
     * @param $string      - The string to decrypt
     * @param $private_key - For better security use \Defender::get_encrypt_key to generate your private key
     *
     * @return null|string
     */
    public static function decrypt_string( $string, $private_key = 'php-fusion' ) {

        $c = base64_decode( $string );
        $ivlen = openssl_cipher_iv_length( $cipher = 'AES-128-CBC' );
        $iv = substr( $c, 0, $ivlen );
        $hmac = substr( $c, $ivlen, $sha2len = 32 );
        $ciphertext_raw = substr( $c, $ivlen + $sha2len );
        $string = openssl_decrypt( $ciphertext_raw, $cipher, $private_key, $options = OPENSSL_RAW_DATA, $iv );
        $string = self::pkcs7_unpad( $string );
        $calcmac = hash_hmac( 'sha256', $ciphertext_raw, $private_key, $as_binary = TRUE );
        if ( !function_exists( 'hash_equals' ) ) {
            function hash_equals( $str1, $str2 ) {

                if ( strlen( $str1 ) != strlen( $str2 ) ) {
                    return FALSE;
                } else {
                    $res = $str1 ^ $str2;
                    $ret = 0;
                    for ( $i = strlen( $res ) - 1; $i >= 0; $i-- ) {
                        $ret |= ord( $res[ $i ] );
                    }

                    return !$ret;
                }
            }
        }
        if ( hash_equals( $hmac, $calcmac ) ) {//PHP 5.6+ timing attack safe comparison
            return $string;
        }

        return NULL;
    }

    private static function pkcs7_unpad( $data ) {

        return substr( $data, 0, -ord( $data[ strlen( $data ) - 1 ] ) );
    }

    /**
     * Request whether safe to proceed at all times
     *
     * @return bool
     */
    public static function safe() {

        if ( !defined( 'FUSION_NULL' ) ) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array $array
     */
    public function addHoneypot( array $array ) {

        $_SESSION['honeypots'][ self::pageHash() ][ $array['honeypot'] ] = $array;
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */

    /**
     * @param string $honeypot
     *
     * @return string
     */
    public function getHoneypot( $honeypot = '' ) {

        if ( $honeypot && isset( $_SESSION['honeypots'][ self::pageHash() ][ $honeypot ] ) ) {
            return $_SESSION['honeypots'][ self::pageHash() ][ $honeypot ];
        } else {
            if ( $honeypot ) {
                return 'This form contains no honeypots';
            } else {
                return $_SESSION['honeypots'][ self::pageHash() ];
            }
        }
    }

    /**
     * @param bool|FALSE $value
     */
    public function debug( $value = FALSE ) {

        self::$debug = $value;
    }

    /*
     * Highlight the form field as error
     */

    /**
     * Sanitize with input name
     *
     * @param        $key
     * @param string $default
     * @param bool   $input_name
     * @param bool   $is_multiLang
     *
     * @return string
     * @throws Exception
     */
    public function sanitizer( $key, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {

        $value = $this->filterPostArray( $key );

        return $this->formSanitizer( $value, $default, $input_name, $is_multiLang );
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function filterPostArray( $key ) {

        $flag = '';
        $input_key = $key;
        if ( is_array( $key ) ) {
            // always use key 0 for filter var
            $input_key = $key[0];
            $flag = FILTER_REQUIRE_ARRAY;
        }

        $filtered = post( $input_key, FILTER_DEFAULT, $flag );

        if ( is_array( $key ) ) {
            $input_ref = $key;
            unset( $input_ref[0] );

            // Get the value of the filtered post value using the $key array as map
            return array_reduce(
                $input_ref,
                function ( $value, $key ) {

                    return ( !empty( $value[ $key ] ) ? $value[ $key ] : '' );
                },
                $filtered
            );
        }

        return (string)$filtered;
    }

    /**
     * Sanitize
     *
     * @param            $value
     * @param string     $default
     * @param bool|FALSE $input_name
     * @param bool|FALSE $is_multiLang
     *
     * @return string
     * @throws Exception
     */
    public function formSanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {

        $val = [];

        if ( $input_name ) {

            if ( $is_multiLang ) {

                $language = array_keys( fusion_get_enabled_languages() );

                foreach ( $language as $lang ) {
                    $iname = $input_name.'['.$lang.']';

                    if ( isset( $_SESSION['form_fields'][ self::pageHash() ][ $iname ] ) ) {

                        $this->field_config = $_SESSION['form_fields'][ self::pageHash() ][ $iname ];
                        $this->field_name = $iname;
                        $this->field_value = $value[ $lang ];
                        $this->field_default = $default;
                        $val[ $lang ] = $this->validate();
                    }
                }

                if ( $this->field_config['required'] && ( !$value[ LANGUAGE ] ) ) {

                    self::stop();
                    $iname = $input_name.'['.LANGUAGE.']';
                    self::setInputError( $iname );

                    return $default;

                } else {
                    $val_ = [];

                    foreach ( $val as $lang => $value ) {
                        $val_[ $lang ] = $val[ $lang ];
                    }

                    return serialize( $val_ );
                }
            } else {
                // Make sure that the input was actually defined in code.. AND there must be a value to worth the processing power expense!
                if ( isset( $_SESSION['form_fields'][ self::pageHash() ][ $input_name ] ) ) {
                    $this->field_config = $_SESSION['form_fields'][ self::pageHash() ][ $input_name ];
                    $this->field_name = $input_name;
                    $this->field_value = $value;
                    $this->field_default = $default;
                    /*
                     * These two checks won't be neccesary after we add the options in all inputs
                     * NOTE: Please don't pass 'stripinput' as callback, before we reach a callback
                     * everything is checked and sanitized already. The callback should only check
                     * if certain conditions are met then return TRUE|FALSE and not do any alterations
                     * the the value itself
                     */
                    $callback = isset( $this->field_config['callback_check'] ) ? $this->field_config['callback_check'] : FALSE;
                    $regex = isset( $this->field_config['regex'] ) ? $this->field_config['regex'] : FALSE;
                    $secured = $this->validate();
                    // If truly FALSE the check failed
                    if ( $secured === FALSE || ( $this->field_config['required'] == 1 && ( $secured === FALSE || $secured == '' ) ) ||
                        ( $secured != '' && $regex && !preg_match( '@^'.$regex.'$@i', $secured ) ) || // regex will fail for an imploded array, maybe move this check
                        ( is_callable( $callback ) && !$callback( $secured ) )
                    ) {

                        self::stop();
                        self::setInputError( $input_name );

                        // Add regex error message.
                        if ( $secured != '' && $regex && !preg_match( '@^'.$regex.'$@i', $secured ) ) {
                            addNotice( 'danger', sprintf( fusion_get_locale( 'regex_error' ), $this->field_config['title'] ) );
                        }
                        // Add a notice
                        if ( self::$debug ) {
                            addNotice( 'warning', '<strong>'.$input_name.':</strong>'.( $this->field_config['safemode'] ? ' is in SAFEMODE and the' : '' ).' check failed' );
                        }

                        // Return user's input for correction
                        return $this->field_value;
                    } else {
                        if ( self::$debug ) {
                            addNotice( 'info', $input_name.' = '.( is_array( $secured ) ? 'array' : $secured ) );
                        }

                        return $secured;
                    }
                } else {
                    return $default;
                }
            }
        } else {
            if ( $value ) {
                if ( !is_array( $value ) ) {
                    if ( intval( $value ) ) {
                        return stripinput( $value ); // numbers
                    } else {
                        return censorwords( stripinput( trim( preg_replace( '/ +/i', ' ', $value ) ) ) );
                    }
                } else {
                    $secured = [];
                    foreach ( $value as $unsecured ) {
                        if ( (int)$unsecured ) {
                            $secured[] = stripinput( $unsecured ); // numbers
                        } else {
                            $secured[] = censorwords( stripinput( trim( preg_replace( '/ +/i', ' ', $unsecured ) ) ) );
                        }
                    }

                    return implode( $this->field_config['delimiter'], $secured );
                }
            } else {
                return $default;
            }
        }

        throw new Exception( 'The form sanitizer could not handle the request! (input: '.$input_name.')' );
    }

    public function fileSanitizer( $key, $default = '', $input_name = FALSE ) {

        $upload = $this->formSanitizer( $_FILES[ $key ], $default, $input_name );
        if ( isset( $upload['error'] ) && $upload['error'] == 0 ) {
            return $upload;
        }

        return [];
    }

    public function validate() {

        Validation::inputName( $this->field_name );
        Validation::inputDefault( $this->field_default );
        Validation::inputValue( $this->field_value );
        Validation::inputConfig( $this->field_config );

        $locale = fusion_get_locale( LOCALE.LOCALESET.'defender.php' );
        // Are there situations were inputs could have leading
        // or trailing spaces? If not then uncomment line below
        //$this->field_value = trim($this->field_value);
        // Don't bother processing and validating empty inputs
        //if ($this->field_value == '') return $this->field_value;
        /**
         * Keep this include in the constructor
         * This solution was needed to load the defender.inc.php before
         * defining LOCALESET
         */
        // declare the validation rules and assign them
        // type of fields vs type of validator

        // execute sanitisation rules at point blank precision using switch
        try {
            if ( !empty( $this->field_config['type'] ) ) {
                if ( empty( $this->field_value ) && ( $this->field_config['type'] !== 'number' ) ) {
                    return $this->field_default;
                }

                return Validation::getValidated();

            } else {
                self::stop();
                $locale['type_unknown'] = '%s: has an unknown type set'; // to be moved
                addNotice( 'danger', sprintf( $locale['type_unknown'], self::$input_name ) );
            }
        } catch ( Exception $e ) {
            self::stop();
            addNotice( 'danger', $e->getMessage() );
        }

        return NULL;
    }

    /**
     * Send an Unsafe Signal acorss all PHP-Fusion Components
     * This will automatically halt on all important execution without exiting.
     *
     * @param $notice - Show custom notice message on validation errors
     */
    public static function stop( $notice = '' ) {
        //debug_print_backtrace();
        if ( !defined( 'FUSION_NULL' ) ) {
            define( 'FUSION_NULL', TRUE );
            if ( $notice ) {
                if ( function_exists( 'alert' ) ) {
                    echo alert( $notice );
                } else {
                    addNotice( 'danger', $notice );
                }
                define( 'STOP_REDIRECT', TRUE );
            }
            //addNotice('danger', '<strong>'.fusion_get_locale('error_request', LOCALE.LOCALESET.'defender.php').'</strong>');
        }
    }

    public static function setInputError( $input_name ) {

        self::$input_errors[ $input_name ] = TRUE;
    }

}

/**
 * Verify and Sanitize Inputs
 *
 * @param            $value
 * @param string     $default
 * @param bool|FALSE $input_name
 * @param bool|FALSE $is_multiLang
 *
 * @return mixed
 * @throws Exception
 */
function form_sanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {

    return Defender::getInstance()->formSanitizer( $value, $default, $input_name, $is_multiLang );
}


/**
 * Verify and Sanitize Inputs with input_name
 * A more secured method
 *
 * @param        $value
 * @param string $default
 * @param bool   $input_name
 * @param bool   $is_multiLang
 *
 * @return string
 * @throws Exception
 */
function sanitizer( $value, $default = '', $input_name = FALSE, $is_multiLang = FALSE ) {

    return Defender::getInstance()->sanitizer( $value, $default, $input_name, $is_multiLang );
}

/**
 * @param        $value
 * @param string $default
 * @param bool   $input_name
 *
 * @return array|string
 */
function file_sanitizer( $value, $default = '', $input_name = FALSE ) {

    return defender::getInstance()->fileSanitizer( $value, $default, $input_name );
}

/**
 * Isset GET
 *
 * @param $key
 *
 * @return bool
 */
function check_get( $key ) {
    return ( isset( $_GET[ $key ] ) ? TRUE : FALSE );
}

/**
 * Isset POST
 *
 * @param $key
 *
 * @return bool
 */
function check_post( $key ) {
    return ( isset( $_POST[ $key ] ) ? TRUE : FALSE );
}

/**
 * @param null   $key
 * @param int    $type
 * @param string $flags
 *
 * @return mixed
 */
function get( $key = NULL, $type = FILTER_DEFAULT, $flags = '' ) {

    if ( $flags == FILTER_VALIDATE_INT ) {

        if ( isset( $_GET[ $key ] ) && isnum( $_GET[ $key ] ) && ( $_GET[ $key ] > PHP_INT_MAX ) ) {
            return (int)$_GET[ $key ];
        }

        return (int)0;
    }

    return stripinput( filter_input( INPUT_GET, $key, $type, $flags ) );
}


/**
 * @param     $key
 * @param int $type
 * @param     $flags
 *
 * @return mixed
 */
function post( $key, $type = FILTER_DEFAULT, $flags = '' ) {

    if ( is_array( $key ) ) {
        $flag = '';
        $input_key = $key;
        if ( is_array( $key ) ) {
            // always use key 0 for filter var
            $input_key = $key[0];
            $flag = FILTER_REQUIRE_ARRAY;
        }

        $filtered = post( $input_key, FILTER_DEFAULT, $flag );

        if ( is_array( $key ) ) {
            $input_ref = $key;
            unset( $input_ref[0] );

            // Get the value of the filtered post value using the $key array as map
            return array_reduce(
                $input_ref,
                function ( $value, $key ) {

                    return ( !empty( $value[ $key ] ) ? $value[ $key ] : '' );
                },
                $filtered
            );
        }

        return (string)$filtered;
    }

    if ( $flags == FILTER_VALIDATE_INT ) {

        if ( isset( $_POST[ $key ] ) && isnum( $_POST[ $key ] ) && ( $_POST[ $key ] > PHP_INT_MAX ) ) {

            return (int)$_POST[ $key ];
        }
    }

    return filter_input( INPUT_POST, $key, $type, $flags );
}


/**
 * @param $key
 *
 * @return array
 */
function post_array( $key ) {

    return (array)Defender::getInstance()->filterPostArray( $key );
}


/**
 * @param     $key
 * @param int $type
 *
 * @return mixed
 */
function server( $key, $type = FILTER_DEFAULT ) {

    return filter_input( INPUT_SERVER, $key, $type );
}


/**
 * @param $key
 *
 * @return bool
 */
function file_uploaded( $key ) {

    if ( !empty( $_FILES ) ) {
        if ( is_array( $key ) ) {
            $files =& $_FILES;
            foreach ( $key as $pkey ) {
                $files =& $files[ $pkey ];
            }

            return is_uploaded_file( $files['tmp_name'] );
        }

        return is_uploaded_file( $_FILES[ $key ]['tmp_name'] );
    }

    return FALSE;
}


/**
 * @param     $key
 * @param int $type
 *
 * @return mixed
 */
function environment( $key, $type = FILTER_DEFAULT ) {

    return filter_input( INPUT_ENV, $key, $type );
}


/***
 * Sets a cookie
 *
 * @param string $key
 * @param int    $type
 *
 * @return mixed|string|string[]
 */
function cookie( string $key, $type = FILTER_DEFAULT ) {

    return stripinput( filter_input( INPUT_COOKIE, $key, $type ) );
}


/**
 * Remove a cookie
 *
 * @param string $key
 *
 * @return mixed
 */
function cookie_remove( string $key ) {

    unset( $_COOKIE[ $key ] );

    return $_COOKIE;
}


/**
 * Cleans curent session
 */
function session_clean() {

    $_SESSION = [];
}


/**
 * @param $key
 * @param $value
 *
 * @return mixed
 */
function session_add( $key, $value ) {

    //global $_SESSION;
    if ( is_array( $key ) ) {
        //  print_p($_SESSION);
        $session =& $_SESSION;
        foreach ( $key as $pkey ) {
            $session =& $session[ $pkey ];
        }
        $session = $value;

        return $session;
    }

    return $_SESSION[ $key ] = $value;
}


/**
 * @param $key
 *
 * @return null
 */
function session_get( $key ) {

    if ( is_array( $key ) ) {
        $session =& $_SESSION;
        foreach ( $key as $i ) {
            $session =& $session[ $i ];
        }

        return $session;
    }

    return ( isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : NULL );
}


/**
 * @param $key
 *
 * @return mixed
 */
function session_remove( $key ) {
    if ( is_array( $key ) ) {
        $temp = &$_SESSION;
        $counter = 1;
        foreach ( $key as $nkey ) {
            if ( $counter == count( $key ) ) {
                unset( $temp[ $nkey ] );
            }
            $temp = &$temp[ $nkey ];
            $counter++;
        }
        $session = &$temp;

        return $session;
    }

    unset( $_SESSION[ $key ] );

    return $_SESSION;
}


/**
 * @param $value
 *
 * @return string
 */
function fusion_encode( $value ) {

    return Defender::encode( $value );
}


/**
 * @param $value
 *
 * @return mixed
 */
function fusion_decode( $value ) {

    return Defender::decode( $value );
}


function fusion_safe() {

    return Defender::getInstance()->safe();
}


/**
 * @return bool
 */

function fusion_stop() {

    return Defender::getInstance()->stop();
}


/**
 * @param $value
 * @param $password
 *
 * @return null|string
 */
function fusion_decrypt( $value, $password ) {

    return Defender::decrypt_string( $value, $password );
}

require_once __DIR__.'/defender/validation.php';
require_once __DIR__.'/defender/token.php';
require_once __DIR__.'/defender/mimecheck.php';

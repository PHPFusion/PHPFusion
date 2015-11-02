<?php

/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 PHP-Fusion Inc.
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc.php
| Author : Frederick MC Chan (Hien)
| Co-Author: Dan C (JoiNNN)
| Version : 9.0.5 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

class defender {
	public $debug = FALSE;
	public $ref = array();
	//public $error_content = array();
	public $error_title = '';
	public $input_errors = array();
	// Declared by Form Sanitizer
	public $field = array();
	public $field_name = '';
	public $field_value = '';
	public $field_default = '';
	public $field_config = array(
		'type' => '',
		'value' => '',
		//'default' => '',
		'name' => '',
		//'id' => '',
		'safemode' => '',
		'path' => '',
		'thumbnail_1' => '',
		'thumbnail_2' => '',
	);
	private $tokenIsValid = TRUE;

	// Sanitize Fields Automatically

	/**
	 * ID for Session
	 * No $userName because it can be changed and tampered via Edit Profile.
	 * Using IP address extends for guest
	 * @return mixed
	 */
	static function set_sessionUserID() {
		global $userdata;
		return isset($userdata['user_id']) && !isset($_POST['login']) ? (int)$userdata['user_id'] : str_replace('.', '-', USER_IP);
	}

	static function add_field_session(array $array) {
		$_SESSION['form_fields'][$_SERVER['PHP_SELF']][$array['input_name']] = $array;
	}

	// Checks whether an input was marked as invalid

	/**
	 * Return the current document field session or sessions
	 * Use for debug purposes
	 * @param string $input_name
	 * @return string
	 */
	static function get_current_field_session($input_name = "") {
		if ($input_name && isset($_SESSION['form_fields'][$_SERVER['PHP_SELF']][$input_name])) {
			return $_SESSION['form_fields'][$_SERVER['PHP_SELF']][$input_name];
		} else {
			if ($input_name) {
				return "The session for this field is not found";
			} else {
				return $_SESSION['form_fields'][$_SERVER['PHP_SELF']];
			}
		}
	}

	// Marks an input as invalid

	public static function unset_field_session() {
		unset($_SESSION['form_fields']);
	}

	/**
	 * Generate a Token
	 * Generates a unique token
	 * @param string $form_id    The ID of the form
	 * @param int    $max_tokens The ammount of tokens to be kept for each form before we start removing older tokens from session
	 * @return string|string[]        The token
     *
     * Protection against CSRF is not going to work when the page redirects out before the form loads again.
     * We need a validation to eat up unncessary tokens
	 */
	public static function generate_token($form_id = 'phpfusion', $max_tokens = 10) {
		global $userdata, $defender;
        $defender->debug = false;
		$user_id = (iMEMBER ? $userdata['user_id'] : 0);
		// store just one token for each form if the user is a guest
		if ($user_id == 0) $max_tokens = 1;
		// Attempt to recover the token instead of generating a new one
		// Checks if a token is being posted and if is valid, and then
		// checks if the form for which this token was intended is
		// the same form for which we are trying to generate a token
		if (isset($_POST['fusion_token']) && $defender->tokenIsValid && ($form_id == stripinput($_POST['form_id']))) {
			$token = stripinput($_POST['fusion_token']);
			if ($defender->debug) addNotice('success', 'The token for "'.stripinput($_POST['form_id']).'" has been recovered and is being reused');
		} else {
            $token_time = time();
			$algo = fusion_get_settings('password_algorithm');
			$key = $user_id.$token_time.$form_id.SECRET_KEY;
			$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
			// generate a new token
			$token = $user_id.".".$token_time.".".hash_hmac($algo, $key, $salt);
			// store the token in session
			$_SESSION['csrf_tokens'][self::pageHash()][$form_id][] = $token;
            if ($defender->debug) {
                addNotice('info', 'A new token for "' . $form_id . '" was generated : ' . $token);
                if (!$defender->safe()) addNotice('danger', 'FUSION NULL is DECLARED');
                if (!empty($_SESSION['csrf_tokens'][self::pageHash()][$form_id])) {
                    addNotice('danger', 'Current Token That is Going to be validated in this page: ');
                    addNotice('danger', $_SESSION['csrf_tokens'][self::pageHash()][$form_id]); // is not going to be able to read the new one.
                } else {
                    addNotice('warning', 'There is no token for this page this round');
                }
            }
            // some cleaning, remove oldest token if there are too many
            if (count($_SESSION['csrf_tokens'][self::pageHash()][$form_id]) > $max_tokens) {
                if ($defender->debug) addNotice('warning', 'Token that is <b>erased</b> ' . $_SESSION['csrf_tokens'][self::pageHash()][$form_id][0] . '. This token cannot be validated anymore.');
                array_shift($_SESSION['csrf_tokens'][self::pageHash()][$form_id]);
            }

			if ($defender->debug) {
				if (!empty($_SESSION['csrf_tokens'][self::pageHash()][$form_id])) {
                    addNotice('danger', "After clean up, the token remaining is on " . $form_id . " is -- ");
					addNotice('danger', $_SESSION['csrf_tokens'][self::pageHash()][$form_id]);
				} else {
					addNotice('warning', 'There is no token for this page this round');
				}
			}
		}
		return $token;
	}

	/**
	 * Generates a md5 hash of the current page to make token session unique
	 * @return string
	 */
	private static function pageHash() {
		return md5(FUSION_REQUEST);
	}

	// Adds the field sessions on document load

	/**
	 * Request whether safe to proceed at all times
	 * @return bool
	 */
	public static function safe() {
		if (!defined("FUSION_NULL")) {
			return TRUE;
		}
		return FALSE;
	}

	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function validate() {
		global $locale;
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
		include_once LOCALE.LOCALESET."defender.php";
		// declare the validation rules and assign them
		// type of fields vs type of validator
		$validation_rules_assigned = array(
			'color' => 'textbox',
			'dropdown' => 'textbox',
			'text' => 'textbox',
			'textarea' => 'textbox',
			'textbox' => 'textbox',
			'checkbox' => 'checkbox',
			'password' => 'password',
			'date' => 'date',
			'timestamp' => 'timestamp',
			'number' => 'number',
			'email' => 'email',
			'address' => 'address',
			'name' => 'name',
			'url' => 'url',
			'image' => 'image',
			'file' => 'file',
			'document' => 'document',
		);
		// execute sanitisation rules at point blank precision using switch
		try {
			if (!empty($this->field_config['type'])) {
				if (empty($this->field_value)) {
					return $this->field_default;
				}
				switch ($validation_rules_assigned[$this->field_config['type']]) {
					case 'textbox':
						return $this->verify_text();
						break;
					// DEV: To be reviewed
					case 'date':
						return $this->verify_date();
						break;
					case 'timestamp':
						return $this->verify_date();
						break;
					case 'password':
						return $this->verify_password();
						break;
					case 'email':
						return $this->verify_email();
						break;
					case 'number' :
						return $this->verify_number();
						break;
					// DEV: To be reviewed
					case 'file' :
						return $this->verify_file_upload();
						break;
					case 'url' :
						return $this->verify_url();
						break;
					case 'checkbox' :
						return $this->verify_checkbox();
						break;
					case 'name':
						$name = $this->field_name;
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							self::setInputError($name.'-firstname');
						}
						if ($this->field_config['required'] && !$_POST[$name][1]) {
							$this->stop();
							self::setInputError($name.'-lastname');
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->verify_text();
							return $return_value;
						}
						break;
					case 'address':
						$name = $this->field_name;
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							self::setInputError($name.'-street-1');
						}
						if ($this->field_config['required'] && !$_POST[$name][1]) {
							$this->stop();
							self::setInputError($name.'-street-2');
						}
						if ($this->field_config['required'] && !$_POST[$name][2]) {
							$this->stop();
							self::setInputError($name.'-country');
						}
						if ($this->field_config['required'] && !$_POST[$name][3]) {
							$this->stop();
							self::setInputError($name.'-region');
						}
						if ($this->field_config['required'] && !$_POST[$name][4]) {
							$this->stop();
							self::setInputError($name.'-city');
						}
						if ($this->field_config['required'] && !$_POST[$name][5]) {
							$this->stop();
							self::setInputError($name.'-postcode');
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->verify_text();
							return $return_value;
						}
						break;
					// DEV: To be reviewed
					case 'image' :
						return $this->verify_image_upload();
						break;
					// need to know what is this field.
					case 'document':
						$name = $this->field_name;
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							self::setInputError($name.'-doc-1');
						}
						if ($this->field_config['required'] && !$_POST[$name][1]) {
							$this->stop();
							self::setInputError($name.'-doc-2');
						}
						if ($this->field_config['required'] && !$_POST[$name][2]) {
							$this->stop();
							self::setInputError($name.'-doc-3');
						}
						if ($this->field_config['required'] && !$_POST[$name][3]) {
							$this->stop();
							self::setInputError($name.'-doc-4');
						}
						if ($this->field_config['required'] && !$_POST[$name][4]) {
							$this->stop();
							self::setInputError($name.'-doc-5');
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->verify_text();
							return $return_value;
						}
						break;
					default:
						$this->stop();
						$locale['type_unknown'] = '%s: has an unknown type set'; // to be moved
						addNotice('danger', $this->field_name.$locale['type_unknown']);
				}
			} else {
				$this->stop();
				$locale['type_unset'] = '%s: has no type set'; // to be moved
				addNotice('danger', $this->field_name.$locale['type_unset']);
			}
		} catch (Exception $e) {
			$this->stop();
			addNotice('danger', $e->getMessage());
		}
	}

	// Destroys the user field session

	/**
	 * validate and sanitize a text
	 * accepts only 50 characters + @ + 4 characters
	 * returns str the sanitized input or bool FALSE
	 * if safemode is set and the check fails
	 */
	protected function verify_text() {
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				$vars[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($val))));
			}
			// set options for checking on delimiter, and default is pipe (json,serialized val)
			$delimiter = (!empty($this->field_config['delimiter'])) ? $this->field_config['delimiter'] : "|";
			$value = implode($delimiter, $vars);
		} else {
			$value = stripinput(trim(preg_replace("/ +/i", " ", censorwords($this->field_value)))); // very strong sanitization.
		}
		if ($this->field_config['required'] && !$value) self::setInputError($this->field_name);
		if ($this->field_config['safemode'] && !preg_check("/^[-0-9A-Z_@\s]+$/i", $value)) {
			return FALSE;
		} else {
			return $value;
		}
	}

	public function setInputError($input_name) {
		$this->input_errors[$input_name] = TRUE;
	}

	/** returns 10 integer timestamp
	 * accepts date format in - , / and . delimiters
	 * returns a default if blank
	 */
	protected function verify_date() {
		global $locale;
		// pair each other to determine which is month.
		// the standard value for dynamics is day-month-year.
		// the standard value for mysql is year-month-day.
		if ($this->field_value) {
			if (stristr($this->field_value, '-')) {
				$this->field_value = explode('-', $this->field_value);
			} elseif (stristr($this->field_value, '/')) {
				$this->field_value = explode('/', $this->field_value);
			} else {
				$this->field_value = explode('.', $this->field_value);
			}
			if (checkdate($this->field_value[1], $this->field_value[0], $this->field_value[2])) {
				if ($this->field_config['type'] == 'timestamp') {
					return mktime(0, 0, 0, $this->field_value[1], $this->field_value[0], $this->field_value[2]);
				} elseif ($this->field_config['type'] == 'date') {
					// year month day.
					$return_value = $this->field_value[2]."-".$this->field_value[1]."-".$this->field_value[0];
					return $return_value;
				}
			} else {
				$this->stop();
				self::setInputError($this->field_name);
				addNotice('info', sprintf($locale['df_404'], $this->field_config['title']));
			}
		} else {
			if ($this->field_config['required']) self::setInputError($this->field_name);
			return $this->field_default;
		}
	}
	// Field Verifications Rules

	/**
	 * Send an Unsafe Signal acorss all PHP-Fusion Components
	 * This will automatically halt on all important execution without exiting.
	 */
    static function stop() {
        global $locale;
        if (!defined('FUSION_NULL')) {
            addNotice('danger', $locale['error_request']);
            define('FUSION_NULL', TRUE);
        }
    }

	/**
	 * Checks if is a valid password
	 * accepts minimum of 8 and maximum of 64 due to encrypt limit
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_password() {
		// add min length, add max length, add strong password into roadmaps.
		if ($this->field_config['required'] && !$this->field_value) self::setInputError($this->field_name);
		if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $this->field_value)) {
			return $this->field_value;
		}
		return FALSE;
	}

	/**
	 * Checks if is a valid email address
	 * accepts only 50 characters + @ + 4 characters
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_email() {
		// TODO: This regex was reported previously as flawed and should be reviewed and fixed
		if ($this->field_config['required'] && !$this->field_value) self::setInputError($this->field_name);
		if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $this->field_value)) {
			return $this->field_value;
		}
		return FALSE;
	}

	/**
	 * Checks if is a valid number
	 * returns str the input or bool FALSE if check fails
	 * TODO: support decimal
	 */
	protected function verify_number() {
		if ($this->field_config['required'] && !$this->field_value) self::setInputError($this->field_name);
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				if (isnum($val)) $vars[] = $val; // no need for stripinput(), if ain't a number why bother stripping invalid chars...
			}
			$delimiter = (!empty($this->field_config['delimiter'])) ? $this->field_config['delimiter'] : ",";
			$value = implode($delimiter, $vars);
			return $value; // empty str is returned if $vars ends up empty
		} elseif (isnum($this->field_value)) {
			return $this->field_value;
		} else {
			return FALSE;
		}
	}

	/** @noinspection PhpInconsistentReturnPointsInspection */
	protected function verify_file_upload() {
		global $locale;
		require_once INCLUDES."infusions_include.php";
		if ($this->field_config['multiple']) {
			if (!empty($_FILES[$this->field_config['input_name']]['name'])) {
				$upload = array('error' => 0);
				if ($this->field_config['max_count'] < count($_FILES[$this->field_config['input_name']]['name'])) {
					$this->stop();
					$upload = array('error' => 1);
					addNotice('danger', $locale['df_424']);
					self::setInputError($this->field_name);
				} else {
					for ($i = 0; $i <= count($_FILES[$this->field_config['input_name']]['name'])-1; $i++) {
						if (($this->field_config['max_count'] == $i)) break;
						$source_file = $this->field_config['input_name'];
						$target_file = $_FILES[$this->field_config['input_name']]['name'][$i];
						$target_folder = $this->field_config['path'];
						$valid_ext = $this->field_config['valid_ext'];
						$max_size = $this->field_config['max_byte'];
						$query = '';
						$upload_file = array(
							'source_file' => '',
							'source_size' => '',
							'source_ext' => '',
							'target_file' => '',
							'target_folder' => '',
							'valid_ext' => '',
							'max_size' => '',
							'query' => '',
							'error' => 0,
						);
						if (is_uploaded_file($_FILES[$source_file]['tmp_name'][$i])) {
							if (stristr($valid_ext, ',')) {
								$valid_ext = explode(",", $valid_ext);
							} elseif (stristr($valid_ext, '|')) {
								$valid_ext = explode("|", $valid_ext);
							} else {
								$this->stop();
								addNotice('warning', 'Fusion Dynamics invalid accepted extension format. Please use either | or ,');
							}
							$file = $_FILES[$source_file];
							$file_type = $file['type'][$i];
							if ($target_file == "" || preg_match("/[^a-zA-Z0-9_-]/", $target_file)) {
								$target_file = stripfilename(substr($file['name'][$i], 0, strrpos($file['name'][$i], ".")));
							}
							$file_ext = strtolower(strrchr($file['name'][$i], "."));
							$file_dest = rtrim($target_folder, '/').'/';
							$upload_file = array(
								"source_file" => $source_file,
								"source_size" => $file['size'][$i],
								"source_ext" => $file_ext,
								"target_file" => $target_file.$file_ext,
								"target_folder" => $target_folder,
								"valid_ext" => $valid_ext,
								"max_size" => $max_size,
								"query" => $query,
								"error" => 0
							);
							if ($file['size'][$i] > $max_size) {
								// Maximum file size exceeded
								$upload['error'] = 1;
							} elseif (!in_array($file_ext, $valid_ext)) {
								// Invalid file extension
								$upload['error'] = 2;
							} else {
								$target_file = filename_exists($file_dest, $target_file.$file_ext);
								$upload_file['target_file'] = $target_file;
								move_uploaded_file($file['tmp_name'][$i], $file_dest.$target_file);
								if (function_exists("chmod")) {
									chmod($file_dest.$target_file, 0644);
								}
								if ($query && !dbquery($query)) {
									// Invalid query string
									$upload['error'] = 3;
									if (file_exists($file_dest.$target_file)) {
										unlink($file_dest.$target_file);
									}
								}
							}
							if ($upload['error'] !== 0) {
								if (file_exists($file_dest.$target_file.$file_ext)) {
									@unlink($file_dest.$target_file.$file_ext);
								}
							}
							$upload['source_file'][$i] = $upload_file['source_file'];
							$upload['source_size'][$i] = $upload_file['source_size'];
							$upload['source_ext'][$i] = $upload_file['source_ext'];
							$upload['target_file'][$i] = $upload_file['target_file'];
							$upload['target_folder'][$i] = $upload_file['target_folder'];
							$upload['valid_ext'][$i] = $upload_file['valid_ext'];
							$upload['max_size'][$i] = $upload_file['max_size'];
							$upload['query'][$i] = $upload_file['query'];
							$upload['type'][$i] = $file_type;
						} else {
							// File not uploaded
							$upload['error'] = array("error" => 4);
						}
						if ($upload['error'] !== 0) {
							$this->stop();
							switch ($upload['error']) {
								case 1: // Maximum file size exceeded
									addNotice('danger', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
									self::setInputError($this->field_name);
									break;
								case 2: // Invalid File extensions
									addNotice('danger', sprintf($locale['df_417'], $this->field_config['valid_ext']));
									self::setInputError($this->field_name);
									break;
								case 3: // Invalid Query String
									addNotice('danger', $locale['df_422']);
									self::setInputError($this->field_name);
									break;
								case 4: // File not uploaded
									addNotice('danger', $locale['df_423']);
									self::setInputError($this->field_name);
									break;
							}
						}
					}
				}
				return $upload;
			} else {
				return array();
			}
		} else {
			if (!empty($_FILES[$this->field_config['input_name']]['name']) && is_uploaded_file($_FILES[$this->field_config['input_name']]['tmp_name']) && !defined('FUSION_NULL')) {
				$upload = upload_file($this->field_config['input_name'], $_FILES[$this->field_config['input_name']]['name'], $this->field_config['path'], $this->field_config['valid_ext'], $this->field_config['max_byte']);
				if ($upload['error'] != 0) {
					$this->stop(); // return FALSE
					switch ($upload['error']) {
						case 1: // Maximum file size exceeded
							addNotice('danger', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
							self::setInputError($this->field_name);
							break;
						case 2: // Invalid File extensions
							addNotice('danger', sprintf($locale['df_417'], $this->field_config['valid_ext']));
							self::setInputError($this->field_name);
							break;
						case 3: // Invalid Query String
							addNotice('danger', $locale['df_422']);
							self::setInputError($this->field_name);
							break;
						case 4: // File not uploaded
							addNotice('danger', $locale['df_423']);
							self::setInputError($this->field_name);
							break;
					}
				} else {
					return $upload;
				}
			} else {
				return FALSE;
			}
		}
	}

	/**
	 * Checks if is a valid URL
	 * require path.
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_url() {
		if ($this->field_config['required'] && !$this->field_value) self::setInputError($this->field_name);
		if ($this->field_value) {
			$url_parts = parse_url($this->field_value);
			if (!isset($url_parts['scheme']) && isset($url_parts['path'])) $this->field_value = 'http://'.$this->field_value;
			if (filter_var($this->field_value, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED) === FALSE) {
				return FALSE;
			} else {
				return $this->field_value;
			}
		}
	}

	/**
	 * Validate a checkbox
	 * If field Value is multiple checkbox, post value must be an array
	 * If field value is a radio, post value must not be an array
	 * If field value is a number, post value must be a boolean 1 or 0
	 */
	protected function verify_checkbox() {
		if ($this->field_config['required'] && !$this->field_value) self::setInputError($this->field_name);
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				$vars[] = stripinput($val);
			}
			$delimiter = (!empty($this->field_config['delimiter'])) ? $this->field_config['delimiter'] : ",";
			$value = implode($delimiter, $vars);
			return $value;
		} elseif (!empty($this->field_value)) {
			if (isnum($this->field_value)) {
				if ($this->field_value == 1) {
					return 1;
				} else {
					return 0;
				}
			} else {
				return stripinput($this->field_value);
			}
		} else {
			return FALSE;
		}
	}

	// Verify and upload image on success. Returns array on file, thumb and thumb2 file names
	// You can use this function anywhere whether bottom or top most of your codes - order unaffected

	protected function verify_image_upload() {
		global $locale;
		require_once INCLUDES."infusions_include.php";
		if ($this->field_config['multiple']) {
			$target_folder = $this->field_config['path'];
			$target_width = $this->field_config['max_width'];
			$target_height = $this->field_config['max_height'];
			$max_size = $this->field_config['max_byte'];
			$delete_original = $this->field_config['delete_original'];
			$thumb1 = $this->field_config['thumbnail'];
			$thumb2 = $this->field_config['thumbnail2'];
			$thumb1_ratio = 1;
			$thumb1_folder = $this->field_config['path'].$this->field_config['thumbnail_folder']."/";
			$thumb1_suffix = $this->field_config['thumbnail_suffix'];
			$thumb1_width = $this->field_config['thumbnail_w'];
			$thumb1_height = $this->field_config['thumbnail_h'];
			$thumb2_ratio = 0;
			$thumb2_folder = $this->field_config['path'].$this->field_config['thumbnail_folder']."/";
			$thumb2_suffix = $this->field_config['thumbnail2_suffix'];
			$thumb2_width = $this->field_config['thumbnail2_w'];
			$thumb2_height = $this->field_config['thumbnail2_h'];
			$query = '';
			if (!empty($_FILES[$this->field_config['input_name']]['name'])) {
				$result = array();
				for ($i = 0; $i <= count($_FILES[$this->field_config['input_name']]['name'])-1; $i++) {
					if (is_uploaded_file($_FILES[$this->field_config['input_name']]['tmp_name'][$i])) {
						$image = $_FILES[$this->field_config['input_name']];
						$target_name = $_FILES[$this->field_config['input_name']]['name'][$i];
						if ($target_name != "" && !preg_match("/[^a-zA-Z0-9_-]/", $target_name)) {
							$image_name = $target_name;
						} else {
							$image_name = stripfilename(substr($image['name'][$i], 0, strrpos($image['name'][$i], ".")));
						}
						$image_ext = strtolower(strrchr($image['name'][$i], "."));
						$image_res = array();
						if (filesize($image['tmp_name'][$i]) > 10 && @getimagesize($image['tmp_name'][$i])) {
							$image_res = @getimagesize($image['tmp_name'][$i]);
						}
						$image_info = array(
							"image" => FALSE,
							"image_name" => $image_name.$image_ext,
							"image_ext" => $image_ext,
							"image_size" => $image['size'],
							"image_width" => $image_res[0],
							"image_height" => $image_res[1],
							"thumb1" => FALSE,
							"thumb1_name" => "",
							"thumb2" => FALSE,
							"thumb2_name" => "",
							"error" => 0,
						);
						if ($image_ext == ".gif") {
							$filetype = 1;
						} elseif ($image_ext == ".jpg") {
							$filetype = 2;
						} elseif ($image_ext == ".png") {
							$filetype = 3;
						} else {
							$filetype = FALSE;
						}
						if ($image['size'][$i] > $max_size) {
							// Invalid file size
							$image_info['error'] = 1;
						} elseif (!$filetype || !verify_image($image['tmp_name'][$i])) {
							// Unsupported image type
							$image_info['error'] = 2;
						} elseif ($image_res[0] > $target_width || $image_res[1] > $target_height) {
							// Invalid image resolution
							$image_info['error'] = 3;
						} else {
							if (!file_exists($target_folder)) {
								mkdir($target_folder, 0755);
							}
							$image_name_full = filename_exists($target_folder, $image_name.$image_ext);
							$image_name = substr($image_name_full, 0, strrpos($image_name_full, "."));
							$image_info['image_name'] = $image_name_full;
							$image_info['image'] = TRUE;
							move_uploaded_file($image['tmp_name'][$i], $target_folder.$image_name_full);
							if (function_exists("chmod")) {
								chmod($target_folder.$image_name_full, 0755);
							}
							if ($query && !dbquery($query)) {
								// Invalid query string
								$image_info['error'] = 4;
								if (file_exists($target_folder.$image_name_full)) {
									@unlink($target_folder.$image_name_full);
								}
							} elseif ($thumb1 || $thumb2) {
								require_once INCLUDES."photo_functions_include.php";
								$noThumb = FALSE;
								if ($thumb1) {
									if ($image_res[0] <= $thumb1_width && $image_res[1] <= $thumb1_height) {
										$noThumb = TRUE;
										$image_info['thumb1_name'] = $image_info['image_name'];
										$image_info['thumb1'] = TRUE;
									} else {
										if (!file_exists($thumb1_folder)) {
											mkdir($thumb1_folder, 0755, TRUE);
										}
										$image_name_t1 = filename_exists($thumb1_folder, $image_name.$thumb1_suffix.$image_ext);
										$image_info['thumb1_name'] = $image_name_t1;
										$image_info['thumb1'] = TRUE;
										if ($thumb1_ratio == 0) {
											createthumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width, $thumb1_height);
										} else {
											createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb1_folder.$image_name_t1, $thumb1_width);
										}
									}
								}
								if ($thumb2) {
									if ($image_res[0] < $thumb2_width && $image_res[1] < $thumb2_height) {
										$noThumb = TRUE;
										$image_info['thumb2_name'] = $image_info['image_name'];
										$image_info['thumb2'] = TRUE;
									} else {
										if (!file_exists($thumb2_folder)) {
											mkdir($thumb2_folder, 0755, TRUE);
										}
										$image_name_t2 = filename_exists($thumb2_folder, $image_name.$thumb2_suffix.$image_ext);
										$image_info['thumb2_name'] = $image_name_t2;
										$image_info['thumb2'] = TRUE;
										if ($thumb2_ratio == 0) {
											createthumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width, $thumb2_height);
										} else {
											createsquarethumbnail($filetype, $target_folder.$image_name_full, $thumb2_folder.$image_name_t2, $thumb2_width);
										}
									}
								}
								if ($delete_original && !$noThumb) {
									unlink($target_folder.$image_name_full);
									$image_info['image'] = FALSE;
								}
							}
						}
					} else {
						$image_info = array("error" => 5);
					}
					if ($image_info['error'] != 0) {
						$this->stop(); // return FALSE if possible
						switch ($image_info['error']) {
							case 1: // Invalid file size
								addNotice('danger', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
								self::setInputError($this->field_name);
								break;
							case 2: // Unsupported image type
								addNotice('danger', sprintf($locale['df_417'], ".gif .jpg .png"));
								self::setInputError($this->field_name);
								break;
							case 3: // Invalid image resolution
								addNotice('danger', sprintf($locale['df_421'], $this->field_config['max_width'], $this->field_config['max_height']));
								self::setInputError($this->field_name);
								break;
							case 4: // Invalid query string
								addNotice('danger', $locale['df_422']);
								self::setInputError($this->field_name);
								break;
							case 5: // Image not uploaded
								addNotice('danger', $locale['df_423']);
								self::setInputError($this->field_name);
								break;
						}
						$result[$i] = $image_info;
					} else {
						$result[$i] = $image_info;
					}
				} // end for
				return $result;
			} else {
				return array();
			}
		} else {
			if (!empty($_FILES[$this->field_config['input_name']]['name']) && is_uploaded_file($_FILES[$this->field_config['input_name']]['tmp_name']) && !defined('FUSION_NULL')) {
				$upload = upload_image($this->field_config['input_name'], $_FILES[$this->field_config['input_name']]['name'], $this->field_config['path'], $this->field_config['max_width'], $this->field_config['max_height'], $this->field_config['max_byte'], $this->field_config['delete_original'], $this->field_config['thumbnail'], $this->field_config['thumbnail2'], 1, $this->field_config['path'].$this->field_config['thumbnail_folder']."/", $this->field_config['thumbnail_suffix'], $this->field_config['thumbnail_w'], $this->field_config['thumbnail_h'], 0, $this->field_config['path'].$this->field_config['thumbnail_folder']."/", $this->field_config['thumbnail2_suffix'], $this->field_config['thumbnail2_w'], $this->field_config['thumbnail2_h']);
				if ($upload['error'] != 0) {
					$this->stop();
					switch ($upload['error']) {
						case 1: // Invalid file size
							addNotice('danger', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
							self::setInputError($this->field_name);
							break;
						case 2: // Unsupported image type
							addNotice('danger', sprintf($locale['df_417'], ".gif .jpg .png"));
							self::setInputError($this->field_name);
							break;
						case 3: // Invalid image resolution
							addNotice('danger', sprintf($locale['df_421'], $this->field_config['max_width'], $this->field_config['max_height']));
							self::setInputError($this->field_name);
							break;
						case 4: // Invalid query string
							addNotice('danger', $locale['df_422']);
							self::setInputError($this->field_name);
							break;
						case 5: // Image not uploaded
							addNotice('danger', $locale['df_423']);
							self::setInputError($this->field_name);
							break;
					}
					return $upload;
				} else {
					return $upload;
				}
			} else {
				return array();
			}
		}
	}

	public function inputHasError($input_name) {
		if (isset($this->input_errors[$input_name])) return TRUE;
		return FALSE;
	}

	/**
	 * Override default error text with custom error text
	 * @note:
	 * We need this because dynamics error text is set to "Field cannot be left empty".
	 * eg: Register.php - user_name field, has 3-4 errors types. Username claimed, username have bad chars, etc. Error doesn not necessary mean empty.
	 * @param $input_name - field name
	 * @param $text       - your error text.
	 */
	public function setErrorText($input_name, $text) {
		add_to_jquery("$('#".$input_name."-help').text('".$text."');");
	}

	/**
	 * Token Sniffer
	 * Checks whether a post contains a valid token
	 */
	public function sniff_token() {
        $error = FALSE;
        if (!empty($_POST)) {
            // Check if a token is being posted and make sure is a string
            if (!isset($_POST['fusion_token']) || !isset($_POST['form_id']) || !is_string($_POST['fusion_token']) || !is_string($_POST['form_id'])) {
                $error = "Token was not posted";
            } elseif (!isset($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                $error = "Cannot find any token for this form";
                // Check if the token exists in storage
            } elseif (!in_array($_POST['fusion_token'], $_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
                $error = "Cannot find token in storage: " . stripinput($_POST['fusion_token']);
            } elseif (!self::verify_token(0)) {
                $error = "Token is invalid: " . stripinput($_POST['fusion_token']);
            }
        }
        // Check if any error was set
        if ($error !== FALSE) {
            // Flag the token as invalid
            global $defender;
            $defender->tokenIsValid = FALSE;
            // Flag that something went wrong
            $defender->stop();
            // Add Error Notices
            setError(2, $error, FUSION_SELF, FUSION_REQUEST, "");
            if ($this->debug) addNotice('danger', $error);
        }
    }

	/**
     * Plain Token Validation - executed at maincore.php through sniff_token() only.
	 * Makes thorough checks of a posted token, and the token alone. It does not unset token.
	 * @param int $post_time      The time in seconds before a posted form is accepted,
	 *                            this is used to prevent spamming post submissions
	 * @return bool
	 */
	private static function verify_token($post_time = 5) {
		global $locale, $userdata, $defender;
		$error = FALSE;
        $defender->debug = FALSE;
        $settings = fusion_get_settings();
		$token_data = explode(".", stripinput($_POST['fusion_token']));
		// check if the token has the correct format
		if (count($token_data) == 3) {
			list($tuser_id, $token_time, $hash) = $token_data;
			$user_id = (iMEMBER ? $userdata['user_id'] : 0);
            $algo = $settings['password_algorithm'];
			$salt = md5(isset($userdata['user_salt']) && !isset($_POST['login']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
			// check if the logged user has the same ID as the one in token
			if ($tuser_id != $user_id) {
				$error = $locale['token_error_4'];
				// make sure the token datestamp is a number
			} elseif (!isnum($token_time)) {
				$error = $locale['token_error_5'];
				// check if the hash is valid
			} elseif ($hash != hash_hmac($algo, $user_id.$token_time.stripinput($_POST['form_id']).SECRET_KEY, $salt)) {
				$error = $locale['token_error_7'];
				// check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
			} elseif (time()-$token_time < $post_time) {
				$error = $locale['token_error_6'];
			}
		} else {
			// token format is incorrect
			$error = $locale['token_error_8'];
		}
		// Check if any error was set
		if ($error !== FALSE) {
            $defender->stop();
			if ($defender->debug) addNotice('danger', $error);
			return FALSE;
        } else {
            if ($defender->safe()) {
                array_shift($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']]);
            }
        }
		// If we made it so far everything is good
		if ($defender->debug) addNotice('info', 'The token for "'.stripinput($_POST['form_id']).'" has been validated successfully');
		return TRUE;
	}

}

// End of defender class

function form_sanitizer($value, $default = "", $input_name = FALSE, $multilang = FALSE) {
	global $defender;
	if ($input_name) {
		$val = array();
		if ($multilang) {
			//$main_field_name = ''; $main_field_id = '';
			// copy the first available value to the next one.
			foreach (fusion_get_enabled_languages() as $lang => $language) {
				$iname = $input_name."[".$lang."]";
				if (isset($_SESSION['form_fields'][$_SERVER['PHP_SELF']][$iname])) {
					$defender->field_config = $_SESSION['form_fields'][$_SERVER['PHP_SELF']][$iname];
					if ($lang == LANGUAGE) {
						$main_field_name = $defender->field_config['title'];
						$main_field_id = $defender->field_config['id'];
					}
					$defender->field_name = $iname;
					$defender->field_value = $value[$lang];
					$defender->field_default = $default;
					$val[$lang] = $defender->validate();
				}
			}
			if ($defender->field_config['required'] && (!$value[LANGUAGE])) {
				//$helper_text = $defender->field_config['error_text'] ? : sprintf($locale['df_error_text'], $main_field_name);
				$defender->stop();
				//$defender->addError($main_field_id);
				//$defender->addHelperText($main_field_id, $helper_text);
				//$defender->addNotice($helper_text);
			} else {
				foreach ($val as $lang => $value) {
					if (empty($value)) {
						$val[$lang] = $val[LANGUAGE];
					}
				}
				return serialize($val);
			}
		} else {
			// Make sure that the input was actually defined in code..
			// AND there must be a value to worth the processing power expense!
			if (isset($_SESSION['form_fields'][$_SERVER['PHP_SELF']][$input_name])) {
				$defender->field_config = $_SESSION['form_fields'][$_SERVER['PHP_SELF']][$input_name];
				$defender->field_name = $input_name;
				$defender->field_value = $value;
				$defender->field_default = $default; // to be removed
				// These two checks won't be neccesary after we add the options in all inputs
				// NOTE: Please don't pass 'stripinput' as callback, before we reach a callback
				// everything is checked and sanitized already. The callback should only check
				// if certain conditions are met then return TRUE|FALSE and not do any alterations
				// the the value itself
				$callback = isset($defender->field_config['callback_check']) ? $defender->field_config['callback_check'] : FALSE;
				$regex = isset($defender->field_config['regex']) ? $defender->field_config['regex'] : FALSE;
				$finalval = $defender->validate();
				// If truly FALSE the check failed
				if ($finalval === FALSE || ($defender->field_config['required'] == 1 && ($finalval === FALSE || $finalval == '')) ||
					($finalval != '' && $regex && !preg_match('@^'.$regex.'$@i', $finalval)) || // regex will fail for an imploded array, maybe move this check
					(is_callable($callback) && !$callback($finalval))
				) {
					// Flag that something went wrong
					$defender->stop();
					// Add regex error message.
					if ($finalval != '' && $regex && !preg_match('@^'.$regex.'$@i', $finalval)){
						global $locale;
						$defender->setInputError($input_name);
						addNotice("danger", sprintf($locale['regex_error'], $defender->field_config['title']));
						unset($locale);
					}
					// Add a notice
					if ($defender->debug) addNotice('warning', '<strong>'.$input_name.':</strong>'.($defender->field_config['safemode'] ? ' is in SAFEMODE and the' : '').' check failed');
					// Return user's input for correction
					return $defender->field_value;
				} else {
					if ($defender->debug) addNotice('info', $input_name.' = '.(is_array($finalval) ? 'array' : $finalval));
					return $finalval;
				}
			} else {
				// The input was not defined in code, the default value will be returned
				// Default value is most of the times data previously saved in DB
				return $default;
			}
		}
	} else {
		// returns descript, sanitized value.
		if ($value) {
			if (!is_array($value)) {
				if (intval($value)) {
					return stripinput($value); // numbers
				} else {
					return stripinput(trim(preg_replace("/ +/i", " ", censorwords($value))));
				}
			} else {
				$secured = array();
				foreach ($value as $arr => $unsecured) {
					if (intval($unsecured)) {
						$secured[] = stripinput($unsecured); // numbers
					} else {
						$secured[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($unsecured))));
					}
				}
				// might want to serialize output in the future if $_POST is an array
				// return addslash(serialize($secured));
				return implode($defender->field_config['delimiter'], $secured);
			}
		} else {
			return $default;
		}
	}
	throw new \Exception('The form sanitizer could not handle the request! (input: '.$input_name.')');
}

function sanitize_array($array) {
	foreach ($array as $name => $value) {
		$array[stripinput($name)] = trim(censorwords(stripinput($value)));
	}
	return $array;
}
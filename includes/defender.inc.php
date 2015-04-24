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

// Notes regarding further development:
// - The check functions should return the value being passed to
// as is or pre-processed(sanitized) or TRUE upon success and
// should not make direct calls to stop() on a failure but rather
// return FALSE, form sanitizer will do the rest.
// - Don't traslate/localise debug notices, is unnecessary.

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
	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function validate() {
		global $locale;

		// Are there situations were inputs could have leading
		// or trailing spaces? If not then uncomment line below
		//$this->field_value = trim($this->field_value);

		// Don't bother processing and validating empty inputs
		if ($this->field_value == '') return $this->field_value;

		/**
		 * Keep this include in the constructor
		 * This solution was needed to load the defender.inc.php before
		 * defining LOCALESET
		 */
		include_once LOCALE.LOCALESET."defender.php";

		// declare the validation rules and assign them
		// type of fields vs type of validator
		$validation_rules_assigned = array(
			'color'		=> 'textbox',
			'dropdown'	=> 'textbox',
			'text'		=> 'textbox',
			'textarea'	=> 'textbox',
			'textbox'	=> 'textbox',
			'checkbox'	=> 'checkbox',
			'password'	=> 'password',
			'date'		=> 'date',
			'timestamp'	=> 'date',
			'number'	=> 'number',
			'email'		=> 'email',
			'address'	=> 'address',
			'name'		=> 'name',
			'url'		=> 'url',
			'image'		=> 'image',
			'file'		=> 'file',
			'document'	=> 'document',
		);
		// execute sanitisation rules at point blank precision using switch
		try {
			if (!empty($this->field_config['type'])) {
				switch ($validation_rules_assigned[$this->field_config['type']]) {
					case 'textbox':
						return $this->verify_text();
						break;
					// DEV: To be reviewed
					case 'date':
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
						// Should it be able to actually process the values of checkboxes?
						if (isset($_POST[$this->field_name])) {
							return 1;
							/*if ($this->field_config['subtype'] == 'number') {
								return $this->verify_number();
							}
							return $this->verify_text();*/
						} else {
							// If a checkbox is not posted we assume that is unchecked
							// and return 0 instead of using the default value from DB
							return 0;
						}
						break;
					// DEV: To be reviewed
					case 'name':
						$name = $this->field_name;

						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-firstname', $locale['firstname_error']);
							//addNotice('info', $locale['firstname_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][1]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-lastname', $locale['lastname_error']);
							//addNotice('info', $locale['lastname_error']);
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->verify_text();
							return $return_value;
						}
						break;
					// DEV: To be reviewed
					case 'address':
						$name = $this->field_name;
						//$def = $this->get_full_options($this->field_config);
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-street', $locale['street_error']);
							//addNotice('info', $locale['street_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][2]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-country', $locale['country_error']);
							//addNotice('info', $locale['country_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][3]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-state', $locale['state_error']);
							//addNotice('info', $locale['state_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][4]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-city', $locale['city_error']);
							//addNotice('info', $locale['city_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][5]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-postcode', $locale['postcode_error']);
							//addNotice('info', $locale['postcode_error']);
						}

						if (!defined('FUSION_NULL')) {

							$return_value = $this->verify_text();
							var_dump($return_value);
							return $return_value;
						}
						break;
					// DEV: To be reviewed
					case 'image' :
						return $this->verify_image_upload();
						break;
					// DEV: To be reviewed
					case 'document':
						$name = $this->field_name;
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-doc_type', $locale['doc_type_error']);
							//addNotice('info', $locale['doc_type_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][1]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-doc_series', $locale['doc_series_error']);
							//addNotice('info', $locale['doc_series_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][2]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-doc_number', $locale['doc_number_error']);
							//addNotice('info', $locale['doc_number_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][3]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-doc_authority', $locale['doc_authority_error']);
							//addNotice('info', $locale['doc_authority_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][4]) {
							$this->stop();
							//$this->addHelperText($this->field_config['id'].'-date_issue', $locale['date_issue_error']);
							//addNotice('info', $locale['date_issue_error']);
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->verify_text();
							return $return_value;
						}
						break;
					default:
						$this->stop();
						$locale['type_unknown'] = '%s: has an unknown type set'; // to be moved
						addNotice('danger',  $this->field_name.$locale['type_unknown']);
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

	// Generates a md5 hash of the current page
	// Used to make token session array more unique in order
	// to avoid validation pass of tokens in forms for which
	// they weren't intended/generated
	private static function pageHash() {
		return md5(FUSION_REQUEST);
	}

	// Checks whether an input was marked as invalid
	public function inputHasError($input_name) {
		if (isset($this->input_errors[$input_name])) return TRUE;
		return FALSE;
	}

	// Marks an input as invalid
	public function setInputError($input_name) {
		$this->input_errors[$input_name] = TRUE;
	}

	/**
	 * ID for Session
	 * No $userName because it can be changed and tampered via Edit Profile.
	 * Using IP address extends for guest
	 * @return mixed
	 */
	static function set_sessionUserID() {
		global $userdata;
		return isset($userdata['user_id']) && !isset($_POST['login']) ? (int) $userdata['user_id'] : str_replace('.', '-', USER_IP);
	}

	// Adds the field sessions on document load
	static function add_field_session(array $array) {
		$_SESSION['form_fields'][$_SERVER['PHP_SELF']][$array['input_name']] = $array;
	}

	// Destroys the user field session
	public static function unset_field_session() {
		unset($_SESSION['form_fields']);
	}

	// Inject FUSION_NULL
	static function stop() {
		global $locale;
		if (!defined('FUSION_NULL')) {
			addNotice('danger', $locale['error_request']);
		}
		if (!defined('FUSION_NULL')) define('FUSION_NULL', TRUE);
	}

	// Field Verifications Rules

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
			$value = implode('|', $vars);
		} else {
			$value = stripinput(trim(preg_replace("/ +/i", " ", censorwords($this->field_value)))); // very strong sanitization.
		}
		
		if ($this->field_config['safemode'] && !preg_check("/^[-0-9A-Z_@\s]+$/i", $value)) {
			return FALSE;
		} else {
			return $value;
		}
	}

	/**
	 * Checks if is a valid email address
	 * accepts only 50 characters + @ + 4 characters
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_email() {
		// TODO: This regex was reported previously as flawed and should be reviewed and fixed
		if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $this->field_value)) {
			return $this->field_value;
		}

		return FALSE;
	}

	/**
	 * Checks if is a valid password
	 * accepts minimum of 8 and maximum of 64 due to encrypt limit
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_password() {
		// add min length, add max length, add strong password into roadmaps.
		if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $this->field_value)) {
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
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				if (isnum($val)) $vars[] = $val; // no need for stripinput(), if ain't a number why bother stripping invalid chars...
			}
			$value = implode(',', $vars);
			return $value; // empty str is returned if $vars ends up empty
		} elseif (isnum($this->field_value)) {
			return $this->field_value;
		} else {
			return FALSE;
		}
	}

	/**
	 * Checks if is a valid URL
	 * require path.
	 * returns str the input or bool FALSE if check fails
	 */
	protected function verify_url() {

		$url_parts = parse_url($this->field_value);
		// If no scheme/protocol is found but a path is present then let's add a protocol,
		// chances are the user won't even know he has to add a protocol for the url to validate
		if (!isset($url_parts['scheme']) && isset($url_parts['path'])) $this->field_value = 'http://'.$this->field_value;

		// Make sure the URL is valid
		if (filter_var($this->field_value, FILTER_VALIDATE_URL)) {
			return $this->field_value;
			//return cleanurl($this->field_value);
		}

		return FALSE;
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
				//$this->addHelperText($this->field_config['id'], sprintf($locale['df_404'], $this->field_config['title']));
				addNotice('info', sprintf($locale['df_404'], $this->field_config['title']));
			}
		} else {
			return $this->field_default;
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
				for($i = 0; $i <= count($_FILES[$this->field_config['input_name']]['name'])-1; $i++) {
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
								unlink($target_folder.$image_name_full);
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
								//$this->addHelperText($this->field_config['id'], sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
								break;
							case 2: // Unsupported image type
								addNotice('danger', sprintf($locale['df_417'], ".gif .jpg .png"));
								//$this->addHelperText($this->field_config['id'], sprintf($locale['df_417'], ".gif .jpg .png"));
								break;
							case 3: // Invalid image resolution
								addNotice('danger', sprintf($locale['df_421'], $this->field_config['max_width']." x ".$this->field_config['max_height']));
								//$this->addHelperText($this->field_config['id'], sprintf($locale['df_421'], $this->field_config['max_width'], $this->field_config['max_height']));
								break;
							case 4: // Invalid query string
								addNotice('danger', $locale['df_422']);
								//$this->addHelperText($this->field_config['id'], $locale['df_422']);
								break;
							case 5: // Image not uploaded
								addNotice('danger', $locale['df_423']);
								//$this->addHelperText($this->field_config['id'], $locale['df_423']);
								break;
						}
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
							addNotice('info', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
							//$this->addHelperText($this->field_config['id'], sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
							break;
						case 2: // Unsupported image type
							addNotice('info', sprintf($locale['df_417'], ".gif .jpg .png"));
							//$this->addHelperText($this->field_config['id'], sprintf($locale['df_417'], ".gif .jpg .png"));
							break;
						case 3: // Invalid image resolution
							addNotice('info', sprintf($locale['df_421'], $this->field_config['max_width']." x ".$this->field_config['max_height']));
							//$this->addHelperText($this->field_config['id'], sprintf($locale['df_421'], $this->field_config['max_width']." x ".$this->field_config['max_height']));
							break;
						case 4: // Invalid query string
							addNotice('info', $locale['df_422']);
							//$this->addHelperText($this->field_config['id'], $locale['df_422']);
							break;
						case 5: // Image not uploaded
							addNotice('info', $locale['df_423']);
							//$this->addHelperText($this->field_config['id'], $locale['df_423']);
							break;
					}
				} else {
					return $upload;
				}
			} else {
				return array();
			}
		}
	}

	/** @noinspection PhpInconsistentReturnPointsInspection */
	protected function verify_file_upload() {
		global $locale;
		require_once INCLUDES."infusions_include.php";
		if (!empty($_FILES[$this->field_config['input_name']]['name']) && is_uploaded_file($_FILES[$this->field_config['input_name']]['tmp_name']) && !defined('FUSION_NULL')) {
			$upload = upload_file($this->field_config['input_name'], $_FILES[$this->field_config['input_name']]['name'], $this->field_config['path'], $this->field_config['valid_ext'], $this->field_config['max_byte']);
			if ($upload['error'] != 0) {
				$this->stop(); // return FALSE
				switch ($upload['error']) {
					case 1: // Maximum file size exceeded
						addNotice('info', sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
						//$this->addHelperText($$this->field_config['id'], $locale['df_416']);
						break;
					case 2: // Invalid File extensions
						addNotice('info', sprintf($locale['df_417'], $this->field_config['valid_ext']));
						//$this->addHelperText($$this->field_config['id'], $locale['df_417']);
						break;
					case 3: // Invalid Query String
						addNotice('info', $locale['df_422']);
						//$this->addHelperText($$this->field_config['id'], $locale['df_422']);
						break;
					case 4: // File not uploaded
						addNotice('info', $locale['df_423']);
						//$this->addHelperText($$this->field_config['id'], $locale['df_423']);
						break;
				}
			} else {
				return $upload;
			}
		} else {
			return FALSE;
		}
	}


	/**
	 * Token Sniffer
	 * Checks whether a post contains a valid token
	 */
	public function sniff_token() {
		global $defender;
		$error = FALSE;
		if (!empty($_POST)) {
			// Check if a token is being posted and make sure is a string
			if (!isset($_POST['fusion_token']) || !isset($_POST['form_id']) || !is_string($_POST['fusion_token']) || !is_string($_POST['form_id'])) {
				$error = "Token was not posted";
			}
			if (!self::verify_token(0)) {
				$error = "Token is invalid: ".stripinput($_POST['fusion_token']);
				if (!isset($_SESSION['csrf_tokens'][self::pageHash()][$_POST['form_id']])) {
					$error = "Cannot find any token for this form - ".$_POST['form_id'];
				}
			}
		}

		// Check if any error was set
		if ($error) {
			// Flag the token as invalid
			$defender->tokenIsValid = FALSE;
			// Flag that something went wrong
			$this->stop();
			if ($this->debug) addNotice('danger', $error);
		}

	}

	/**
	 * Generate a Token
	 * Generates a unique token
	 * @param string $form_id		The ID of the form
	 * @param int    $max_tokens	The ammount of tokens to be kept for each form before we start removing older tokens from session
	 * @return string|string[]		The token
	 */
	public static function generate_token($form_id = 'phpfusion', $max_tokens = 10) {
		global $userdata, $defender;
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

			if ($defender->debug) addNotice('info', 'A new token for "'.$form_id.'" was generated : '.$token);

			if ($defender->debug) {
				//print_p("And we have ".count($_SESSION['csrf_tokens'][$form_id])." tokens in place...");
				//print_p("Max token allowed in $form_id is $max_tokens");
				if (defined('FUSION_NULL')) addNotice('danger', 'FUSION NULL is DECLARED');
				if (!empty($_SESSION['csrf_tokens'][self::pageHash()][$form_id])) {
					addNotice('danger', 'Current Token That is Going to be validated in this page: ');
					addNotice('danger', $_SESSION['csrf_tokens'][self::pageHash()][$form_id]);
				} else {
					addNotice('warning', 'There is no token for this page this round');
				}
			}
			// some cleaning, remove oldest token if there are too many
			if ($max_tokens > 0 && count($_SESSION['csrf_tokens'][self::pageHash()][$form_id]) > $max_tokens) {
				if ($defender->debug) addNotice('warning', 'Token that is <b>erased</b> '.$_SESSION['csrf_tokens'][self::pageHash()][$form_id][0].'. This token cannot be validated anymore.');
				array_shift($_SESSION['csrf_tokens'][self::pageHash()][$form_id]);
			}

			if ($defender->debug) {
				if (!empty($_SESSION['csrf_tokens'][self::pageHash()][$form_id])) {
					addNotice('danger', 'After clean up, the token remaining is: ');
					addNotice('danger', $_SESSION['csrf_tokens'][self::pageHash()][$form_id]);
				} else {
					addNotice('warning', 'There is no token for this page this round');
				}
			}
		}
		return $token;
	}

	/**
	 * Plain Token Validation
	 * Makes thorough checks of a posted token, and the token alone. It does not unset token.
	 *
	 * @param int	$post_time	The time in seconds before a posted form is accepted,
	 *							this is used to prevent spamming post submissions
	 * @return bool
	 */
	private static function verify_token($post_time = 5) {
		global $locale, $userdata, $defender;

		$error = FALSE;

		$token_data = explode(".", stripinput($_POST['fusion_token']));
		// check if the token has the correct format
		if (count($token_data) == 3) {

			list($tuser_id, $token_time, $hash) = $token_data;

			$user_id = (iMEMBER ? $userdata['user_id'] : 0);
			$algo = fusion_get_settings('password_algorithm');
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
			if ($defender->debug) addNotice('danger', $error);
			return FALSE;
		}
		
		// If we made it so far everything is good
		if ($defender->debug) addNotice('info', 'The token for "'.stripinput($_POST['form_id']).'" has been validated successfully');
		return TRUE;
	}
}

function form_sanitizer($value, $default = "", $input_name = FALSE, $multilang = FALSE) {
	global $defender;

	// DEV: To be reviewed
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
				foreach($val as $lang => $value) {
					if (empty($value)) {
						$val[$lang] = $val[LANGUAGE];
					}
				}
				return serialize($val);
			}
		} else {
			// Make sure that the input was actually defined in code
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
				if (	$finalval === FALSE ||
						($defender->field_config['required'] == 1 && ($finalval === FALSE || $finalval == '')) || // remove FALSE check?
						($finalval != '' && $regex && !preg_match('@^'.$regex.'$@i', $finalval)) || // regex will fail for an imploded array, maybe move this check
						(is_callable($callback) && !$callback($finalval))
				) {
					// Flag that something went wrong
					$defender->stop();
					// Mark this input as invalid
					$defender->setInputError($input_name);
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
	// DEV: To be reviewed
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
				// flatten array;
				$secured = array();
				foreach($value as $arr => $unsecured) {
					if (intval($unsecured)) {
						$secured[] = stripinput($unsecured); // numbers
					} else {
						$secured[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($unsecured))));
					}
				}
				// might want to serialize output in the future if $_POST is an array
				// return addslash(serialize($secured));
				return implode('.', $secured); // this is very different than defender's output, which is based on '|' delimiter
			}
		} else {
			return $default;
		}
	}

	//addNotice('warning', '<b> *** WARNING:</b> No input defined in source code for <b>'.$input_name.'</b>');
	throw new \Exception('The form sanitizer could not handle the request! (input: '.$input_name.')');
}

function sanitize_array($array) {
	foreach ($array as $name => $value) {
		$array[stripinput($name)] = trim(censorwords(stripinput($value)));
	}
	return $array;
}

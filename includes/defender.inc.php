<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 PHP-Fusion Inc.
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc.php
| Author : Frederick MC Chan (Hien)
| Version : 9.0.2 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

include LOCALE.LOCALESET."defender.php";

class defender {
	public $debug = FALSE;
	public $debug_notice = FALSE;
	public $ref = array();
	public $error_content = array();
	public $error_title = '';
	public $field = array();
	public $field_name = ''; // declared by form_sanitizer()
	public $field_value = ''; // declared by form_sanitizer()
	public $field_default = ''; // declared by form_sanitizer()
	public $field_config = array(
		'type' => '',
		'value' => '',
		'default' => '',
		'name' => '',
		'id' => '',
		'safemode' => '',
		// the file uploads
		'path'	=> '',
		'thumbnail_1' => '',
		'thumbnail_2' => '',
	); // declared by form_sanitizer()

	/* Sanitize Fields Automatically */
	public function defender() {
		global $locale;
		require_once INCLUDES."notify/notify.inc.php";
		$this->noAdminCookie();
		// declare the validation rules and assign them
		// type of fields vs type of validator
		$validation_rules_assigned = array(
			'textbox' => 'textbox',
			'dropdown' => 'textbox',
			'name' => 'textbox',
			'password' => 'password',
			'email' => 'email',
			'date' => 'date',
			'color' => 'textbox',
			'address' => 'address',
			'url'	=> 'url',
			'image' => 'image',
			'file'	=> 'file',
		);
		// execute sanitisation rules at point blank precision using switch
		try {
			if (!empty($this->field_config['type'])) {
				switch ($validation_rules_assigned[$this->field_config['type']]) {
					case 'textbox':
						return $this->verify_text();
						break;
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
					case 'file' :
						return $this->verify_file();
						break;
					case 'url' :
						return $this->verify_url();
						break;
					case 'address':
						$name = $this->field_name;
						//$def = $this->get_full_options($this->field_config);
						if ($this->field_config['required'] && !$_POST[$name][0]) {
							$this->stop();
							$this->addError($this->field_config['id'].'-street');
							$this->addHelperText($this->field_config['id'].'-street', $locale['street_error']);
							$this->addNotice($locale['street_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][2]) {
							$this->stop();
							$this->addError($this->field_config['id'].'-country');
							$this->addHelperText($this->field_config['id'].'-country', $locale['country_error']);
							$this->addNotice($locale['country_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][3]) {
							$this->stop();
							$this->addError($this->field_config['id'].'-state');
							$this->addHelperText($this->field_config['id'].'-state', $locale['state_error']);
							$this->addNotice($locale['state_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][4]) {
							$this->stop();
							$this->addError($this->field_config['id'].'-city');
							$this->addHelperText($this->field_config['id'].'-city', $locale['city_error']);
							$this->addNotice($locale['city_error']);
						}
						if ($this->field_config['required'] && !$_POST[$name][5]) {
							$this->stop();
							$this->addError($this->field_config['id'].'-postcode');
							$this->addHelperText($this->field_config['id'].'-postcode', $locale['postcode_error']);
							$this->addNotice($locale['postcode_error']);
						}
						if (!defined('FUSION_NULL')) {
							$return_value = $this->validate_text();
							return $return_value;
						}
						break;
					case 'image' :
						return $this->verify_image_upload();
						break;
					default:
						$this->stop();
						$this->addNotice($this->field_name);
						$this->addNotice(var_dump($this->field_config));
						$this->addNotice('Verification on unknown type of fields is prohibited.');

				}
			} else {
				return $this->field_default;
				//$this->stop();
				//$message = $this->field_name.' has no value.'; // this has no value and must pushed out.
				//$this->addNotice($message);
			}
		} catch (Exception $e) {
			$error_message = $e->getMessage();
			$this->stop();
			$this->addNotice($error_message);
		}

	}

	/* Adds the field sessions on document load */
	public function add_field_session(array $array) {
		global $userdata;
		$_SESSION['form_fields'][$userdata['user_id']][$_SERVER['PHP_SELF']][$array['input_name']] = $array;
	}

	/* Fetches your users field sessions so you can do anything with it */
	static function my_field_session() {
		global $userdata;
		return $_SESSION['form_fields'][$userdata['user_id']][$_SERVER['PHP_SELF']];
	}

	/* Destroys a users field session. use carefully */
	public function unset_field_session() {
		global $userdata;
		unset($_SESSION['form_fields'][$userdata['user_id']][$_SERVER['PHP_SELF']]);
	}

	/* Jquery Error Class Injector */
	public function addError($id) {
		// add class to id.
		add_to_jquery("$('#$id-field').addClass('has-error');");
	}

	public function noAdminCookie() {
		global $locale;
		$admin_cookie = COOKIE_PREFIX."admin";
		$input_password = '';
		if (defined('ADMIN_PANEL') && !isset($_COOKIE[$admin_cookie])) {
			if (isset($_POST['admin_login'])) {
				check_admin_pass($input_password);
			} else {
				redirect(FUSION_REQUEST."&amp;cookie_expired");
			}
		} elseif (isset($_GET['cookie_expired'])) {
			if (!isset($_COOKIE[$admin_cookie])) {
				notify($locale['cookie_title'], $locale['cookie_description']);
			} else {
				redirect(str_replace("&amp;cookie_expired", "", FUSION_REQUEST));
			}
		}
	}

	public function verify_tokens($form, $post_time = 10, $preserve_token = FALSE) {
		global $locale, $settings, $userdata;
		$error = array();
		$user_id = isset($userdata['user_id']) && !isset($_POST['login']) ? $userdata['user_id'] : 0;
		$algo = $settings['password_algorithm'];
		$salt = md5(isset($userdata['user_salt']) && !isset($_POST['login']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		if ($this->debug) {
			print_p($_POST);
		}
		// check if a session is started
		if (!isset($_SESSION['csrf_tokens'])) {
			$error = $locale['token_error_1'];
			$this->stop($locale['token_error_1']);
			// check if a token is posted
		} elseif (!isset($_POST['fusion_token'])) {
			$error = $locale['token_error_2'];
			$this->stop($locale['token_error_2']);
			// check if the posted token exists
		} elseif (!in_array($_POST['fusion_token'], isset($_SESSION['csrf_tokens'][$form]) ? $_SESSION['csrf_tokens'][$form] : array())) {
			$error = $locale['token_error_3'];
			$this->stop($locale['token_error_3']);
			// invalid token - will not accept double posting.
		} else {
			$token_data = explode(".", stripinput($_POST['fusion_token']));
			// check if the token has the correct format
			if (count($token_data) == 3) {
				list($tuser_id, $token_time, $hash) = $token_data;
				if ($tuser_id != $user_id) { // check if the logged user has the same ID as the one in token
					$error = $locale['token_error_4'];
					$this->stop($locale['token_error_4']);
				} elseif (!isnum($token_time)) { // make sure the token datestamp is a number before performing calculations
					$error = $locale['token_error_5'];
					$this->stop($locale['token_error_5']);
					// token is not a number.
				} elseif (time()-$token_time < $post_time) { // post made too fast. Set $post_time to 0 for instant. Go for System Settings later.
					$error = $locale['token_error_6'];
					$this->stop($locale['token_error_6']);
					// check if the hash in token is valid
				} elseif ($hash != hash_hmac($algo, $user_id.$token_time.$form.SECRET_KEY, $salt)) {
					$error = $locale['token_error_7'];
					$this->stop($locale['token_error_7']);
				}
			} else {
				// token incorrect format.
				$error = $locale['token_error_8'];
				$this->stop($locale['token_error_8']);
			}
		}
		// remove the token from the array as it has been used
		if ($post_time > 0) { // token with $post_time 0 are reusable
			foreach ($_SESSION['csrf_tokens'][$form] as $key => $val) {
				if (isset($_POST['fusion_token']) && $val == $_POST['fusion_token']) {
					unset($_SESSION['csrf_tokens'][$form][$key]);
				}
			}
		}
		if ($error) {
			if ($this->debug) {
				print_p($error);
			}
			return FALSE;
		}
		if ($this->debug) {
			print_p('Validate success');
			notify("Token Verification Success!", "The token on token ring has been passed and validated successfully.", array('icon' => 'notify_icon n-magic'));
		}
		return TRUE;
	}

	/* Append The Helper Text */
	public function addHelperText($id, $content) {
		// add prevention of double entry should the fields are the same id.
		if (!defined(".$id-help")) {
			define(".$id-help", TRUE);
			add_to_jquery("
                $('#$id-help').addClass('label label-danger m-t-5 p-5 display-inline-block');
                $('#$id-help').append('$content');
			");
		}
	}

	/* Except for blank $_POST, every single form in must have token - added to maincore */
	public function sniff_token() {
		global $locale;
		if (!empty($_POST)) {
			if (!isset($_POST['fusion_token'])) {
				$this->stop();
				$this->addNotice($locale['token_error_2']);
				if ($this->debug_notice) {
					print_p($locale['token_error_2']);
				}
			} else {
				// check token.
				if (isset($_POST['token_rings']) && !empty($_POST['token_rings'])) {
					foreach($_POST['token_rings'] as $hash => $form_name) {
						$this->verify_tokens($form_name, 0);
					}
				} else {
					// token tampered
					$this->stop();
					$this->addNotice($locale['token_error_2']);
					if ($this->debug_notice) {
						print_p($locale['token_error_2']." Tampered.");
					}
				}
			}
		}
	}

	/* Inject form notice */
	public function addNotice($content) {
		// add prevention of double entry should the fields are the same id.
		$this->error_content[] = $content;
		return $this->error_content;
	}

	public function setNoticeTitle($title) {
		$this->error_title = $title;
	}

	/* Aggregate notices */
	public function Notice() {
		if (isset($this->error_content)) {
			return $this->error_content;
		}
		return FALSE;
	}

	public function showNotice() {
		global $locale;
		$html = '';
		$title = $this->error_title ? $this->error_title : $locale['validate_title'];
		if (!empty($this->error_content)) {
			$html .= "<div id='close-message'>\n";
			$html .= "<div class='admin-message alert alert-danger alert-dismissable' role='alert'>\n";
			$html .= "<span class='text-bigger'><strong>".$title."</strong></p><br/>\n";
			$html .= "<ul id='error_list'>\n";
			foreach ($this->error_content as $notices) {
				$html .= "<li>".$notices."</li>\n";
			}
			$html .= "</ul>\n";
			$html .= "</div>\n</div>\n";
		}
		return $html;
	}

	/* Inject FUSION_NULL */
	public function stop($ref = FALSE) {
		if ($ref && $this->debug_notice) {
			notify('There was an error processing your request.', $ref);
		}
		if (!defined('FUSION_NULL')) {
			define('FUSION_NULL', TRUE);
		}
	}

	// Field Verifications Rules

	/* validate and sanitize a text
 	 * accepts only 50 characters + @ + 4 characters
 	 */
	private function verify_text() {
		global $locale;
		$return_value = ''; $value = '';
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				$vars[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($val))));
			}
			$value = implode('|', $vars); // this is where the pipe is.
		} else {
			$value = stripinput(trim(preg_replace("/ +/i", " ", censorwords($this->field_value)))); // very strong sanitization.
		}
		if ($this->field_config['safemode'] == 1) {
			if (!preg_check("/^[-0-9A-Z_@\s]+$/i", $this->field_value)) { // invalid chars
				$this->stop();
				$this->addError($this->field_config['id']);
				$this->addHelperText($this->field_config['id'], sprintf($locale['df_400'], $this->field_config['title'])); // maybe name, maybe
				$this->addNotice(sprintf($locale['df_400'], $this->field_config['title']));
			} else {
				$return_value = ($value) ? $value : $this->field_default;
				return $return_value;
			}
		} else {
			if ($value) {
				return $value;
			} else {
				return $this->field_default;
			}
		}
	}

	/* validate an email address
	 * accepts only 50 characters + @ + 4 characters
	 */
	private function verify_email() {
		global $locale;
		if ($this->field_value) {
			$value = stripinput(trim(preg_replace("/ +/i", " ", $this->field_value)));
			if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $value)) {
				return $value;
			} else {
				$this->stop();
				$this->addError($this->field_config['id']);
				$this->addHelperText($this->field_config['id'], sprintf($locale['df_401'], $this->field_config['name']));
				$this->addNotice(sprintf($locale['df_401'], $this->field_config['name']));
			}
		} else {
			return $this->field_default;
		}
	}

	/* validate a valid password
	 * accepts minimum of 8 and maximum of 64 due to encrypt limit
	 * returns a default if blank
	 */
	private function verify_password() {
		global $locale;
		// add min length, add max length, add strong password into roadmaps.
		if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $this->field_value)) {
			return $this->field_default;
		} else {
			// invalid password
			$this->stop();
			$this->addError($this->field_config['id']);
			$this->addHelperText($this->field_config['id'], sprintf($locale['df_402'], $this->field_config['name']));
			$this->addNotice(sprintf($locale['df_402'], $this->field_config['name']));
		}
	}

	/* validate a valid number
	 * accepts only integer and decimal .
	 * returns a default if blank
	 */
	private function verify_number() {
		global $locale;
		$value = '';
		if (is_array($this->field_value)) {
			$vars = array();
			foreach ($this->field_value as $val) {
				$vars[] = stripinput($val);
			}
			$value = implode(',', $vars);
		} else {
			$value = intval(stripinput($this->field_value));
		}

		if ($value) {
			if (is_numeric($this->field_value)) {
				return $this->field_value;
			} else {
				$this->stop();
				$this->addError($this->field_config['id']);
				$this->addHelperText($id, sprintf($locale['df_403'], $this->field_config['name']));
				$this->addNotice(sprintf($locale['df_403'], $this->field_config['name']));
			}
		} else {
			return $this->field_default;
		}
	}

	/* validate a valid url
	* require path.
	* returns a default if blank
	*/
	private function verify_url() {
		if ($this->field_value) {
			return filter_var($this->field_value, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);
			//return cleanurl($this->field_value);
		} else {
			return $this->field_default;
		}
	}

	/* returns 10 integer timestamp
	 * accepts date format in - , / and . delimiters
	 * returns a default if blank
	 */
	private function verify_date() {
		global $locale;
		//$news_start = isset($_POST['news_start']) && $_POST['news_start'] ? explode('-', $_POST['news_start']) : '';
		//$news_start_date = (!empty($news_start)) ? mktime(0, 0, 0, $news_start[1], $news_start[0], $news_start[2]) : '';
		// pair each other to determine which is month.
		// the standard value for dynamics is day-month-year.
		if ($this->field_value) {
			if (stristr($this->field_value, '-')) {
				$this->field_value = explode('-', $this->field_value);
			} elseif (stristr($this->field_value, '/')) {
				$this->field_value = explode('/', $this->field_value);
			} else {
				$this->field_value = explode('.', $this->field_value);
			}
			if (checkdate($this->field_value[1], $this->field_value[0], $this->field_value[2])) {
				return mktime(0, 0, 0, $this->field_value[1], $this->field_value[0], $this->field_value[2]);
			} else {
				$this->stop();
				$this->addError($this->field_config['id']);
				$this->addHelperText($this->field_config['id'], sprintf($locale['df_404'], $this->field_config['title']));
				$this->addNotice(sprintf($locale['df_404'], $this->field_config['title']));
			}
		} else {
			return $this->field_default;
		}
	}

	/* Verify and upload image on success. Returns array on file, thumb and thumb2 file names */
	/* You can use this function anywhere whether bottom or top most of your codes - order unaffected */
	private function verify_image_upload() {
		global $locale;
		require_once INCLUDES."infusions_include.php";
		if (!empty($_FILES[$this->field_config['input_name']]['name']) && is_uploaded_file($_FILES[$this->field_config['input_name']]['tmp_name']) && !defined('FUSION_NULL')) {
			$upload = upload_image(	$this->field_config['input_name'],
									$_FILES[$this->field_config['input_name']]['name'],
									$this->field_config['path'],
									$this->field_config['max_width'],
									$this->field_config['max_height'],
									$this->field_config['max_byte'],
									$this->field_config['delete_original'],
									$this->field_config['thumbnail'],
									$this->field_config['thumbnail2'],
									1,
									$this->field_config['path'].'thumbs/',
									'_t1',
									$this->field_config['thumbnail_w'],
									$this->field_config['thumbnail_h'],
									0,
									$this->field_config['path'].'thumbs/',
									'_t2',
									$this->field_config['thumbnail2_w'],
									$this->field_config['thumbnail2_h']
						);
			if ($upload['error'] != 0) {
				$this->stop();
				$this->addError($this->field_config['id']);
				switch ($upload['error']) {
					case 1: // Invalid file size
						$this->addNotice(sprintf($locale['df_416'], parsebytesize($this->field_config['max_byte'])));
						$this->addHelperText($$this->field_config['id'], $locale['df_416']);
						break;
					case 2:	// Unsupported image type
						$this->addNotice(sprintf($locale['df_417'], ".gif .jpg .png"));
						$this->addHelperText($$this->field_config['id'], $locale['df_417']);
						break;
					case 3: // Invalid image resolution
						$this->addNotice(sprintf($locale['df_421'], $this->field_config['max_width']." x ".$this->field_config['max_height']));
						$this->addHelperText($$this->field_config['id'], $locale['df_421']);
						break;
					case 4: // Invalid query string
						$this->addNotice($locale['df_422']);
						$this->addHelperText($$this->field_config['id'], $locale['df_422']);
						break;
					case 5: // Image not uploaded
						$this->addNotice($locale['df_423']);
						$this->addHelperText($$this->field_config['id'], $locale['df_423']);
						break;
				}
			} else {
				return $upload;
			}
		} else {
			return array();
		}
	}





	private function verify_file() {
		global $settings, $locale;
		/*
		//@todo: To build the most complete File check ever on PHP-Fusion. Consolidate every code in one place. Add own logic.
		require_once INCLUDES."photo_functions_include.php";
		$true_file = $default;
		if ($value['name'] && is_uploaded_file($value['tmp_name'])) {
			if (isset($value['name'])) {
				require_once BASEDIR.'includes/mimetypes_include.php';
				$mimetypes = array();
				$errors = array();
				// copied from admin/photos.php
				$file_name = stripfilename(str_replace(" ", "_", strtolower(substr($value['name'], 0, strrpos($value['name'], ".")))));
				$file_ext = strtolower(strrchr($value['name'], "."));
				$file_info = pathinfo($value['name']);
				$extension = $file_info['extension'];
				$file_dest = $path;
				$max_size = '';
				$maxWidth = '';
				$maxHeight = '';
				if ($type == 'image') { //// Idea: possibly add more types in like video/audio - which can be declared by Dynamics Opts.
					$mimetypes = img_mimeTypes();
					$maxsize = $settings['photo_max_b']; // max amount size.
					$maxWidth = $settings['photo_max_w'];
					$maxHeight = $settings['photo_max_h'];
				} elseif ($type == 'system') {
					$mimetypes = mimeTypes();
					$acceptable_mime = explode(',', $settings['attachtypes']); // .7zip
					foreach ($acceptable_mime as $file_mime) {
						$files_mime = $mimetypes[ltrim($file_mime, '.')];
						if ($files_mime) {
							$mimetypes[] = $files_mime;
						}
					}
				} elseif ($type == 'all') {
					$mimetypes = mimeTypes(); // all
					$maxsize = $settings['attachmax']; // max amount size.
				}
				$allowed_ext = array();
				foreach ($mimetypes as $mime_type => $mime_hex) {
					$allowed_ext[] = $mime_type;
				}
				// name check
				if (!preg_match("/^[-0-9A-Z_\.\[\]]+$/i", $file_name)) {
					$errors[] = 1;
					$this->stop();
					$this->addError($id);
					$this->addHelperText($id, $locale['df_415']);
					$this->addNotice($locale['df_415']);
				}
				// filesize checking.
				if (($value['size'] >= $maxsize) || ($value['size'] == 0)) {
					$errors[] = 1;
					$this->stop(); // declare FUSION_NULL. Protect the SQL from being executed.
					$this->addError($id); // inject JS highlight the field ID dynamically.
					$this->addHelperText($id, sprintf($locale['df_416'], parsebytesize($maxsize))); // inject field containers
					$this->addNotice(sprintf($locale['df_416'], parsebytesize($maxsize))); // inject form with error text.
				}
				// first check on mime hex and then check for extensions.
				// This is Arda's code on maincore.php, copied but altered to not die(). Instead, set an error, and protect SQL with FUSION_NULL.
				if ($settings['mime_check']) {
					$mime_error = 0;
					if (array_key_exists($extension, $mimetypes)) {
						if (is_array($mimetypes[$extension])) {
							$valid_mimetype = FALSE;
							foreach ($mimetypes[$extension] as $each_mimetype) {
								if ($each_mimetype == $value['type']) {
									$valid_mimetype = TRUE;
									break;
								}
							}
							if (!$valid_mimetype) {
								$mime_error = 1;
							}
							unset($valid_mimetype);
						} else {
							if ($mimetypes[$extension] != $value['type']) {
								$mime_error = 1;
							}
						}
					}
					unset($file_info, $extension);
					if ($mime_error) {
						$errors[] = 1;
						$error_text = sprintf($locale['df_417'], implode(', ', $allowed_ext));
						$this->stop();
						$this->addError($id);
						$this->addHelperText($id, $error_text);
						$this->addNotice($error_text);
					}
				}
				// verify the image for malicious code.
				if ($type == 'image' && (!verify_image($value['tmp_name']))) {
					$errors[] = 1;
					$this->stop();
					$this->addError($id);
					$this->addHelperText($id, $locale['df_419']);
					$this->addNotice($locale['df_419']);
				}
				if (!file_exists($path)) {
					// Only available in 8.00
					if (!SAFEMODE && !file_exists($path)) {
						mkdir($path, 0755, TRUE);
					} else {
						$errors[] = 1;
						$this->stop();
						$this->addError($id);
						$this->addHelperText($id, $locale['df_420']);
						$this->addNotice($locale['df_420']);
					}
				}
				// No major big errors.
				if (count($errors) === 0) {
					// last check - on extension name. Error to ask for rename of file.
					if ((!in_array(ltrim($file_ext, '.'), $allowed_ext)) && (!empty($file_ext))) {
						$this->stop();
						$this->addError($id);
						$this->addHelperText($id, $locale['df_418']);
						$this->addNotice($locale['df_418']);
					} else {
						// Ok, no error and the file is perfectly normal.
						// Drop original filename, use Hash Algorithm by Domi.
						$ext = strrchr($value['name'], ".");
						$secret_rand = rand(1000000, 9999999);
						$hashed_filename = substr(md5($secret_rand), 8, 8);
						$true_file = image_exists($path, $hashed_filename.$ext);
						move_uploaded_file($value['tmp_name'], $path.$hashed_filename.$ext);
						chmod($path.$true_file, 0666);
						// ok for photo, we drop it if fail again.
						if ($type == 'image') {
							$image_file = @getimagesize($path.$true_file);
							if ($image_file[0] > $settings['photo_max_w'] || $image_file[1] > $settings['photo_max_h']) {
								unlink($path.$true_file);
								$this->stop();
								$this->addNotice(sprintf($locale['df_421'], $settings['photo_max_w'], $settings['photo_max_h']));
							} else {
								// generates a thumbnail folder on 8.00.
								if ($thumbnail && !file_exists($thumbnail)) {
									mkdir($thumbnail, 0755, TRUE);
								}
								if ($thumbnail) {
									$photo_thumb1 = image_exists($thumbnail, $hashed_filename."_t1".$ext);
									createthumbnail($image_file[2], $path.$true_file, $thumbnail.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
									if ($image_file[0] > $settings['photo_w'] || $image_file[1] > $settings['photo_h']) {
										// rewrite the image since both name is same.
										$photo_thumb2 = image_exists($thumbnail, $hashed_filename."_t2".$ext);
										createthumbnail($image_file[2], $path.$true_file, $thumbnail.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
									}
								}
							}
						}
					}
				}
				return $true_file;
			}
		} else {
			return $this->field_default;
		}
		*/
	}
	// end class
}

function form_sanitizer($value, $default = "", $input_name = FALSE) {
	global $userdata, $defender;
	if ($input_name) {
		if (isset($_SESSION['form_fields'][$userdata['user_id']][$_SERVER['PHP_SELF']][$input_name])) {
			$defender->field_config = $_SESSION['form_fields'][$userdata['user_id']][$_SERVER['PHP_SELF']][$input_name];
			$defender->field_name = $input_name;
			$defender->field_value = $value;
			$defender->field_default = $default;
			//$data = $defender->get_full_options($defender->field_config);
			if ($defender->field_config['required'] == 1 && (!$value)) { // it is required field but does not contain any value.. do reject.
				$defender->stop();
				$defender->addError($defender->field_config['id']);
				$defender->addHelperText($defender->field_config['id'], $defender->field_config['error_text']);
				$defender->addNotice($defender->field_config['error_text']);
			} else {
				$val = $defender->defender();
				return $val;
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
				// flatten array;
				$merged = array_shift($value); // for one field which has a nested array;
				return $merged;
			}
		} else {
			return $default;
		}
	}
}

function sanitize_array($array) {
	foreach ($array as $name => $value) {
		$array[stripinput($name)] = trim(censorwords(stripinput($value)));
	}
	return $array;
}

function generate_token($form, $max_tokens = 10, $return_token = FALSE) {
	global $settings, $userdata, $defender;
	$being_posted = 0;
	if (isset($_POST['token_rings']) && count($_POST['token_rings'])) {
		foreach ($_POST['token_rings'] as $rings => $form_name) {
			if ($form_name == $form) {
				$being_posted = 1;
			}
		}
	}
	// reuse a posted token if is valid instead of generating a new one - fixed: reuse on the form that is being posted only. Generate new on all others.
	if (isset($_POST['fusion_token']) && $being_posted && $defender->verify_tokens($form, $max_tokens)) {  // will delete max token out. hence flush out previous token..
		$token = stripinput($_POST['fusion_token']);
	} else {
		$user_id = (isset($userdata['user_id']) ? $userdata['user_id'] : 0);
		$token_time = time();
		$algo = $settings['password_algorithm'];
		$key = $user_id.$token_time.$form.SECRET_KEY;
		$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		// generate a new token and store it
		$token = $user_id.".".$token_time.".".hash_hmac($algo, $key, $salt);
		// $max_tokens override for guest.
		if ($user_id == 0) { $max_tokens = 1; }
		// generate a new token.
		$_SESSION['csrf_tokens'][$form][] = $token;
		// store just one token for each form, if the user is a guest
		//print_p("Max token allowed in $form is $max_tokens");
		if ($max_tokens > 0 && count($_SESSION['csrf_tokens'][$form]) > $max_tokens) {
			array_shift($_SESSION['csrf_tokens'][$form]); // remove first element - this keeps changing
		}
		//print_p("And we have ".count($_SESSION['csrf_tokens'][$form])." tokens in place...");
	}
	$html = '';
	$shuffle = str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890");
	if ($return_token == 1) {
		return $token;
	} else {
		if (!defined("TOKEN-$shuffle")) {
			define("TOKEN-$shuffle", TRUE);
			$html .= "<input type='hidden' name='fusion_token' value='$token' />\n"; // form token
			$html .= "<input type='hidden' name='token_rings[$shuffle]' value='$form' />\n";
		}
	}
	return $html;
}

?>
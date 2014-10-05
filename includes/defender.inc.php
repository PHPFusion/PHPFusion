<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 PHP-Fusion Inc.
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: defender.inc.php
| Author : Frederick MC Chan (Hien)
| Version : 9.0.0 (please update every commit)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

require_once INCLUDES."notify/notify.inc.php";
include LOCALE.LOCALESET."defender.php";

class defender {
	public $debug = FALSE;
	public $ref = array();

	/* Sanitize Fields Automatically */
	public function defender($type = FALSE, $value = FALSE, $default = FALSE, $name = FALSE, $id = FALSE, $path = FALSE, $safemode = FALSE, $error_text = FALSE, $thumbnail = FALSE) {
		global $locale;
		$this->noAdminCookie();
		/* Validation of Files */
		if ($type == "textbox" || $type == 'dropdown' || $type == 'name' || $type == 'textarea') { // done.
			return $this->validate_text($value, $default, $name, $id, $safemode, $error_text);
		} elseif ($type == "color") {
			return $this->validate_text($value, $default, $name, $id, $safemode, $error_text);
			//return validate_color_field($value, $default, $name, $id); on 8.00 only
		} elseif ($type == "date") {
			return $this->validate_number($value, $default, $name, $id, $safemode, $error_text);
			//return validate_date_field($value, $default, $name, $id); on 8.00 only - outputs 10 int timestamp.
		} elseif ($type == "password") {
			return $this->validate_password($value, $default, $name, $id, $safemode, $error_text);
		} elseif ($type == "email") { // done
			return $this->validate_email($value, $default, $name, $id, $safemode, $error_text);
		} elseif ($type == "number") {
			return $this->validate_number($value, $default, $name, $id, $safemode, $error_text);
		} elseif ($type == "url") {
			return $this->validate_url($value, $default, $name, $id, $safemode, $error_text);
		} elseif ($type == 'image' || $type == 'file') {
			return $this->validate_file($value, $type, $path, $thumbnail, $default, $name, $id, $safemode, $error_text);
		} else {
			// default
			$return_value = (isset($value) && ($value !== "")) ? stripinput($value) : $default;
			return $return_value;
		}
	}

	/* Jquery Error Class Injector */
	public function addError($id) {
		// add class to id.
		add_to_jquery("
            $('#$id-field').addClass('has-error');
            ");
	}

	public function noAdminCookie() {
		global $locale;
		$admin_cookie = COOKIE_PREFIX."admin";
		$input_password = '';
		if (defined('ADMIN_PANEL') && !$_COOKIE[$admin_cookie]) {
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
		$user_id = (isset($userdata['user_id']) ? $userdata['user_id'] : 0);
		$algo = $settings['password_algorithm'];
		$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		if ($this->debug) {
			print_p($_POST);
		}
		// check if a session is started
		if (!isset($_SESSION['csrf_tokens'])) {
			$error[1] = $locale['token_error_1'];
			$this->stop($locale['token_error_1']);
			// check if a token is posted
		} elseif (!isset($_POST['fusion_token'])) {
			$error[2] = $locale['token_error_2'];
			$this->stop($locale['token_error_2']);
			// check if the posted token exists
		} elseif (!in_array($_POST['fusion_token'], isset($_SESSION['csrf_tokens'][$form]) ? $_SESSION['csrf_tokens'][$form] : array())) {
			$error[3] = $locale['token_error_3'];
			$this->stop($locale['token_error_3']);
			// invalid token - will not accept double posting.
		} else {
			$token_data = explode(".", stripinput($_POST['fusion_token']));
			// check if the token has the correct format
			if (count($token_data) == 3) {
				list($tuser_id, $token_time, $hash) = $token_data;
				if ($tuser_id != $user_id) { // check if the logged user has the same ID as the one in token
					$error = 1;
					$this->stop($locale['token_error_4']);
				} elseif (!isnum($token_time)) { // make sure the token datestamp is a number before performing calculations
					$error = 1;
					$this->stop($locale['token_error_5']);
					// token is not a number.
				} elseif (time()-$token_time < $post_time) { // post made too fast. Set $post_time to 0 for instant. Go for System Settings later.
					$error = 1;
					$this->stop($locale['token_error_6']);
					// check if the hash in token is valid
				} elseif ($hash != hash_hmac($algo, $user_id.$token_time.$form.SECRET_KEY, $salt)) {
					$error = 1;
					$this->stop($locale['token_error_7']);
				}
			} else {
				// token incorrect format.
				$error = 1;
				$this->stop($locale['token_error_8']);
			}
		}
		// remove the token from the array as it has been used
		if ($post_time > 0) { // token with $post_time 0 are reusable
			foreach ($_SESSION['csrf_tokens'][$form] as $key => $val) {
				if ($val == $_POST['fusion_token']) {
					unset($_SESSION['csrf_tokens'][$form][$key]);
				}
			}
		}
		if ($error) {
			return FALSE;
		}
		if ($this->debug) {
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
                $('#$id-help').addClass('label label-danger m-t-5 p-5 inline-block');
                $('#$id-help').append('$content');
                ");
		}
	}

	/* Inject form notice */
	public function addNotice($content) {
		// add prevention of double entry should the fields are the same id.
		$this->error_content[] = $content;
		return $this->error_content;
	}

	/* Except for blank $_POST, every single form in must have token. */
	public function sniff_token() {
		global $locale;
		if (!empty($_POST)) {
			if (!isset($_POST['fusion_token'])) {
				$this->stop();
				$this->addNotice($locale['token_error_2']);
			}
		}
	}

	/* Aggregate notices */
	public function Notice() {
		if (isset($this->error_content)) {
			return $this->error_content;
		}
		return FALSE;
	}

	public function showNotice() {
		$html = '';
		if (!empty($this->error_content)) {
			$html .= "<div id='close-message'>\n";
			$html .= "<div class='admin-message alert alert-warning alert-dismissable' role='alert'>\n";
			$html .= "<p><strong style='font-size:15px;'>Could you check something!</strong></p><br/>\n";
			$html .= "<ul id='error_list'>\n";
			foreach ($this->error_content as $notices) {
				$html .= "<li>$notices</li>\n";
			}
			$html .= "</ul>\n";
			$html .= "</div>\n</div>\n";
		}
		return $html;
	}

	/* Read Dynamics Data - Build Defense Config */
	public function defenseOpts($input_name) {
		$array = array();
		$array = construct_array($input_name);
		$data = array();
		foreach ($array as $ks => $vs) {
			$clean_up = str_replace("[", "", $vs);
			$clean_up = str_replace("]", "", $clean_up);
			$cdata[$input_name][] = construct_array($clean_up, "", "=");
		}
		foreach ($cdata[$input_name] as $arr => $v) {
			$data[$v['0']] = array_key_exists('1', $v) ? $v['1'] : '';
		}
		if ($data) {
			$opts['type'] = array_key_exists("type", $data) ? $data['type'] : "";
			$opts['name'] = array_key_exists("title", $data) ? rtrim($data['title'], ':') : "";
			$opts['id'] = array_key_exists("id", $data) ? $data['id'] : "";
			$opts['required'] = array_key_exists("required", $data) ? $data['required'] : 0;
			$opts['safemode'] = array_key_exists("safemode", $data) ? $data['safemode'] : 0;
			$opts['path'] = array_key_exists("path", $data) ? $data['path'] : '';
			$opts['thumbnail'] = array_key_exists("thumbnail", $data) ? $data['thumbnail'] : '';
			$opts['error_text'] = array_key_exists('error_text', $data) && $data['error_text'] ? $data['error_text'] : "".$opts['name']." needs your attention";
			return $opts;
		}
		return FALSE;
	}

	/* Inject FUSION_NULL */
	public function stop($ref = FALSE) {
		if ($ref && $this->debug) {
			notify('There was an error processing your request.', $ref);
		}
		if (!defined('FUSION_NULL')) {
			define('FUSION_NULL', TRUE);
		}
	}

	/* validation method */
	private function validate_text($value, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		global $locale;
		if (is_array($value)) {
			$vars = array();
			foreach ($value as $val) {
				$vars[] = stripinput(trim(preg_replace("/ +/i", " ", censorwords($val))));
			}
			$value = implode(',', $vars);
		} else {
			$value = stripinput(trim(preg_replace("/ +/i", " ", censorwords($value)))); // very strong sanitization.
		}
		if ($safemode == 1) {
			if (!preg_check("/^[-0-9A-Z_@\s]+$/i", $value)) { // invalid chars
				$this->stop();
				$this->addError($id);
				$this->addHelperText($id, sprintf($locale['df_400'], $name));
				$this->addNotice(sprintf($locale['df_400'], $name));
			} else {
				$return_value = ($value) ? $value : $default;
				return $return_value;
			}
		} else {
			if ($value) {
				return $value;
			} else {
				return $default;
			}
		}
	}

	private function validate_email($value, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		global $locale;
		$value = stripinput(trim(preg_replace("/ +/i", " ", $value)));
		if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $value)) {
			return $value;
		} else {
			$this->stop();
			$this->addError($id);
			$this->addHelperText($id, sprintf($locale['df_401'], $name));
			$this->addNotice(sprintf($locale['df_401'], $name));
		}
	}

	private function validate_password($value, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		global $locale;
		// no safemode
		if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $value)) {
			$return_value = (isset($value) && (($value) !== "")) ? $value : $default;
			return $return_value;
		} else {
			// invalid password
			$this->stop();
			$this->addError($id);
			$this->addHelperText($id, sprintf($locale['df_402'], $name));
			$this->addNotice(sprintf($locale['df_402'], $name));
		}
	}

	private function validate_number($value, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		global $locale;
		if (is_array($value)) {
			$vars = array();
			foreach ($value as $val) {
				$vars[] = stripinput($val);
			}
			$value = implode(',', $vars);
		} else {
			$value = stripinput($value);
		}

		if ($value) {
			if (is_numeric($value)) {
				return $value;
			} else {
				$this->stop();
				$this->addError($id);
				$this->addHelperText($id, sprintf($locale['df_403'], $name));
				$this->addNotice(sprintf($locale['df_403'], $name));
			}
		} else {
			if ($value) {
				return $value;
			} else {
				return $default;
			}
		}
	}

	private function validate_url($value, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		if (isset($value) && $value !== "") {
			return cleanurl($value);
		} else {
			return $default;
		}
	}

	private function validate_file($value, $type, $path, $thumbnail, $default, $name, $id, $safemode = FALSE, $error_text = FALSE) {
		global $settings, $locale;
		//@todo: To build the most complete File check ever on PHP-Fusion. Consolidate every code in one place. Add own logic.
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
				// check on folder exist for path. if not exist, crash again.
				if (!file_exists($path)) {
					$errors[] = 1;
					// Only available in 8.00
					//if (!file_exists($path) && $settings['generate_folder']) {
					//    mkdir($location, 0644, true);
					//} else {
					$this->stop();
					$this->addError($id);
					$this->addHelperText($id, $locale['df_420']);
					$this->addNotice($locale['df_420']);
					//}
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
								//if (!file_exists($path) && $settings['generate_thumbnail_folder']) {
								//  mkdir($path."thumbnail", 0644, true);
								//	$photo_thumb1 = image_exists($path, $true_file."_t1".$ext);
								//	createthumbnail($image_file[2], $path."thumbnail/".$true_file, $path."thumbnail/".$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
								//}
								if ($thumbnail) {
									$photo_thumb1 = image_exists($path, $hashed_filename."_t1".$ext);
									createthumbnail($image_file[2], $path.$true_file, $path.$photo_thumb1, $settings['thumb_w'], $settings['thumb_h']);
									if ($image_file[0] > $settings['photo_w'] || $image_file[1] > $settings['photo_h']) {
										// rewrite the image since both name is same.
										$photo_thumb2 = image_exists($path, $hashed_filename."_t2".$ext);
										createthumbnail($image_file[2], $path.$true_file, $path.$photo_thumb2, $settings['photo_w'], $settings['photo_h']);
									}
								}
							}
						}
					}
				}
				return $true_file;
			}
		} else {
			return $default;
		}
	}
	// end class
}

function form_sanitizer($value, $default = "", $input_name = FALSE) {
	global $_POST, $locale, $defender;
	//print_p($_POST['def']);
	// Standard Sanitization
	//$value = descript(stripinput($value));
	if ($input_name) { // must have input name to initiate defender.
		if (isset($_POST['def'][$input_name])) { // deprecate address config.
			// Strips Defence Tags.
			$data = $defender->DefenseOpts($_POST['def'][$input_name]);
			// already filter out required. validate doesn't need anymore.
			if ($data['required'] == 1 && (!$value)) { // it is required field but does not contain any value.. do reject.
				$defender->stop();
				$defender->addError($data['id']);
				$defender->addHelperText($data['id'], $data['error_text']);
				$defender->addNotice($data['error_text']);
			} else {
				//$type, $value, $default, $name, $id, $opts;
				$val = $defender->defender($data['type'], $value, $default, $data['name'], $data['id'], $data['path'], $data['safemode'], $data['safemode'], $data['error_text'], $data['thumbnail']);
				return $val;
			}
		} elseif (array_key_exists("single-multi", $_POST['def']) && isset($_POST['def']['single-multi'][$input_name])) {
			// pending for deprecation.
			// Make for multiple input [];
			/*
			if ($input_name) {

				// this is imploded.
				if (isset($_POST['def']['single-multi'][$input_name])) { // like no more already.
				   // $data = construct_array($_POST['def']['single-multi'][$input_name]);
					foreach ($data as $ks=>$vs) {
						$clean_up = str_replace("[", "", $vs);
						$clean_up = str_replace("]", "", $clean_up);
					//    $cdata[$input_name][] = construct_array($clean_up, "", "=");
					}
					unset($data);
					foreach ($cdata[$input_name] as $arr=>$v) {
						$data[$v['0']] = $v['1'];
					}
					if ($data) {
						$type = array_key_exists("type", $data) ? $data['type'] : "";
						$name = array_key_exists("title", $data) ? $data['title'] : "";
						$id = array_key_exists("id", $data) ? $data['id'] : "";
						$required = array_key_exists("required", $data) ? $data['required'] : 0;
						$safemode = array_key_exists("safemode", $data) ? $data['safemode'] : 0;
						$opts = array(
							"required"=>$required,
							"safemode"=>$safemode
						);
					}
					if (($opts['required'] == 1) && (!$value)) {
						if (!defined("FUSION_NULL")) {
							define("FUSION_NULL", true);
						}

						notify("The $name field seems to be blank", $locale['validate']);
						addError($id, "error");
						addContainerError($id, "error");
						addHelper($id, "This field cannot be empty", "$name field seems to be blank, and should not be empty. Please fill in the required details.", array("placement"=>"left"));
						addHelperText($id, "This field cannot be empty.");

					} else {
						if ($value) {
							foreach($value as $arr=>$ms_value) {
								$index[] = ''; //$defender->defender($type, $ms_value, $default, $name, $id, $opts); // this is not dangerous.
							}
					   //     $return = deconstruct_array($index, ",");
							return $return; // will be plaintext to sql.
						} else {
							return $default;
						}
					}
				} else {
					/* for <input name='xxx[]'>
					// print_p($input_name);
					// print_p($_POST[$input_name]);
					// sanitize directly here for multi text
					if ($_POST[$input_name]) {
						foreach ($_POST[$input_name] as $key=>$val) {
							$index[] = stripinput($val); // this is not dangerous.
						}
					   // $return = deconstruct_array($index, ",");
						return $return;
					} else {
						return $default;
					}
				}
			}

		} */
		}
	} else {
		// returns descript, sanitized value.
		if ($value) {
			if (!is_array($value)) {
				if (intval($value)) {
					return stripinput($value); // numbers
				} else {
					return addslash(trim(stripinput($value))); // text
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
		$array[stripinput($name)] = stripinput($value);
	}
	return $array;
}

function generate_token($form, $max_tokens = 10) {
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
	if (isset($_POST['fusion_token']) && $being_posted && $defender->verify_tokens($form, $max_tokens)) {
		$token = stripinput($_POST['fusion_token']);
	} else {
		$user_id = (isset($userdata['user_id']) ? $userdata['user_id'] : 0);
		$token_time = time();
		$algo = $settings['password_algorithm'];
		$key = $user_id.$token_time.$form.SECRET_KEY;
		$salt = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
		// generate a new token and store it
		$token = $user_id.".".$token_time.".".hash_hmac($algo, $key, $salt);
		$_SESSION['csrf_tokens'][$form][] = $token;
		// store just one token for each form, if the user is a guest
		if ($user_id == 0) {
			$max_tokens = 1;
		}
		// maximum number of tokens to be stored for each form
		if ($max_tokens > 0 && count($_SESSION['csrf_tokens'][$form]) > $max_tokens) {
			array_shift($_SESSION['csrf_tokens'][$form]); // remove first element
		}
	}
	$html = '';
	$shuffle = str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890");
	if (!defined("TOKEN-$shuffle")) {
		define("TOKEN-$shuffle", TRUE);
		$html .= "<input type='hidden' name='fusion_token' value='$token' readonly />\n"; // form token
		$html .= "<input type='hidden' name='token_rings[$shuffle]' value='$form' readonly />\n";
	}
	return $html;
}

?>
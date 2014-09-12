<?php

    /*-------------------------------------------------------+
    | PHP-Fusion Content Management System Version 8
    | Copyright (C) 2002 - 2014 PHP-Fusion Inc.
    | http://www.php-fusion.co.uk/
    +--------------------------------------------------------+
    | Filename: defender.inc.php
    | Author : Frederick MC Chan (Hien)
    | Version : 8.0.5 (please update every commit)
    +--------------------------------------------------------+
    | This program is released as free software under the
    | Affero GPL license. You can redistribute it and/or
    | modify it under the terms of this license which you
    | can read by viewing the included agpl.txt or online
    | at www.gnu.org/licenses/agpl.html. Removal of this
    | copyright header is strictly prohibited without
    | written permission from the original author(s).
    +--------------------------------------------------------*/
    $locale['validate'] = "Please check and revalidate the field.";
    require_once INCLUDES."notify/notify.inc.php";

    class defender {
        public $debug = FALSE;
        public $ref = array();

        /* Sanitize Fields Automatically */
        public function defender($type = FALSE, $value = FALSE, $default = FALSE, $name = FALSE, $id = FALSE, $required = FALSE, $safemode = FALSE, $error_text = FALSE, $path = FALSE, $maxsize = FALSE) {
            /* Validation of Files */
            if ($type == "textbox" || $type == 'dropdown' || $type == 'name' || $type == 'textarea') { // done.
                return $this->validate_text($value, $default, $name, $id, $required, $safemode, $error_text);
            } elseif ($type == "color") {
                //return validate_color_field($value, $default, $name, $id);
            } elseif ($type == "date") {
                //return validate_date_field($value, $default, $name, $id); // must go to timestamp.
            } elseif ($type == "password") {
                return $this->validate_password($value, $default, $name, $id, $required, $safemode, $error_text);
            } elseif ($type == "email") { // done
                return $this->validate_email($value, $default, $name, $id, $required, $safemode, $error_text);
            } elseif ($type == "number") {
                return $this->validate_number($value, $default, $name, $id, $required, $safemode, $error_text);
            } elseif ($type == "url") {
                return $this->validate_url($value, $default, $name, $id, $required, $safemode, $error_text);
            } elseif ($type == 'image' || $type == 'file') {
                if ($this->debug) {
                    //print_p($location); // this generates upload based on location.
                }
                return $this->validate_file($value, $type, $path, $maxsize, $default, $name, $id, $required, $safemode, $error_text);
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

        public function verify_tokens($form, $post_time = 10, $preserve_token = FALSE) {
            global $locale, $settings, $userdata;
            // we are using this as many times of the form included in this file?
            $error   = array();
            $user_id = (isset($userdata['user_id']) ? $userdata['user_id'] : 0);
            $algo    = $settings['password_algorithm'];
            $salt    = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
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
                        //$error[4] = $locale['token_error_4'];
                        $error = 1;
                        $this->stop($locale['token_error_4']);
                    } elseif (!isnum($token_time)) { // make sure the token datestamp is a number before performing calculations
                        //$error[5] = $locale['token_error_5'];
                        $error = 1;
                        $this->stop($locale['token_error_5']);
                        // token is not a number.
                    } elseif (time()-$token_time < $post_time) { // post made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                        //$error[6] = $locale['token_error_6'];
                        $error = 1;
                        $this->stop($locale['token_error_6']);
                        // check if the hash in token is valid
                    } elseif ($hash != hash_hmac($algo, $user_id.$token_time.$form.SECRET_KEY, $salt)) {
                        //$error[7] = $locale['token_error_7'];
                        $error = 1;
                        $this->stop($locale['token_error_7']);
                    }
                } else {
                    // token incorrect format.
                    //$error[8] = $locale['token_error_8'];
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
                foreach ($this->Notice() as $notices) {
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
            foreach ($array as $ks => $vs) {
                $clean_up             = str_replace("[", "", $vs);
                $clean_up             = str_replace("]", "", $clean_up);
                $cdata[$input_name][] = construct_array($clean_up, "", "=");
            }
            foreach ($cdata[$input_name] as $arr => $v) {
                $data[$v['0']] = array_key_exists('1', $v) ? $v['1'] : '';
            }
            if ($data) {
                $opts['type']       = array_key_exists("type", $data) ? $data['type'] : "";
                $opts['name']       = array_key_exists("title", $data) ? rtrim($data['title'], ':') : "";
                $opts['id']         = array_key_exists("id", $data) ? $data['id'] : "";
                $opts['required']   = array_key_exists("required", $data) ? $data['required'] : 0;
                $opts['safemode']   = array_key_exists("safemode", $data) ? $data['safemode'] : 0;
                $opts['error_text'] = array_key_exists('error_text', $data) && $data['error_text'] ? $data['error_text'] : "".$opts['name']." needs your attention";
                if (array_key_exists('path', $data) && $data['path']) {
                    $opts['path'] = $data['path'];
                }
                if (array_key_exists('maxsize', $data) && $data['maxsize']) {
                    $opts['maxsize'] = $data['maxsize'];
                }
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
        private function validate_text($value, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            if (is_array($value)) {
                $vars = array();
                foreach($value as $val) {
                    $vars[] = stripinput(trim(preg_replace("/ +/i", " ", $val)));
                }
                $value = implode(',', $vars);
            } else {
                $value = stripinput(trim(preg_replace("/ +/i", " ", $value))); // very strong sanitization.
            }
            if ($safemode == 1) {
                if (!preg_check("/^[-0-9A-Z_@\s]+$/i", $value)) { // invalid chars
                    $this->stop();
                    $this->addError($id);
                    $this->addHelperText($id, 'Invalid characters');
                    $this->addNotice("<b>$name</b> contains invalid characters");
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

        private function validate_email($value, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            $value = stripinput(trim(preg_replace("/ +/i", " ", $value)));
            if (preg_check("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $value)) {
                return $value;
            } else {
                $this->stop();
                $this->addError($id);
                $this->addHelperText($id, $error_text);
                $this->addNotice("<b>$name</b> is not a valid email address.");
            }
        }

        private function validate_password($value, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            // no safemode
            if (preg_match("/^[0-9A-Z@!#$%&\/\(\)=\-_?+\*\.,:;]{8,64}$/i", $value)) {
                $return_value = (isset($value) && (($value) !== "")) ? $value : $default;
                return $return_value;
            } else {
                // invalid password
                $this->stop();
                $this->addError($id);
                $this->addHelperText($id, $error_text);
                $this->addNotice("<b>$name</b> is not a valid password.");
            }
        }

        private function validate_number($value, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            // no safemode
            if ($value && isnum($value) || $value === 0) {
                $return_value = (isset($value) && (($value) !== "")) ? $value : $default;
                return $return_value;
            } else {
                // invalid password
                $this->stop();
                $this->addError($id);
                $this->addHelperText($id, $error_text);
                $this->addNotice("<b>$name</b> is not a valid number.");
            }
        }

        private function validate_url($value, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            if (isset($value) && $value !== "") {
                return cleanurl($value);
            } else {
                return $default;
            }
        }

        private function validate_file($value, $type, $path, $maxsize, $default, $name, $id, $required = FALSE, $safemode = FALSE, $error_text = FALSE) {
            global $settings;
            if ($required && $value['name']) {
                if (isset($value['name'])) {
                    require_once BASEDIR.'includes/mimetypes_include.php';
                    if ($type == 'image') {
                        $mimetypes = array('jpg' => 'image/jpg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'png' => 'image/png', 'tiff' => 'image/tiff', 'tif' => 'image/tif', 'bmp' => 'image/x-ms-bmp', 'ico' => 'image/x-icon'); // all
                    } elseif ($type == 'file') {
                        $mimetypes = mimeTypes(); // all
                    }
                    $acceptable = explode(',', $settings['attachtypes']); //jpg.
                    foreach ($acceptable as $types_of_files_mime) {
                        $files_ext = $mimetypes[ltrim($types_of_files_mime, '.')];
                        if ($files_ext) {
                            $acceptable_files[] = $files_ext;
                        }
                    }
                    $errors        = array();
                    $maxsize       = $settings['attachmax'];
                    $file_max_size = parsebytesize($maxsize);
                    if (($value['size'] >= $maxsize) || ($value['size'] == 0)) {
                        $errors[]   = 1;
                        $error_text = "File too large. File must be less than ".$file_max_size.".";
                        $this->stop();
                        $this->addError($id);
                        $this->addHelperText($id, $error_text);
                        $this->addNotice("<b>$name</b> is not a valid file type.");
                    }
                    if ((!in_array($value['type'], $acceptable_files)) && (!empty($value['type']))) {
                        $errors[]   = 1;
                        $error_text = "Invalid file type. Only ".implode(", ", $acceptable)." is allowed.";
                        $this->stop();
                        $this->addError($id);
                        $this->addHelperText($id, $error_text);
                        $this->addNotice("<b>$name</b> is not a valid file type.");
                    }
                    if (count($errors) === 0) {
                        $ext          = strrchr($value['name'], ".");
                        $secret_rand  = rand(1000000, 9999999);
                        $hash         = substr(md5($secret_rand), 8, 8);
                        $return_value = (isset($value['name']) && (($value['name']) !== "")) ? $location.$hash.$ext : $default;
                        if (!defined('FUSION_NULL')) {
                            if (is_uploaded_file($value['tmp_name'])) {
                                if (verify_image($value['tmp_name'])) {
                                    //if (!file_exists($location)) {
                                    //    mkdir($location, 0644, true);
                                    //}
                                    move_uploaded_file($value['tmp_name'], $location.$hash.$ext);
                                } else {
                                    $this->addNotice("<b>$name</b> is failed verification check.");
                                }
                            } else {
                                $this->addNotice("<b>$name</b> is not uploaded.");
                            }
                        }
                        return $return_value;
                    }
                    return $default;
                } else {
                    $this->stop();
                    $this->addError($id);
                    $this->addHelperText($id, $error_text);
                    $this->addNotice("<b>$name</b> is not a valid file.");
                }
            } else {
                return $default;
            }
        }
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
                    if (isset($data['path'])) {
                        $val = $defender->defender($data['type'], $value, $default, $data['name'], $data['id'], $data['required'], $data['safemode'], $data['error_text'], $data['path'], $data['maxsize']);
                    } else {
                        $val = $defender->defender($data['type'], $value, $default, $data['name'], $data['id'], $data['required'], $data['safemode'], $data['error_text']);
                    }
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
            $user_id    = (isset($userdata['user_id']) ? $userdata['user_id'] : 0);
            $token_time = time();
            $algo       = $settings['password_algorithm'];
            $key        = $user_id.$token_time.$form.SECRET_KEY;
            $salt       = md5(isset($userdata['user_salt']) ? $userdata['user_salt'].SECRET_KEY_SALT : SECRET_KEY_SALT);
            // generate a new token and store it
            $token                            = $user_id.".".$token_time.".".hash_hmac($algo, $key, $salt);
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
        $html    = '';
        $shuffle = str_shuffle("abcdefghijklmnopqrstuvwxyz1234567890");
        if (!defined("TOKEN-$shuffle")) {
            define("TOKEN-$shuffle", TRUE);
            $html .= "<input type='hidden' name='fusion_token' value='$token' />\n"; // form token
            $html .= "<input type='hidden' name='token_rings[$shuffle]' value='$form' readonly />\n";
        }
        return $html;
    }

?>
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: login/login.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

namespace PHPFusion\Infusions\Login;

use PHPFusion\Authenticate;
use PHPFusion\PasswordAuth;

/**
 * Class Login
 *
 * @package PHPFusion\Infusions\Login
 */
class Login {

    private static $login_connectors = [];
    private static $current_user_language = 'English';
    private static $drivers = [];
    private static $driver_status = [];
    private static $driver_config_status = [];
    private static $driver_settings = [];

    public function set_current_language() {
        $langData = dbarray(dbquery('SELECT * FROM '.DB_LANGUAGE_SESSIONS.' WHERE user_ip=:ip', [':ip' => USER_IP]));
        self::$current_user_language = ($langData['user_language'] ?: fusion_get_settings('locale'));
        if (!defined('LANGUAGE')) {
            define('LANGUAGE', self::$current_user_language);
        }
    }

    public function filename_to_title($filename) {
        $field_name = explode("_", $filename);
        $field_title = "";
        for ($i = 0; $i <= count($field_name) - 3; $i++) {
            $field_title .= ($field_title) ? "_" : "";
            $field_title .= $field_name[$i];
        }

        return (string)$field_title;
    }

    public function cache_driver($driver_name = NULL) {
        if (empty(self::$drivers)) {
            $result = dbquery("SELECT * FROM ".DB_LOGIN);
            if (dbrows($result)) {
                while ($data = dbarray($result)) {
                    self::$drivers[$data['login_name']] = $data;
                }
            }
        }
        if ($driver_name !== NULL) {
            return (isset(self::$drivers[$driver_name]) ? self::$drivers[$driver_name] : NULL);
        }

        return self::$drivers;

    }

    public function check_driver_config($title = '') {
        if (empty(self::$driver_config_status)) {
            $drivers = $this->cache_driver();
            if (!empty($drivers)) {
                foreach ($drivers as $driver_title => $data) {
                    self::$driver_config_status[$driver_title] = !empty($data['login_settings']) ? TRUE : FALSE;
                }
            }
        }
        if ($title) {
            return (isset(self::$driver_config_status[$title]) ? self::$driver_config_status[$title] : 0);
        }

        return self::$driver_config_status;
    }

    public function check_driver_status($title = '') {
        if (empty(self::$driver_status)) {
            $drivers = $this->cache_driver();
            if (!empty($drivers)) {
                foreach ($drivers as $driver_title => $data) {
                    self::$driver_status[$driver_title] = $data['login_status'];
                }
            }
        }
        if ($title) {
            return (isset(self::$driver_status[$title]) ? self::$driver_status[$title] : 0);
        }

        return self::$driver_status;
    }

    public function cache_files() {
        $list = [];
        $files = makefilelist(INFUSIONS.'login/user_fields/', 'index.php|.|..', TRUE);
        if (!empty($files)) {
            foreach ($files as $file) {
                if (preg_match("/_var.php/i", $file)) {
                    $list[] = $file;
                }
            }
        }

        return $list;
    }

    /**
     * Get the driver settings
     * Method is used for fetching all the driver settings.
     *
     * @param string $title
     *
     * @return int
     */
    protected function get_driver_settings($title = '') {
        if (empty(self::$driver_settings)) {
            $drivers = $this->cache_driver();
            if (!empty($drivers)) {
                foreach ($drivers as $driver_title => $data) {
                    self::$driver_settings[$driver_title] = $data['login_settings'];
                }
            }
        }
        if ($title) {
            return (isset(self::$driver_settings[$title]) ? self::$driver_settings[$title] : NULL);
        }

        return self::$driver_settings;
    }

    /**
     * Load plugin driver settings
     *
     * @param $title
     *
     * @return array
     */
    public function load_driver_settings($title) {
        $settings = [];
        $driver_settings = $this->get_driver_settings($title);
        if (!empty($driver_settings)) {
            $settings = json_decode(\defender::decrypt_string($driver_settings, SECRET_KEY_SALT), true);
        }

        return (array)$settings;
    }

    /**
     * Store plugin driver settings
     *
     * @param       $title
     * @param array $settings_array
     *
     * @return bool
     */
    protected function update_driver_settings($title, $settings_array = array()) {
        if (\defender::safe() && !empty($settings_array) && !empty($title)) {
            // I will need a pair to encrypt
            /* $encoded = json_encode($settings_array);
            print_p($encoded);
            $encrypted = \defender::encrypt_string($encoded, SECRET_KEY_SALT);
            print_p($encrypted);
            $decrypted = \defender::decrypt_string($encrypted, SECRET_KEY_SALT);
            print_p($decrypted);
            $readBack = json_decode($decrypted, true);
            print_p($readBack); */
            $driver = [
                'login_name'     => $title,
                'login_settings' => \defender::encrypt_string(json_encode($settings_array), SECRET_KEY_SALT)
            ];
            dbquery_insert(DB_LOGIN, $driver, 'update');
            addNotice('success', fusion_get_locale('login_127'));

            return TRUE;
        }

        return FALSE;
    }

    /**
     * This is read by the the authenticator class.
     *
     * @param array $userdata
     *
     * @return bool
     */
    public function authenticate(array $userdata = array()) {
        // set language constants
        $this->set_current_language();
        require_once INFUSIONS.'login/infusion_db.php';

        if (!empty($userdata)) { // user found

            // check for installed drivers
            $driver = $this->cache_driver();

            if (!empty($driver)) {
                foreach ($driver as $driver_title => $data) {
                    if ($data['login_type'] == '2FA') {
                        $locale_file_path = LOGIN_LOCALESET.$driver_title.'.php';
                        $driver_file_path = INFUSIONS.'login/user_fields/'.$driver_title.'_include_var.php';
                        if (file_exists($locale_file_path) && file_exists($driver_file_path)) {

                            include($locale_file_path);
                            include($driver_file_path);
                            if (!empty($user_field_auth) && !empty($user_field_dbname)) {
                                // Display the field with accessing the class or function names
                                $authenticate_method = NULL;
                                // This is the class
                                if (is_array($user_field_auth) && count($user_field_auth) > 1) {
                                    $login_class = $user_field_auth[0];
                                    $login_method = $user_field_auth[1];
                                    $login_authenticator = new $login_class();
                                    // Call the authentication method
                                    $login_authenticator->$login_method($userdata);
                                } elseif (is_callable($user_field_auth)) {
                                    // Call the authentication method
                                    $login_methods = $user_field_auth();
                                }
                            }
                            unset($user_field_auth);
                            unset($user_field_dbname);
                            unset($login_methods);
                        } else {
                            die($locale_file_path.' does not exist');
                            die($driver_file_path.' does not exsit');
                        }
                    }
                }
            }

            return FALSE;
        }

        return FALSE;
    }

    // Outputs a string where MVT logins can display the user fields buttons.
    public function get_login_connectors() {

        $this->set_current_language();
        require_once INFUSIONS.'login/infusion_db.php';

        $driver = $this->cache_driver();

        if (!empty($driver)) {
            foreach ($driver as $driver_title => $data) {

                if ($data['login_type'] == 'LGA') {
                    $locale_file_path = LOGIN_LOCALESET.$driver_title.'.php';
                    $driver_file_path = INFUSIONS.'login/user_fields/'.$driver_title.'_include_var.php';

                    if (file_exists($locale_file_path) && file_exists($driver_file_path)) {

                        include($locale_file_path);
                        include($driver_file_path);

                        if (!empty($user_field_login) && !empty($user_field_dbname)) {
                            // Display the field with accessing the class or function names
                            $login_connectors = NULL;

                            // This is the class
                            if (is_array($user_field_login) && count($user_field_login) > 1) {
                                $login_class = $user_field_login[0];
                                $login_method = $user_field_login[1];
                                $login_connectors = new $login_class();
                                // Call the authentication method
                                self::$login_connectors[] = $login_connectors->$login_method();
                            } elseif (is_callable($user_field_login)) {
                                // Call the authentication method
                                self::$login_connectors[] = $login_methods = $user_field_login();
                            }
                        }
                        unset($user_field_login);
                        unset($user_field_dbname);
                        unset($login_connectors);
                    }
                }
            }
            unset($user_field_login);
            unset($user_field_dbname);
            unset($login_methods);
        }

        return self::$login_connectors;
    }

    public function register_new_user() {
    }

    /**
     * Generate a new set of password, hash, salt and algo for new user registration
     *
     * @return array
     */
    public static function get_new_user_password() {
        $loginPass = new PasswordAuth();
        $newLoginPass = $loginPass->getNewPassword(12);
        $loginPass->inputNewPassword = $newLoginPass;
        $loginPass->inputNewPassword2 = $newLoginPass;

        return array(
            'password_test' => ($loginPass->isValidNewPassword() === 0 ? TRUE : FALSE),
            'password'      => $newLoginPass,
            'algo'          => $loginPass->getNewAlgo(),
            'salt'          => $loginPass->getNewSalt(),
            'hash'          => $loginPass->getNewHash(),
        );
    }

    /**
     * Authenticate password
     *
     * @param $input_password
     * @param $hash
     * @param $algo
     * @param $salt
     *
     * @return bool
     */
    public static function verify_user_password($input_password, $hash, $algo, $salt) {
        $inputHash = ($algo != 'md5' ? hash_hmac($algo, $input_password, $salt) : md5(md5($input_password)));
        if ($inputHash === $hash) {
            return TRUE;
        }

        return FALSE;
    }

    protected static function send_email_verification($userData) {
        $settings = fusion_get_settings();
        $locale = fusion_get_locale('', LOCALE.LOCALESET.'user_fields.php');
        require_once(INCLUDES."sendmail_include.php");

        $user_name = $userData['user_name'];
        $user_email = $userData['user_email'];
        $userCode = hash_hmac("sha1", PasswordAuth::getNewPassword(), $user_email);
        $userPassword = $userData['input_password'];
        $activationUrl = $settings['siteurl']."register.php?email=".$user_email."&code=".$userCode;

        $message = str_replace("USER_NAME", $user_name, $locale['u152']);
        $message = str_replace("SITENAME", fusion_get_settings("sitename"), $message);
        $message = str_replace("SITEUSERNAME", fusion_get_settings("siteusername"), $message);
        $message = str_replace("USER_PASSWORD", $userPassword, $message);
        $message = str_replace("ACTIVATION_LINK", $activationUrl, $message);
        $subject = str_replace("[SITENAME]", fusion_get_settings("sitename"), $locale['u151']);

        if (!sendemail($user_name, $user_email, $settings['siteusername'], $settings['siteemail'], $subject, $message)) {
            $message = strtr($locale['u154'], [
                '[LINK]'  => "<a href='".BASEDIR."contact.php'><strong>",
                '[/LINK]' => "</strong></a>"
            ]);
            addNotice('warning', $locale['u153']."<br />".$message, 'all');
        }
        $userInfo = base64_encode(serialize($userData));
        dbquery("INSERT INTO ".DB_NEW_USERS."
					(user_code, user_name, user_email, user_datestamp, user_info)
					VALUES
					('".$userCode."', '".$user_name."', '".$user_email."', '".TIME."', '".$userInfo."')
					");
        addNotice("success", $locale['u150'], 'all');
    }

    /**
     * Authenticate a user login session
     *
     * @param $user_id
     */
    protected static function authenticate_user_login($user_id) {

        $settings = fusion_get_settings();
        $locale = fusion_get_locale();
        $user = fusion_get_user($user_id);
        $remember = false;
        // Initialize password auth
        if (!empty($user['user_id']) && $user['user_status'] == 0) {
            // Implement new login class
            $login = new Login();
            $authenticate_methods = $login->authenticate($user);
            if (empty($authenticate_methods)) {
                Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], $remember, TRUE);
                Authenticate::_setUserTheme($user);
            }
        } else {
            require_once INCLUDES."suspend_include.php";
            require_once INCLUDES."sendmail_include.php";
            if (($user['user_status'] == 3 && $user['user_actiontime'] < time()) || $user['user_status'] == 7) {
                dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user['user_id']."'");
                if ($user['user_status'] == 3) {
                    $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['global_451']);
                    $message = str_replace("[SITEURL]", $settings['siteurl'], $locale['global_455']);
                    $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                    unsuspend_log($user['user_id'], 3, $locale['global_450'], TRUE);
                } else {
                    $subject = $locale['global_454'];
                    $message = str_replace("[SITEURL]", $settings['siteurl'], $locale['global_452']);
                    $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                }
                $message = str_replace("USER_NAME", $user['user_name'], $message);
                sendemail($user['user_name'], $user['user_email'], $settings['siteusername'], $settings['siteemail'], $subject, $message);
            }
        }
    }

}

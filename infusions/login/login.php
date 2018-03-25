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
/**
 * Class Login
 *
 * @package PHPFusion\Infusions\Login
 */
class Login {

    private $login_methods = [];
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
     *
     * @param string $title
     *
     * @return int
     */
    public function get_driver_settings($title = '') {
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
    public function get_login_methods() {

        $this->set_current_language();
        require_once INFUSIONS.'login/infusion_db.php';

        $driver = $this->cache_driver();

        if (!empty($driver)) {
            foreach ($driver as $driver_title => $data) {
                if ($data['login_type'] == 'LOGIN') {
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
                                $login_authenticator->$login_method();
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
            unset($user_field_login);
            unset($user_field_dbname);
            unset($login_methods);

        }

        $this->set_current_language();
        // parse all login methods here into an array, an array of html buttons and fields.
        // each must have its form module.
        $files = $this->cache_files();
        foreach ($files as $file) {
            include INFUSIONS.'login/user_fields/'.$file;
            if (!empty($user_field_login) && !empty($user_field_dbname)) {
                if ($this->login_db_settings[$user_field_dbname]) { // enable by administrator.
                    // now display the field with accessing the class or function names
                    $login_methods = NULL;
                    if (is_array($user_field_login) && count($user_field_login) > 1) {
                        $login_class = $user_field_login[0];
                        $login_method = $user_field_login[1];
                        $login_param = isset($user_field_login[2]) ? $user_field_login[2] : '';
                        $login_authenticator = new $login_class();
                        $login_methods = $login_authenticator->$login_method($login_param);
                    } elseif (is_callable($user_field_login)) {
                        $login_methods = $user_field_login();
                    }
                    // this is a first step.
                    $this->login_methods[] = $login_methods;
                }
            }
            unset($user_field_login);
            unset($user_field_dbname);
            unset($login_methods);
        }

        return $this->login_methods;
    }

    public function register_new_user() {

    }

}

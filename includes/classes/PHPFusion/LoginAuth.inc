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

namespace PHPFusion;

/**
 * Class Login
 *
 * @package PHPFusion\Infusions\Login
 */
class LoginAuth {

    private static $login_connectors = [];
    private static $security_connectors = [];
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

    public function check_driver_status($plugin_name = '') {
        if (empty(self::$driver_status)) {
            $drivers = $this->cache_driver();
            if (!empty($drivers)) {
                foreach ($drivers as $driver_title => $data) {
                    self::$driver_status[$driver_title] = $data['login_status'];
                }
            }
        }
        if ($plugin_name) {
            return (isset(self::$driver_status[$plugin_name]) ? self::$driver_status[$plugin_name] : 0);
        }

        return self::$driver_status;
    }

    public function cache_files() {
        $list = [];
        $folders = makefilelist(INCLUDES.'login/', 'index.php|.|..', TRUE, 'folders');
        if (!empty($folders)) {
            foreach ($folders as $folder) {
                $files = makefilelist(INCLUDES.'login/'.$folder.'/', 'index.php|.|..', TRUE);
                foreach ($files as $file) {
                    if ($folder.'.login.inc' == $file) {
                        $list[] = $folder;
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get plugin file readings
     *
     * @param $plugin_name
     *
     * @return array
     */
    public function get_login_file($plugin_name) {

        $login_plugin_name = "";
        $login_plugin_desc = "";
        $login_plugin_folder = "";
        $login_plugin_handler = "";
        $login_plugin_login = [];
        $login_plugin_connect = [];
        $login_plugin_settings = [];
        $login_plugin_type = "";
        $login_plugin_icon = "";
        $login_plugin_version = "";
        $user_field_dbname = "";
        $user_field_dbinfo = "";
        $user_field_default = "";
        $user_field_options = "";
        $user_field_error = "";

        require_once INCLUDES."/login/".$plugin_name."/".$plugin_name.".login.inc";

        return array(
            // Login Plugin
            'name'            => $login_plugin_name,
            'description'     => $login_plugin_desc,
            'folder'          => $login_plugin_folder,
            'handler'         => $login_plugin_handler,
            'version'         => $login_plugin_version,
            'login_method'    => $login_plugin_login,
            'settings_method' => $login_plugin_settings,
            'connect_method'  => $login_plugin_connect,
            'type'            => $login_plugin_type,
            'icon'            => $login_plugin_icon,
            // Users table columns
            'dbname'          => $user_field_dbname,
            'dbinfo'          => $user_field_dbinfo,
            'default_value'   => $user_field_default,
            'field_options'   => $user_field_options,
            'field_error'     => $user_field_error,
        );

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
            $settings = json_decode(\Defender::decrypt_string($driver_settings, SECRET_KEY_SALT), TRUE);
        }

        return (array)$settings;
    }

    protected function update_driver_settings($title, $settings_array = []) {
        if (\Defender::safe() && !empty($settings_array) && !empty($title)) {
            // I will need a pair to encrypt
            /* $encoded = json_encode($settings_array);
            print_p($encoded);
            $encrypted = \Defender::encrypt_string($encoded, SECRET_KEY_SALT);
            print_p($encrypted);
            $decrypted = \Defender::decrypt_string($encrypted, SECRET_KEY_SALT);
            print_p($decrypted);
            $readBack = json_decode($decrypted, true);
            print_p($readBack); */
            $driver = [
                'login_name'     => $title,
                'login_settings' => \Defender::encrypt_string(json_encode($settings_array), SECRET_KEY_SALT)
            ];
            dbquery_insert(DB_LOGIN, $driver, 'update');
            addNotice('success', fusion_get_locale('login_127'));

            return TRUE;
        }

        return FALSE;
    }

    // Outputs a string where MVT logins can display the user fields buttons.
    public function get_login_connectors() {
        $this->set_current_language();

        return self::$login_connectors;
    }

    // Outputs an array where MVT security connectors can display the user fields connections
    public function get_security_connectors() {
        $this->set_current_language();
        $enabled_plugins = $this->cache_driver();
        if (!empty($enabled_plugins)) {
            foreach ($enabled_plugins as $plugin_id => $plugin_data) {
                $login_connectors = NULL;
                if ($plugin_data['login_type'] == '2FA') {
                    $plugin = $this->get_login_file($plugin_id);
                    $method = (iMEMBER ? "connect_method" : "login_method");
                    if (!empty($plugin[$method])) {
                        // Display the field with accessing the class or function names
                        if (is_array($plugin[$method]) && count($plugin[$method]) > 1) {
                            $login_class = $plugin[$method][0];
                            $login_method = $plugin[$method][1];
                            $login_connectors = new $login_class();
                            $logins = $login_connectors->$login_method();
                            // Call the authentication method
                        } else if (is_callable($plugin[$method])) {
                            // Call the authentication method
                            $logins = $plugin[$method]();
                        }
                        if (!empty($logins)) {
                            self::$security_connectors[$plugin_id] = [
                                'title'     => $plugin['name'],
                                'icon'      => $plugin['icon'],
                                'connector' => $logins,
                            ];
                        }
                    }
                }
            }
        }

        return self::$security_connectors;
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

        return [
            'password_test' => ($loginPass->isValidNewPassword() === 0 ? TRUE : FALSE),
            'password'      => $newLoginPass,
            'algo'          => $loginPass->getNewAlgo(),
            'salt'          => $loginPass->getNewSalt(),
            'hash'          => $loginPass->getNewHash(),
        ];
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

}

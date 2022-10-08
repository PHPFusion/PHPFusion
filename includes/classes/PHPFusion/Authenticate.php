<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: Authenticate.php
| Author: Core Development Team
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

$settings = fusion_get_settings();
if (!empty($settings['domain_server'])) {
    // convert to arr
    $domain_server = explode("|", $settings['domain_server']);
    $domain_server[] = (strstr($settings['site_host'], "www.") ? substr($settings['site_host'], 3) : $settings['site_host']);
    $domain_server = array_unique(array_filter($domain_server));
    foreach ($domain_server as $server_name) {
        if ($_SERVER['SERVER_NAME'] === $server_name) {
            define("COOKIE_DOMAIN", $server_name);
        }
    }
} else {
    $fusion_domain = (strstr($settings['site_host'], "www.") ? substr($settings['site_host'], 3) : $settings['site_host']);
    define("COOKIE_DOMAIN", $settings['site_host'] != 'localhost' ? $fusion_domain : FALSE);
}
define("COOKIE_PATH", $settings['site_path']);
define("COOKIE_USER", COOKIE_PREFIX."user");
define("COOKIE_ADMIN", COOKIE_PREFIX."admin");
define("COOKIE_VISITED", COOKIE_PREFIX."visited");
define("COOKIE_LASTVISIT", COOKIE_PREFIX."lastvisit");

class Authenticate {

    private static $authenticate_url = "";
    private $user_data = [
        "user_level"  => 0,
        "user_rights" => "",
        "user_groups" => "",
        "user_theme"  => "Default"
    ];
    private $two_factor_redirect = FALSE;

    /**
     * Authenticate constructor.
     *
     * @param string $inputUserName
     * @param string $inputPassword
     * @param bool   $remember
     * @param string $authentication_url
     */
    public function __construct($inputUserName, $inputPassword, $remember, $authentication_url = NULL) {

        if ($authentication_url) {
            self::$authenticate_url = $authentication_url;
        }

        $this->_authenticate($inputUserName, $inputPassword, $remember);
    }

    /**
     * @param string $inputUserName
     * @param string $inputPassword
     * @param bool   $remember
     *
     * @throws \PHPMailer\PHPMailer\Exception
     */
    private function _authenticate($inputUserName, $inputPassword, $remember) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $inputUserName = preg_replace(["/\=/", "/\#/", "/\sOR\s/"], "", stripinput($inputUserName));
        $where = "user_name";
        switch ($settings['login_method']) {
            case 1:
                $where = "user_email";
                break;
            case 2:
                $where = (preg_match("/^[-0-9A-Z_\.]{1,50}@([-0-9A-Z_\.]+\.){1,50}([0-9A-Z]){2,4}$/i", $inputUserName) ? "user_email" : "user_name");
                break;
        }
        $result = dbquery("SELECT * FROM ".DB_USERS." WHERE ".$where."='".$inputUserName."' LIMIT 1");
        if (dbrows($result) == 1) {
            $user = dbarray($result);
            // Initialize password auth
            $passAuth = new PasswordAuth();
            $passAuth->currentAlgo = $user["user_algo"];
            $passAuth->currentSalt = $user["user_salt"];
            $passAuth->currentPasswordHash = $user["user_password"];
            $passAuth->inputPassword = $inputPassword;
            // Check if input password is valid
            if ($passAuth->isValidCurrentPassword(TRUE)) {
                if (!$settings['multiple_logins']) {
                    $user['user_algo'] = $passAuth->getNewAlgo();
                    $user['user_salt'] = $passAuth->getNewSalt();
                    $user['user_password'] = $passAuth->getNewHash();
                    dbquery("UPDATE ".DB_USERS." SET user_algo='".$user['user_algo']."', user_salt='".$user['user_salt']."', user_password='".$user['user_password']."' WHERE user_id='".$user['user_id']."'");
                }

                if ($user['user_status'] == 0 && $user['user_actiontime'] == 0) {

                    Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], $remember);

                    if ($settings['auth_login_enabled'] == 1 && $user['user_auth'] == 1) {
                        $this->two_factor_redirect = TRUE;
                    } else {
                        Authenticate::storeUserSession($passAuth, $user["user_id"]);
                        $this->user_data = $user;
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
                    } else {
                        redirect(Authenticate::getRedirectUrl(4, $user['user_status'], $user['user_id']));
                    }
                }
            } else {
                redirect(Authenticate::getRedirectUrl(1));
            }
        } else {
            redirect(Authenticate::getRedirectUrl(1));
        }
    }

    /**
     * @param int    $userID
     * @param string $salt
     * @param string $algo
     * @param bool   $remember
     * @param bool   $userCookie
     */
    public static function setUserCookie($userID, $salt, $algo, $remember = FALSE, $userCookie = TRUE) {
        global $_COOKIE;
        $cookiePath = COOKIE_PATH;
        $cookieName = COOKIE_USER;
        if ($remember) {
            $cookieExpiration = time() + 1209600; // 14 days
        } else {
            $cookieExpiration = time() + 172800; // 48 hours
        }
        if (!$userCookie) {
            $cookiePath = COOKIE_PATH; // also allow infusions admin.
            $cookieName = COOKIE_ADMIN;
            $cookieExpiration = time() + 172800; // 48 hours
        }
        $key = hash_hmac($algo, $userID.$cookieExpiration, $salt);
        $hash = hash_hmac($algo, $userID.$cookieExpiration, $key);
        $cookieContent = $userID.".".$cookieExpiration.".".$hash;

        fusion_set_cookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, COOKIE_DOMAIN, FALSE, TRUE, 'lax');
        // Unable to set cookies properly
        if (!isset($_COOKIE[COOKIE_VISITED])) {
            redirect(Authenticate::getRedirectUrl(3));
        }
    }

    /**
     * Get the redirection url
     * If there is a new authentication url, error request will not valid
     *
     * @param int    $errorId
     * @param string $userStatus
     * @param string $userId
     *
     * @return string
     * @todo: use addNotice('') instead of going for errorId
     *
     */
    public static function getRedirectUrl($errorId, $userStatus = "", $userId = "") {
        global $_SERVER;

        if (self::$authenticate_url) {
            return self::$authenticate_url;
        }

        $return = BASEDIR."login.php?error=".$errorId;

        if ($userStatus) {
            $return .= "&status=".$userStatus;
        }
        if ($userId) {
            $return .= "&id=".$userId;
        }
        $return .= "&redirect=".urlencode($_SERVER['PHP_SELF']);
        if (FUSION_QUERY) {
            $return .= urlencode("?".preg_replace("/&amp;/i", "&", FUSION_QUERY));
        }

        return $return;
    }

    /**
     * @param PasswordAuth $passAuth
     * @param int          $user_id
     */
    private static function storeUserSession(PasswordAuth $passAuth, $user_id) {
        if ($passAuth->isValidCurrentPassword(TRUE)) {
            $session = $passAuth->getNewSalt().".".$passAuth->getNewHash();
            dbquery("UPDATE ".DB_USERS." SET user_session='$session' WHERE user_id=$user_id");
        }
    }

    /**
     * Set admin login
     */
    public static function setAdminLogin() {
        $locale = fusion_get_locale();

        if (check_get("logout")) {
            if (defined('COOKIE_ADMIN') && isset($_COOKIE[COOKIE_ADMIN]) && $_COOKIE[COOKIE_ADMIN] != "") {
                $cookieDataArr = explode(".", $_COOKIE[COOKIE_ADMIN]);
                if (count($cookieDataArr) == 3) {
                    self::expireAdminCookie();
                }
            }

            redirect(BASEDIR."index.php");
        }

        if (check_post("admin_password")) {
            $admin_password = sanitizer('admin_password', '', 'admin_password');
            if (Authenticate::validateAuthAdmin($admin_password)) {
                if (Authenticate::setAdminCookie($admin_password)) {
                    unset($_SESSION['notices']);
                    redirect(FUSION_REQUEST);
                } else {
                    addnotice("danger", $locale['cookie_error'], $locale['cookie_error_description']);
                }
            } else {
                addnotice("danger", $locale['password_invalid'], $locale['password_invalid_description']);
            }
        }
        if (defined('ADMIN_PANEL') && !isset($_COOKIE[COOKIE_PREFIX."admin"])) {
            setnotice("danger", $locale['cookie_title'], $locale['cookie_description']);
        }
    }

    /**
     * Expire admin cookie
     */
    public static function expireAdminCookie() {
        fusion_set_cookie(COOKIE_ADMIN, '', time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE, 'lax');
    }

    /**
     * @param string $pass
     *
     * @return bool
     */
    public static function validateAuthAdmin($pass = "") {
        $userdata = fusion_get_userdata();
        $locale = fusion_get_locale();
        if (iADMIN) {
            // Validate existing admin cookie
            if ($pass == '' && isset($_COOKIE[COOKIE_ADMIN]) && $_COOKIE[COOKIE_ADMIN] != "") {
                $cookieDataArr = explode(".", $_COOKIE[COOKIE_ADMIN]);
                if (count($cookieDataArr) == 3) {
                    list($userID, $cookieExpiration, $cookieHash) = $cookieDataArr;
                    if ($cookieExpiration > time() && $userID == $userdata['user_id']) {
                        $result = dbquery("SELECT user_admin_algo, user_admin_salt FROM ".DB_USERS."
                            WHERE user_id='".(isnum($userID) ? $userID : 0)."' AND user_level < ".USER_LEVEL_MEMBER." AND  user_status='0' AND user_actiontime='0'
                            LIMIT 1");
                        if (dbrows($result) == 1) {
                            $user = dbarray($result);
                            $key = hash_hmac($user['user_admin_algo'], $userID.$cookieExpiration, $user['user_admin_salt']);
                            $hash = hash_hmac($user['user_admin_algo'], $userID.$cookieExpiration, $key);
                            if ($cookieHash == $hash) {
                                $error = FALSE;
                                $aid = session_get("aid");
                                if (!$aid) {
                                    return FALSE;
                                }

                                $password_algo = fusion_get_settings("password_algorithm");
                                $token_data = explode(".", $_SESSION['aid']);
                                // check if the token has the correct format
                                if (count($token_data) == 3) {
                                    list($tuser_id, $token_time, $hash) = $token_data;
                                    $user_id = (iMEMBER ? $userdata['user_id'] : 0);
                                    $algo = $password_algo;
                                    $key = $userdata['user_id'].$token_time.iAUTH.SECRET_KEY;
                                    $salt = md5($userdata['user_admin_salt'].SECRET_KEY_SALT);
                                    // check if the logged user has the same ID as the one in token
                                    if ($tuser_id != $user_id) {
                                        $error = $locale['token_error_4'];
                                        // make sure the token datestamp is a number
                                    } else if (!isnum($token_time)) {
                                        $error = $locale['token_error_5'];
                                        // check if the hash is valid
                                    } else if ($hash != hash_hmac($algo, $key, $salt)) {
                                        $error = $locale['token_error_7'];
                                        // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                                    }
                                } else {
                                    // token format is incorrect
                                    $error = $locale['token_error_8'];
                                }
                                // Check if any error was set
                                if ($error !== FALSE) {
                                    fusion_stop();
                                    addnotice("warning", $error);

                                    return FALSE;
                                }

                                return TRUE;
                            }
                        }
                    }
                }
                // Validate a provided password
            } else if ($pass != "") {
                $result = dbquery("SELECT user_admin_algo, user_admin_salt, user_admin_password FROM ".DB_USERS." WHERE user_id='".$userdata['user_id']."' AND user_level < ".USER_LEVEL_MEMBER." AND  user_status='0' AND user_actiontime='0' LIMIT 1");
                if (dbrows($result) == 1) {
                    $user = dbarray($result);
                    $inputHash = ($user['user_admin_algo'] != 'md5' ? hash_hmac($user['user_admin_algo'], $pass, $user['user_admin_salt']) : md5(md5($pass)));
                    if ($inputHash == $user['user_admin_password']) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    /**
     * @param string $inputPassword
     *
     * @return bool
     */
    public static function setAdminCookie($inputPassword) {
        $userdata = fusion_get_userdata();
        if (iADMIN) {
            // Initialize password auth
            $passAuth = new PasswordAuth();
            $passAuth->currentAlgo = $userdata['user_admin_algo'];
            $passAuth->currentSalt = $userdata['user_admin_salt'];
            $passAuth->currentPasswordHash = $userdata['user_admin_password'];
            $passAuth->inputPassword = $inputPassword;
            // Check if input password is valid
            if ($passAuth->isValidCurrentPassword(TRUE)) {
                if (fusion_get_settings('multiple_logins') != 1) {
                    $userdata['user_admin_algo'] = $passAuth->getNewAlgo();
                    $userdata['user_admin_salt'] = $passAuth->getNewSalt();
                    $userdata['user_admin_password'] = $passAuth->getNewHash();
                    dbquery("UPDATE ".DB_USERS." SET user_admin_algo='".$userdata['user_admin_algo']."', user_admin_salt='".$userdata['user_admin_salt']."', user_admin_password='".$userdata['user_admin_password']."' WHERE user_id='".$userdata['user_id']."'");
                }
                Authenticate::setUserCookie($userdata['user_id'], $userdata['user_admin_salt'], $userdata['user_admin_algo'], FALSE, FALSE);

                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @return array|string|null
     */
    public static function validateAuthUser() {
        $settings = fusion_get_settings();
        $locale_file = LOCALE.$settings['locale'].'/global.php'; // fix for multilang issue

        if (get("logoff", FILTER_VALIDATE_INT)) {
            session_remove("login_as");
            addnotice("success", fusion_get_locale('global_185', $locale_file), BASEDIR.$settings["opening_page"]);
            redirect(BASEDIR.$settings["opening_page"]);
        }

        if (isset($_COOKIE[COOKIE_USER]) && $_COOKIE[COOKIE_USER] != "") {
            $cookieDataArr = explode(".", $_COOKIE[COOKIE_USER]);
            if (count($cookieDataArr) == 3) {
                list($userID, $cookieExpiration, $cookieHash) = $cookieDataArr;
                if ($cookieExpiration > time()) {
                    // must update user_salt
                    $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".(isnum($userID) ? $userID : 0)."' AND user_status='0' AND user_actiontime='0' LIMIT 1");
                    if (dbrows($result) == 1) {
                        $user = dbarray($result);
                        $secure_auth = get("auth");

                        if (empty($user["user_session"])) {
                            if (FUSION_SELF == "login.php" && $secure_auth == 'security_pin') {
                                if ($pin = post("pin")) {
                                    $login_count = self::getValidationCount();

                                    if ($user["user_auth_actiontime"] > time()) {
                                        // if get it correct
                                        if ($pin == $user["user_auth_pin"]) {
                                            // then validate the idiot.
                                            $key = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $user['user_salt']);
                                            $hash = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $key);
                                            if ($cookieHash == $hash) {
                                                // set the user session.
                                                dbquery("UPDATE ".DB_USERS." SET user_session=:session WHERE user_id=:uid", [':uid' => $userID, ':session' => $user['user_salt'].".".$user['user_password']]);
                                                // we need to do a new redirection for log in.
                                                addnotice('success', 'OTP is successfully verified. You are now logged in.', fusion_get_settings('opening_page'));
                                                redirect(BASEDIR.fusion_get_settings('opening_page'));

                                            } else {
                                                // Cookie has been tampered with!
                                                return self::logOut();
                                            }
                                        }

                                        if ($login_count) {
                                            addnotice('danger', "Invalid Authentication Code. You have ".$login_count." attempts left.");
                                        } else {
                                            // logout and clear cookie.
                                            self::logOut();
                                            redirect(BASEDIR."login.php?error=6");
                                        }
                                    } else {
                                        self::logOut();
                                        redirect(BASEDIR."login.php?error=5");
                                    }

                                } else if (!$user["user_auth_pin"] && !check_get("auth_email")) {
                                    // return sendmail signal
                                    redirect(BASEDIR."login.php?auth=security_pin&auth_email=pin");
                                }
                            } else if (FUSION_SELF == 'login.php' && $secure_auth == 'restart') {
                                self::logOut();
                                redirect(BASEDIR."login.php");
                            } else {
                                self::logOut();
                                redirect(BASEDIR."login.php?error=7");
                            }
                            // If session exist.
                        } else if (FUSION_SELF == 'login.php' && $secure_auth == 'restart') {
                            self::logOut();
                            redirect(BASEDIR."login.php");
                        }

                        // From Version 7 to Version 9, Ported Database has this problem - where user_salt has problem entering
                        $key = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $user['user_salt']);
                        $hash = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $key);
                        if ($cookieHash === $hash) {

                            if ($login_id = session_get("login_as")) {
                                if (isnum($login_id)) {
                                    $login_user = fusion_get_user(session_get("login_as"));
                                    if (!empty($login_user["user_id"]) && $login_user["user_status"] == 0 && $login_user["user_actiontime"] == 0) {
                                        addnotice("success", sprintf(fusion_get_locale('global_184', $locale_file), $login_user["user_name"]));
                                        $user = $login_user;
                                    }
                                }
                            }

                            return $user;
                        } else {
                            // Cookie has been tampered with!
                            return Authenticate::logOut();
                        }
                    } else {
                        // User id does not exist or user_status / user_actiontime != 0
                        return Authenticate::logOut();
                    }
                } else {
                    // Cookie expired
                    Authenticate::logOut();
                    redirect(Authenticate::getRedirectUrl(2));

                    return NULL;
                }
            } else {
                // Missing arguments in cookie
                Authenticate::logOut();
                redirect(Authenticate::getRedirectUrl(2));

                return NULL;
            }
        } else {
            return Authenticate::getEmptyUserData();
        }
    }

    private static function getValidationCount() {
        $settings = fusion_get_settings();
        if (!isset($_SESSION['2fa_attempts'])) {
            $login_count = $settings['auth_login_attempts'] - 1;
            if ($login_count > 1) {
                $login_count = 1;
            }
            $_SESSION['2fa_attempts'] = $login_count;

            return $login_count;
        } else if ($_SESSION['2fa_attempts'] > 0) {
            $_SESSION['2fa_attempts'] = $_SESSION['2fa_attempts'] - 1;
            return $_SESSION['2fa_attempts'];
        }

        return 0;
    }

    /**
     * Log out
     *
     * @return array
     */
    public static function logOut() {

        if (defined('COOKIE_USER') && isset($_COOKIE[COOKIE_USER]) && $_COOKIE[COOKIE_USER] != "") {
            $cookieDataArr = explode(".", $_COOKIE[COOKIE_USER]);
            if (count($cookieDataArr) == 3) {
                list($userID, $cookieExpiration, $cookieHash) = $cookieDataArr;
                //unset($_SESSION['2fa_attempts']);
                //unset($_SESSION['auth_email_send']);
                dbquery("UPDATE ".DB_USERS." SET user_auth_pin='', user_auth_actiontime='' WHERE user_id=:uid", [':uid' => $userID]);
                $session_token = fusion_get_user($userID, "user_session");
                // if cookie has expired, we need to reset immediately
                if (!empty($session_token)) {
                    $session_token = explode(".", $session_token);
                    if (count($session_token) === 2) {
                        //$sql = "UPDATE ".DB_USERS." SET user_salt='".$session_token[0]."', user_password='".$session_token[1]."', user_session='' WHERE user_id=$userID";
                        dbquery("UPDATE ".DB_USERS." SET user_session='' WHERE user_id=$userID");
                    }
                }
            }
        }

        self::expireAdminCookie();

        dbquery("DELETE FROM ".DB_ONLINE." WHERE online_ip='".USER_IP."'");
        // Expires cookie
        fusion_set_cookie(COOKIE_LASTVISIT, "", time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE, 'lax');
        fusion_set_cookie(COOKIE_USER, "", time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE, 'lax');

        if (session_id()) {
            session_destroy();
        }

        return Authenticate::getEmptyUserData();
    }

    /**
     * @return array
     */
    public static function getEmptyUserData() {
        return [
            "user_id"     => USER_IP,
            "user_name"   => fusion_get_locale("user_guest"),
            "user_status" => 1,
            "user_level"  => 0,
            "user_rights" => "",
            "user_groups" => "",
            "user_theme"  => fusion_get_settings("theme")
        ];
    }

    public static function validateUserPasscode() {

        if (iMEMBER && (get("auth_email") == "pin" || (check_post("resend_otp")))) {
            $user = fusion_get_userdata();
            $settings = fusion_get_settings();
            $locale = fusion_get_locale('', [LOCALE.LOCALESET.'admin/members_email.php']);
            require_once INCLUDES.'sendmail_include.php';

            if (check_post('resend_otp') && $user['user_auth_actiontime'] >= time()
                && isset($_SESSION['new_otp_time']) && $_SESSION['new_otp_time'] <= time()) {

                $_SESSION['new_otp_time'] = time() + 30;

                fusion_sendmail('L_2FA', $user['user_name'], $user['user_email'], $locale['email_2fa_subject'], $locale['email_2fa_message'], [
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]'      => $user['user_auth_pin']
                    ]
                ]);

                addnotice("success", "We have resent the One Time Passcode to your registered email address");

            } else if ($user["user_auth_actiontime"] <= time()) {

                $_SESSION['new_otp_time'] = time() + 30;

                $random_pin = Authenticate::generateOTP($settings['auth_login_length']);

                $auth_actiontime = time() + $settings['auth_login_expiry'];

                dbquery("UPDATE ".DB_USERS." SET user_auth_pin=:pin, user_auth_actiontime=:time WHERE user_id=:uid", [":pin" => $random_pin, ":time" => $auth_actiontime, ':uid' => $user['user_id']]);

                fusion_sendmail('L_2FA', $user['user_name'], $user['user_email'], $locale['email_2fa_subject'], $locale['email_2fa_message'], [
                    'replace' => [
                        '[SITENAME]' => $settings['sitename'],
                        '[OTP]'      => $random_pin
                    ]
                ]);
                addnotice("success", "We have sent a One Time Passcode to your registered email address for the authentication");

            } else {
                // this one is to extend the validity of the shit.
                addnotice("danger", "You cannot request for another OTP until the time has expired");
            }

            redirect(BASEDIR.'login.php?auth=security_pin');
        }

    }

    /**
     * @throws \Exception
     */
    public static function generateOTP($keyLength) {
        // Set a blank variable to store the key in
        $key = "";
        for ($x = 1; $x <= $keyLength; $x++) {
            // Set each digit
            $key .= random_int(0, 9);
        }
        return $key;
    }

    /**
     * @return array|int|mixed|string|null
     */
    public static function setLastVisitCookie() {
        $guest_lastvisit = time() - 3600;
        $set_cookie = TRUE;
        $cookie_exists = isset($_COOKIE[COOKIE_LASTVISIT]) && isnum($_COOKIE[COOKIE_LASTVISIT]);
        if (iMEMBER) {
            $last_visited = fusion_get_userdata("user_lastvisit");
            $id = fusion_get_userdata("user_id");
            $update_threads = TRUE;
            $lastvisit = $last_visited;
            if ($cookie_exists) {
                if ($_COOKIE[COOKIE_LASTVISIT] > $last_visited) {
                    $update_threads = TRUE;
                    $lastvisit = $last_visited;
                } else {
                    $update_threads = FALSE;
                    $set_cookie = FALSE;
                    $lastvisit = $_COOKIE[COOKIE_LASTVISIT];
                }
            }
            if ($update_threads) {
                dbquery("UPDATE ".DB_USERS." SET user_threads='' WHERE user_id=$id");
            }
        } else {
            if ($cookie_exists) {
                if ($_COOKIE[COOKIE_LASTVISIT] > $guest_lastvisit) {
                    $lastvisit = $guest_lastvisit;
                } else {
                    $set_cookie = FALSE;
                    $lastvisit = $_COOKIE[COOKIE_LASTVISIT];
                }
            } else {
                $lastvisit = $guest_lastvisit;
            }
        }
        if ($set_cookie) {
            fusion_set_cookie(COOKIE_LASTVISIT, $lastvisit, time() + 3600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE, 'lax');
        }

        return $lastvisit;
    }

    /**
     * Set visitor counter
     */
    public static function setVisitorCounter() {
        if (!isset($_COOKIE[COOKIE_PREFIX.'visited'])) {
            dbquery("UPDATE ".DB_SETTINGS." SET settings_value=settings_value+1 WHERE settings_name='counter'");
            fusion_set_cookie(COOKIE_PREFIX."visited", "yes", time() + 31536000, "/", "", FALSE, FALSE, 'lax');
        }
    }

    public function authRedirection() {
        return $this->two_factor_redirect;
    }

    /**
     * @return array
     */
    public function getUserData() {
        return $this->user_data;
    }
}

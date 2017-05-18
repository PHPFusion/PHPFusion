<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: Authenticate.inc
| Author: Hans Kristian Flaatten (Starefossen)
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
$fusion_domain = (strstr($settings['site_host'], "www.") ? substr($settings['site_host'], 3) : $settings['site_host']);
define("COOKIE_DOMAIN", $settings['site_host'] != 'localhost' ? $fusion_domain : FALSE);
define("COOKIE_PATH", $settings['site_path']);
define("COOKIE_USER", COOKIE_PREFIX."user");
define("COOKIE_ADMIN", COOKIE_PREFIX."admin");
define("COOKIE_VISITED", COOKIE_PREFIX."visited");
define("COOKIE_LASTVISIT", COOKIE_PREFIX."lastvisit");

class Authenticate {

    private static $authenticate_url = "";
    private $_userData = array("user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => "Default");

    public function __construct($inputUserName, $inputPassword, $remember, $authentication_url = FALSE) {

        if ($authentication_url) {
            self::$authenticate_url = $authentication_url;
        }

        $this->_authenticate($inputUserName, $inputPassword, $remember);
    }

    private function _authenticate($inputUserName, $inputPassword, $remember) {
        $locale = fusion_get_locale();
        $settings = fusion_get_settings();
        $inputUserName = preg_replace(array("/\=/", "/\#/", "/\sOR\s/"), "", stripinput($inputUserName));
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
                if ($settings['multiple_logins'] != 1) {
                    $user['user_algo'] = $passAuth->getNewAlgo();
                    $user['user_salt'] = $passAuth->getNewSalt();
                    $user['user_password'] = $passAuth->getNewHash();
                    $result = dbquery("UPDATE ".DB_USERS."
						SET user_algo='".$user['user_algo']."', user_salt='".$user['user_salt']."', user_password='".$user['user_password']."'
						WHERE user_id='".$user['user_id']."'");
                }
                if ($user['user_status'] == 0 && $user['user_actiontime'] == 0) {
                    Authenticate::setUserCookie($user['user_id'], $user['user_salt'], $user['user_algo'], $remember, TRUE);
                    Authenticate::_setUserTheme($user);
                    $this->_userData = $user;
                } else {
                    require_once INCLUDES."suspend_include.php";
                    require_once INCLUDES."sendmail_include.php";
                    if (($user['user_status'] == 3 && $user['user_actiontime'] < time()) || $user['user_status'] == 7) {
                        $result = dbquery("UPDATE ".DB_USERS." SET user_status='0', user_actiontime='0' WHERE user_id='".$user['user_id']."'");
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
                redirect(Authenticate::getRedirectUrl(5));
            }
        } else {
            redirect(Authenticate::getRedirectUrl(1));
        }
    }

    // Get user data when authenticating in user

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
            $cookieExpiration = TIME + 172800; // 48 hours
        }
        $key = hash_hmac($algo, $userID.$cookieExpiration, $salt);
        $hash = hash_hmac($algo, $userID.$cookieExpiration, $key);
        $cookieContent = $userID.".".$cookieExpiration.".".$hash;
        //header("P3P: CP='NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM'");
        Authenticate::_setCookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, COOKIE_DOMAIN, FALSE, TRUE);
        // Unable to set cookies properly
        if (!isset($_COOKIE[COOKIE_VISITED])) {
            redirect(Authenticate::getRedirectUrl(3));
        }
    }

    /* Admin Login Authentication */
    // Set user Cookie
    public static function _setCookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, $cookieDomain, $secure = FALSE, $httpOnly = FALSE) {
        if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
            setcookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, $cookieDomain, $secure, $httpOnly);
        } else {
            setcookie($cookieName, $cookieContent, $cookieExpiration, $cookiePath, $cookieDomain, $secure);
        }
    }

    /**
     * Get the redirection url
     * If there is a new authentication url, error request will not valid
     * @todo: use addNotice('') instead of going for errorId
     * @param        $errorId
     * @param string $userStatus
     * @param string $userId
     * @return string
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

    // Validate authenticated user

    private static function _setUserTheme($user) {
        if ($user['user_level'] == USER_LEVEL_SUPER_ADMIN) {
            return $user['user_level'];
        }
        if (fusion_get_settings("userthemes") == 0 && $user['user_level'] < -102 && $user['user_theme'] != "Default") {
            $user['user_theme'] = "Default";
        }
    }

    public static function setAdminLogin() {
        $locale = fusion_get_locale();
        if (isset($_GET['logout'])) {
            self::expireAdminCookie();
            $user = fusion_get_userdata("user_id");
            session_destroy();
            if (!empty($user)) {
                redirect(BASEDIR."index.php");
            }
        }
        if (isset($_POST['admin_password'])) {
            $admin_password = form_sanitizer($_POST['admin_password'], '', 'admin_password');
            if (Authenticate::validateAuthAdmin($admin_password)) {
                if (Authenticate::setAdminCookie($admin_password)) {
                    unset($_SESSION['notices']);
                    redirect(FUSION_REQUEST);
                } else {
                    addNotice("danger", $locale['cookie_error'], $locale['cookie_error_description']);
                }
            } else {
                addNotice("danger", $locale['password_invalid'], $locale['password_invalid_description']);
            }
        }
        if (defined('ADMIN_PANEL') && !isset($_COOKIE[COOKIE_PREFIX."admin"])) {
            setNotice("danger", $locale['cookie_title'], $locale['cookie_description']);
        }
    }

    // Log out authenticated user

    public static function expireAdminCookie() {
        Authenticate::_setCookie(COOKIE_ADMIN, '', time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE);
    }

    // Checks or sets the lastvisit cookie

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
							WHERE user_id='".(isnum($userID) ? $userID : 0)."' AND user_level < -101 AND  user_status='0' AND user_actiontime='0'
							LIMIT 1");
                        if (dbrows($result) == 1) {
                            $user = dbarray($result);
                            $key = hash_hmac($user['user_admin_algo'], $userID.$cookieExpiration, $user['user_admin_salt']);
                            $hash = hash_hmac($user['user_admin_algo'], $userID.$cookieExpiration, $key);
                            if ($cookieHash == $hash) {
                                $error = FALSE;

                                /**
                                 * New 2nd factor session authentication
                                 */
                                if (empty($_SESSION['aid'])) {
                                    return FALSE;
                                } else {
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
                                        } elseif (!isnum($token_time)) {
                                            $error = $locale['token_error_5'];
                                            // check if the hash is valid
                                        } elseif ($hash != hash_hmac($algo, $key, $salt)) {
                                            $error = $locale['token_error_7'];
                                            // check if a post wasn't made too fast. Set $post_time to 0 for instant. Go for System Settings later.
                                        }
                                    } else {
                                        // token format is incorrect
                                        $error = $locale['token_error_8'];
                                    }
                                    // Check if any error was set
                                    if ($error !== FALSE) {
                                        \defender::stop();
                                        addNotice("warning", $error);

                                        return FALSE;
                                    }
                                }

                                return TRUE;
                            }
                        }
                    }
                }
                // Validate a provided password
            } elseif ($pass != "") {
                $result = dbquery("SELECT user_admin_algo, user_admin_salt, user_admin_password FROM ".DB_USERS."
					WHERE user_id='".$userdata['user_id']."' AND user_level < '-".USER_LEVEL_MEMBER."' AND  user_status='0' AND user_actiontime='0'
					LIMIT 1");
                if (dbrows($result) == 1) {
                    $user = dbarray($result);
                    if ($user['user_admin_algo'] != "md5") {
                        $inputHash = hash_hmac($user['user_admin_algo'], $pass, $user['user_admin_salt']);
                    } else {
                        $inputHash = md5(md5($pass));
                    }
                    if ($inputHash == $user['user_admin_password']) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

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
                    dbquery("UPDATE ".DB_USERS."
					SET user_admin_algo='".$userdata['user_admin_algo']."', user_admin_salt='".$userdata['user_admin_salt']."', user_admin_password='".$userdata['user_admin_password']."'
					WHERE user_id='".$userdata['user_id']."'");
                }
                Authenticate::setUserCookie($userdata['user_id'], $userdata['user_admin_salt'], $userdata['user_admin_algo'], FALSE, FALSE);
                return TRUE;
            }
        }

        return FALSE;
    }

    // Checks and sets the admin last visit cookie
    public static function validateAuthUser($userCookie = TRUE) {
        if (isset($_COOKIE[COOKIE_USER]) && $_COOKIE[COOKIE_USER] != "") {
            $cookieDataArr = explode(".", $_COOKIE[COOKIE_USER]);
            if (count($cookieDataArr) == 3) {
                list($userID, $cookieExpiration, $cookieHash) = $cookieDataArr;
                if ($cookieExpiration > TIME) {
                    // must update user_salt
                    $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".(isnum($userID) ? $userID : 0)."' AND user_status='0' AND user_actiontime='0' LIMIT 1");
                    if (dbrows($result) == 1) {
                        $user = dbarray($result);
                        Authenticate::_setUserTheme($user);
                        // From Version 7 to Version 9, Ported Database has this problem - where user_salt has problem entering
                        $key = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $user['user_salt']);
                        $hash = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $key);
                        if ($cookieHash == $hash) {
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
                }
            } else {
                // Missing arguments in cookie
                Authenticate::logOut();
                redirect(Authenticate::getRedirectUrl(2));
            }
        } else {
            return Authenticate::getEmptyUserData();
        }
    }

    // Get Loging Redirect Url
    public static function logOut() {
        $result = dbquery("DELETE FROM ".DB_ONLINE." WHERE online_ip='".USER_IP."'");
        //header("P3P: CP='NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM'");
        Authenticate::_setCookie(COOKIE_USER, "", time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE);
        Authenticate::_setCookie(COOKIE_LASTVISIT, "", time() - 1209600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE);
        session_destroy();

        return Authenticate::getEmptyUserData();
    }

    // Get Empty User Data

    public static function getEmptyUserData() {
        global $settings;

        return array("user_level" => 0, "user_rights" => "", "user_groups" => "", "user_theme" => $settings['theme']);
    }

    // Set user theme

    public static function setLastVisitCookie() {
        global $userdata;
        $guest_lastvisit = time() - 3600;
        $update_threads = FALSE;
        $set_cookie = TRUE;
        $cookie_exists = isset($_COOKIE[COOKIE_LASTVISIT]) && isnum($_COOKIE[COOKIE_LASTVISIT]) ? TRUE : FALSE;
        if (iMEMBER) {
            if ($cookie_exists) {
                if ($_COOKIE[COOKIE_LASTVISIT] > $userdata['user_lastvisit']) {
                    $update_threads = TRUE;
                    $lastvisit = $userdata['user_lastvisit'];
                } else {
                    $set_cookie = FALSE;
                    $lastvisit = $_COOKIE[COOKIE_LASTVISIT];
                }
            } else {
                $update_threads = TRUE;
                $lastvisit = $userdata['user_lastvisit'];
            }
            if ($update_threads) {
                dbquery("UPDATE ".DB_USERS." SET user_threads='' WHERE user_id='".$userdata['user_id']."'");
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
            Authenticate::_setCookie(COOKIE_LASTVISIT, $lastvisit, time() + 3600, COOKIE_PATH, COOKIE_DOMAIN, FALSE, TRUE);
        }

        return $lastvisit;
    }

    public function getUserData() {
        return $this->_userData;
    }
}

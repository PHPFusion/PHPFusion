<?php

/**
 * File autoloading
 * Given a file, i.e. /css/base.css, replaces it with a string containing the file's mtime, i.e. /css/base.1221534296.css.
 *
 * @param $file_path - The file to be loaded.  Must be an absolute path (i.e. starting with slash)
 *
 * Add to .htacces
 * RewriteEngine on
 * RewriteRule ^(.*)\.[\d]{10}\.(css|js)$ $1.$2 [L]
 *
 * @return string
 */
use PHPFusion\OpenGraph;

if (!function_exists("auto_file")) {
    function auto_file($file_path, $show_warnings = FALSE) {

        $file_info = pathinfo($file_path);

        try {
            if (isset($file_info['dirname']) && isset($file_info['basename']) && isset($file_info['extension']) && isset($file_info['filename'])) {

                $file = $file_info['dirname'].DIRECTORY_SEPARATOR.$file_info['basename'];

                $min_file = $file_info['dirname'].DIRECTORY_SEPARATOR.$file_info['filename'].'.min.'.$file_info['extension'];

                $return_file = $file;

                if (!is_file($file) && !is_file($min_file) && $show_warnings === TRUE) {
                    add_notice("danger", "File <strong>$file_path</strong> does not exist");
                }

                if (is_file($min_file)) {
                    //if (!fusion_get_settings("devmode")) {
                    $return_file = $min_file;
                    //}
                }

                if (is_file($return_file)) {
                    $mtime = filemtime($return_file);
                    $file = preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $return_file);
                    return $file;
                }

                return "";
            }
            throw new \Exception("The file specified was invalid");

        } catch (\Exception $e) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return "";
    }
}

if (!function_exists("showdatetime")) {
    /**
     * @param int    $timestamp
     * @param string $date_format
     * @param string $time_format
     * @param bool   $showtime
     *
     * @return string
     */
    function showdatetime(int $timestamp, string $date_format = "j M Y", $time_format = "h:i A", $showtime = TRUE) {
        $timezone_offset = fusion_get_settings("serveroffset");
        if (iMEMBER) {
            $user_offset = fusion_get_userdata("user_timezone");
            $timezone_offset = ($user_offset ?: $timezone_offset);
        }
        try {
            $date = new DateTime();
            $date->setTimestamp($timestamp);
            $date->setTimezone(new DateTimeZone($timezone_offset));
            $date_value = $date->format($date_format);
            $time_value = "";
            if ($showtime) {
                //$time_value = fusion_get_locale("at")." ".$date->format($time_format);
                $time_value = "at ".$date->format($time_format);
            }
            return $date_value.whitespace($time_value);
        } catch (\Exception $e) {
            set_error(E_USER_NOTICE, $e->getMessage(), $e->getFile(), $e->getLine());
        }
        return showdate("longdate", $timestamp);
    }
}

/**
 * Authenticate the current user with fusion cookie format param
 *
 * @return bool
 */
function auth_user_cookie() {
    if ($user_cookie = $_COOKIE[COOKIE_USER]) {
        $auth = explode('.', $user_cookie);
        if (count($auth) == 3) {
            [$userID, $cookieExpiration, $cookieHash] = $auth;
            if ($cookieExpiration > TIME) {
                $result = dbquery("SELECT * FROM ".DB_USERS." WHERE user_id='".(isnum($userID) ? $userID : 0)."' AND user_status='0' AND user_actiontime='0' LIMIT 1");
                if (dbrows($result) == 1) {
                    $user = dbarray($result);
                    $hash = $user["user_password"];
                    // definition
                    set_user_level();

                    $key = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $user['user_salt']);
                    $hash = hash_hmac($user['user_algo'], $userID.$cookieExpiration, $key);
                    if ($cookieHash == $hash) {
                        return $userID;
                    }
                    return FALSE;
                }
            }
        }
    }
    return FALSE;
}

/**
 * Authenticate the user with password
 * @param string $password
 */
function auth_user_password($password = "") {

}

// User level, Admin Rights & User Group definitions
function set_user_level() {
    $userdata = fusion_get_userdata();
    if (!defined('iGUEST')) {
        define("iGUEST", $userdata['user_level'] == USER_LEVEL_PUBLIC ? 1 : 0);
        define("iMEMBER", $userdata['user_level'] <= USER_LEVEL_MEMBER ? 1 : 0);
        define("iADMIN", $userdata['user_level'] <= USER_LEVEL_ADMIN ? 1 : 0);
        define("iSUPERADMIN", $userdata['user_level'] == USER_LEVEL_SUPER_ADMIN ? 1 : 0);
        define("iUSER", $userdata['user_level']);
        define("iUSER_RIGHTS", $userdata['user_rights']);
        define("iUSER_GROUPS", substr($userdata['user_groups'], 1));
    }
}

function set_og($title = '', $description = '', $url = '', $keywords = '') {
    OpenGraph::setCustom([
        'title'       => $title,
        'description' => $description,
        'url'         => $url,
        'keywords'    => $keywords
    ]);
}

<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: ip_handling_include.php
| Author: Karoly Nagy (Korcsii)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
defined('IN_FUSION') || exit;

if (!function_exists('uncompressipv6')) {
    /**
     * Convert a shortened IPv6 address to its full length form.
     *
     * @param string $ip    IPv6 address to convert.
     * @param int    $count This parameter shows how many : are in the full length version.
     *                      Note: IPv6 address has 7 of them, but the mixed (IPv6 and IPv4) address has only 5.
     *
     * @return string
     */
    function uncompressipv6($ip, $count = 7) {
        if (strpos($ip, "::") !== FALSE) {
            $ip = str_replace("::", str_repeat(":", $count + 2 - substr_count($ip, ":")), $ip);
        }
        $tmp_ip = explode(":", $ip);
        foreach ($tmp_ip as &$value) {
            $value = str_pad($value, 4, '0', STR_PAD_LEFT);
        }

        return implode(":", $tmp_ip);
    }
}

/**
 * Check if user's full or partial ip is blacklisted.
 *
 * @return bool
 */
function is_blacklisted() {
    if (strpos(FUSION_IP, ".")) {
        if (strpos(FUSION_IP, ":") === FALSE) {
            // IPv4
            if (!defined('USER_IP_TYPE')) {
                define("USER_IP_TYPE", 4);
            }
            if (!defined('USER_IP')) {
                define("USER_IP", FUSION_IP);
            }
            $check_value = "blacklist_ip_type='4' AND blacklist_ip REGEXP '^";
            $check_value .= str_replace(".", "(\.", USER_IP, $i);
            $check_value .= str_repeat(")?", $i);
            $check_value .= "$'";
        } else {
            // Mixed IPv4 and IPv6
            define("USER_IP_TYPE", 5);
            $last_pos = strrpos(FUSION_IP, ":");
            $ipv4 = substr(FUSION_IP, $last_pos + 1);
            $ipv6 = substr(FUSION_IP, 0, $last_pos);
            $ipv6 = uncompressipv6($ipv6, 5);
            define("USER_IP", $ipv6.":".$ipv4);
            $check_value = "(blacklist_ip_type='4' AND blacklist_ip REGEXP '^";
            $check_value .= str_replace(".", "(\.", $ipv4, $i);
            $check_value .= str_repeat(")?", $i);
            $check_value .= "$') OR (blacklist_ip_type='6' AND blacklist_ip REGEXP '^";
            $check_value .= str_replace(":", "(:", $ipv6, $i);
            $check_value .= str_repeat(")?", $i);
            $check_value .= "$') OR (blacklist_ip_type='5' AND blacklist_ip='".USER_IP."')";
            unset($ipv4, $ipv6, $last_pos);
        }
    } else {
        // IPv6
        if (!defined('USER_IP_TYPE')) {
            define("USER_IP_TYPE", 6);
        }
        if (!defined('USER_IP')) {
            define("USER_IP", uncompressipv6(FUSION_IP));
        }
        $check_value = "blacklist_ip_type='6' AND blacklist_ip REGEXP '^";
        $check_value .= str_replace(":", "(:", USER_IP, $i);
        $check_value .= str_repeat(")?", $i);
        $check_value .= "$'";
    }

    return dbcount("(blacklist_id)", DB_BLACKLIST, $check_value) > 0;
}

if (is_blacklisted()) {
    $settings = fusion_get_settings();
    $link = (empty($settings['blaclist_site']) ? "http://www.google.com/" : (!preg_match("@^http(s)?\:\/\/@i", $settings['blaclist_site']) ? "http://".$settings['blaclist_site'] : $settings['blaclist_site']));
    redirect($link);
}

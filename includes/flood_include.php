<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: flood_include.php
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
if (!defined("IN_FUSION")) {
    die("Access Denied");
}

// Enable ajax based flood control
function flood_control($field, $table, $where, $debug = FALSE) {
    $userdata = fusion_get_userdata('user_id');
    $settings = fusion_get_settings();
    $locale = fusion_get_locale();
    $flood = FALSE;
    if ((!iSUPERADMIN && !iADMIN && (!defined('iMOD') || !iMOD)) || $debug) {
        $result = dbquery("SELECT MAX(".$field.") 'last_post' FROM ".$table." WHERE ".$where);
        if (dbrows($result)) {
            $time = TIME;
            $data = dbarray($result);
            if (($time - $data['last_post']) < $settings['flood_interval']) {
                $flood = (sprintf($locale['flood'], countdown($settings['flood_interval'] - ($time - $data['last_post']))));
                \defender::stop($flood);
                dbquery("INSERT INTO ".DB_FLOOD_CONTROL." (flood_ip, flood_ip_type, flood_timestamp) VALUES ('".USER_IP."', '".USER_IP_TYPE."', '".time()."')");
                // This should be in settings, "After how many flood offences take action" then a setting for what action to take
                if (dbcount("(flood_ip)", DB_FLOOD_CONTROL, "flood_ip='".USER_IP."'") > 4) {
                    if (!$debug) {
                        if (iMEMBER && $settings['flood_autoban'] == "1") {
                            require_once INCLUDES."sendmail_include.php";
                            require_once INCLUDES."suspend_include.php";
                            dbquery("UPDATE ".DB_USERS." SET user_status='4', user_actiontime='0' WHERE user_id='".$userdata['user_id']."'");
                            suspend_log($userdata['user_id'], 4, $locale['global_440'], TRUE);
                            $message = str_replace("[USER_NAME]", $userdata['user_name'], $locale['global_442']);
                            $message = str_replace("[USER_IP]", USER_IP, $message);
                            $message = str_replace("[USER_IP]", USER_IP, $message);
                            $message = str_replace("[SITE_EMAIL]", $settings['siteemail'], $message);
                            $message = str_replace("[SITEUSERNAME]", $settings['siteusername'], $message);
                            $subject = str_replace("[SITENAME]", $settings['sitename'], $locale['global_441']);
                            sendemail($userdata['user_name'], $userdata['user_email'], $settings['siteusername'], $settings['siteemail'], $subject,
                                      $message);
                        } elseif (!iMEMBER) {
                            dbquery("INSERT INTO ".DB_BLACKLIST." (blacklist_ip, blacklist_ip_type, blacklist_email, blacklist_reason) VALUES ('".USER_IP."', '".USER_IP_TYPE."', '', '".$locale['global_440']."')");
                        }
                    } else {
                        addNotice('info',
                            "DEBUG MESSAGE: Triggered flood control action due to repeated offences. This could've resulted in a ban or suspension");
                    }

                }
            }
        }
    }

    return $flood;
}
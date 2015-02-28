<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: flood_include.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined("IN_FUSION")) { die("Access Denied"); }

function flood_control($field, $table, $where) {
	
	global $userdata, $settings, $locale;
	
	$flood = false;

	if (!iSUPERADMIN && !iADMIN && (!defined("iMOD") || !iMOD)) {
		$result = dbquery("SELECT MAX(".$field.") AS last_post FROM ".$table." WHERE ".$where);
		if (dbrows($result)) {
			$data = dbarray($result);
			if ((time() - $data['last_post']) < $settings['flood_interval']) {
				$flood = true;
				$result = dbquery("INSERT INTO ".DB_FLOOD_CONTROL." (flood_ip, flood_ip_type, flood_timestamp) VALUES ('".USER_IP."', '".USER_IP_TYPE."', '".time()."')");
				if (dbcount("(flood_ip)", DB_FLOOD_CONTROL, "flood_ip='".USER_IP."'") > 4) {
					if (iMEMBER && $settings['flood_autoban'] == "1") {
						require_once INCLUDES."sendmail_include.php";
						require_once INCLUDES."suspend_include.php";
						
						$result = dbquery("UPDATE ".DB_USERS." SET user_status='4', user_actiontime='0' WHERE user_id='".$userdata['user_id']."'");
						suspend_log($userdata['user_id'], 4, $locale['global_440'], true);
						$message = str_replace("[USER_NAME]", $userdata['user_name'], $locale['global_442']);
						sendemail($userdata['user_name'], $userdata['user_email'], $settings['siteusername'], $settings['siteemail'], $locale['global_441'], $message);
					} elseif (!iMEMBER) {
						$result = dbquery("INSERT INTO ".DB_BLACKLIST." (blacklist_ip, blacklist_ip_type, blacklist_email, blacklist_reason) VALUES ('".USER_IP."', '".USER_IP_TYPE."', '', '".$locale['global_440']."')");
					}
				}
			}
		}
	}	
	return $flood;
}
?>
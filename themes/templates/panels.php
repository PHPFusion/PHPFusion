<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: panels.php
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
if (!defined("IN_FUSION")) { die("Access Denied"); }

// Add admin message
$ad_mess = array(); $admin_mess ='';
if (iADMIN && !defined("ADMIN_PANEL")) {
	$admin_mess .= "<a id='content' name='content'></a>\n";
	if (iSUPERADMIN && file_exists(BASEDIR."setup.php")) $ad_mess[] = $locale['global_198'];
	if ($settings['maintenance']) $ad_mess[] = $locale['global_190'];
	if (!$userdata['user_admin_password']) $ad_mess[] = $locale['global_199'];
	if (!empty($ad_mess)) {
		$admin_mess .= "<div class='admin-message'>";
			foreach ($ad_mess as $message) {
				$admin_mess .= $message."<br />\n";
			}
		$admin_mess .= "</div>\n";
	}
}

$admin_mess .= "<noscript><div class='noscript-message admin-message'>".$locale['global_303']."</div>\n</noscript>\n<!--error_handler-->\n";


// Declare panels side
$p_name = array(
	array('name' => 'LEFT', 'side' => 'left'),
	array('name' => 'U_CENTER', 'side' => 'upper'),
	array('name' => 'L_CENTER', 'side' => 'lower'),
	array('name' => 'RIGHT', 'side' => 'right')
);

// Get panels data to array
$panels_cache = array();
$p_result = dbquery("SELECT panel_name, panel_filename, panel_content, panel_side, panel_type, panel_access, panel_display, panel_url_list, panel_restriction FROM ".DB_PANELS." WHERE panel_status='1' ORDER BY panel_side, panel_order");
while ($panel_data = dbarray($p_result)) {
	if (checkgroup($panel_data['panel_access'])) { $panels_cache[$panel_data['panel_side']][] = $panel_data; }
}

$url_arr = array();
foreach ($p_name as $p_key => $p_side) {
	if (isset($panels_cache[$p_key + 1]) || defined("ADMIN_PANEL")) {
		ob_start();
		if (!defined("ADMIN_PANEL")) {
			if (check_panel_status($p_side['side'])) {
				foreach ($panels_cache[$p_key + 1] as $p_data) {
					$url_arr = explode("\r\n", $p_data['panel_url_list']);
					if ($p_data['panel_url_list'] == ""
						|| ($p_data['panel_restriction'] == 1 && (!in_array(TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $url_arr) && !in_array(TRUE_PHP_SELF, $url_arr)))
						|| ($p_data['panel_restriction'] == 0 && (in_array(TRUE_PHP_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : ""), $url_arr)  || in_array(TRUE_PHP_SELF, $url_arr))))
					{
						if (($p_data['panel_side'] != 2 && $p_data['panel_side'] != 3)
							|| $p_data['panel_display'] == 1 || $settings['opening_page'] == START_PAGE)
						{
							if ($p_data['panel_type'] == "file") {
								if (file_exists(INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php")) {
									include INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php";
								}
							} else {
								eval(stripslashes($p_data['panel_content']));
							}
						}
					}
				}
				unset($p_data);
			}
		} else if ($p_key == 0) {
			require_once ADMIN."navigation.php";
		}
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : '').ob_get_contents());
		ob_end_clean();
	} else {
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));
	}
}
unset($panels_cache);

if (defined("ADMIN_PANEL") || LEFT && !RIGHT) {
	$main_style = "side-left";
} elseif (LEFT && RIGHT) {
	$main_style = "side-both";
} elseif (!LEFT && RIGHT) {
	$main_style = "side-right";
} elseif (!LEFT && !RIGHT) {
	$main_style = "";
}
?>
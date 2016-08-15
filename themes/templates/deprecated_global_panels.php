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

$settings = \fusion_get_settings();
$locale = \fusion_get_locale();

$site['path'] = ltrim(TRUE_PHP_SELF, '/').(FUSION_QUERY ? "?".FUSION_QUERY : "");
if ($settings['site_seo'] == 1 && defined('IN_PERMALINK') && !isset($_GET['aid'])) {
    global $filepath;
    $site['path'] = $filepath;
}

// Add admin message
$ad_mess = array();
$admin_mess = '';
$admin_mess .= "<noscript><div class='alert alert-danger noscript-message admin-message'><strong>".$locale['global_303']."</strong></div>\n</noscript>\n<!--error_handler-->\n";
// Declare panels side
$p_name = array(array('name' => 'LEFT', 'side' => 'left'),
				array('name' => 'U_CENTER', 'side' => 'upper'),
				array('name' => 'L_CENTER', 'side' => 'lower'),
				array('name' => 'RIGHT', 'side' => 'right'),
				array('name' => 'AU_CENTER', 'side' => 'aupper'),
				array('name' => 'BL_CENTER', 'side' => 'blower')
			);
// Get panels data to array
$panels_cache = array();
$p_result = dbquery("SELECT panel_name, panel_filename, panel_content, panel_side, panel_type, panel_access, panel_display, panel_url_list, panel_restriction, panel_languages FROM ".DB_PANELS." WHERE panel_status='1' ORDER BY panel_side, panel_order");
if (multilang_table("PN")) {
	while ($panel_data = dbarray($p_result)) {
			$p_langs = explode('.', $panel_data['panel_languages']);
			if (checkgroup($panel_data['panel_access']) && in_array(LANGUAGE, $p_langs)) {
				$panels_cache[$panel_data['panel_side']][] = $panel_data;
			}
		}
} else {
		while ($panel_data = dbarray($p_result)) {
			if (checkgroup($panel_data['panel_access'])) {
				$panels_cache[$panel_data['panel_side']][] = $panel_data;
			}
		}
}
$url_arr = array();
foreach ($p_name as $p_key => $p_side) {
	if (isset($panels_cache[$p_key+1]) || defined("ADMIN_PANEL")) {
		ob_start();
		if (!defined("ADMIN_PANEL")) {
			if (check_panel_status($p_side['side'])) {

                // Panel display can be deprecated - For compatibility reasons.

				foreach ($panels_cache[$p_key+1] as $p_data) {

                    $url_arr = explode("\r\n", $p_data['panel_url_list']);
					$url = array();
					foreach($url_arr as $url_list) {
						$url[] = $url_list; //strpos($urldata, '/', 0) ? $urldata : '/'.
					}
                    $show_panel = FALSE;
					/*
					 * show only if the following conditions are met:
					 * */
                    switch($p_data['panel_restriction']) {
                        case 1:
                            //  Exclude on current url only
                            //  url_list is set, and panel_restriction set to 1 (Exclude) and current page does not match url_list.
                            if (!empty($p_data['panel_url_list']) && !in_array($site['path'], $url)) {
                                $show_panel = TRUE;
                            }
                            break;
                        case 2: // Display on home page only
                            if (!empty($p_data['panel_url_list']) && $site['path'] == fusion_get_settings('opening_page')) {
                                $show_panel = TRUE;
                            }
                        break;
                        case 3: // Display on all pages
                            //  url_list must be blank
                            if (empty($p_data['panel_url_list'])) {
                                $show_panel = TRUE;
                            }
                        break;
                        default: // Include on defined url only
                            //  url_list is set, and panel_restriction set to 0 (Include) and current page matches url_list.
                            if (!empty($p_data['panel_url_list']) && in_array($site['path'], $url)) {
                                $show_panel = TRUE;
                            }
                        break;
                    }

                    if ($show_panel) {
                        //if (($p_data['panel_side'] == 1 || $p_data['panel_side'] == 4) && $p_data['panel_display'] == 1) {
                            if ($p_data['panel_type'] == "file") {
                                if (file_exists(INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php")) {
                                    include INFUSIONS.$p_data['panel_filename']."/".$p_data['panel_filename'].".php";
                                }
                            } else {
                                if (fusion_get_settings("allow_php_exe")) {
                                    eval(stripslashes($p_data['panel_content']));
                                } else {
                                    echo parse_textarea($p_data['panel_content']);
                                }
                            }
                        //}
					}
				}
				unset($p_data);
				if (multilang_table("PN")) {
					unset($p_langs);
				}
			}
		} else if ($p_key == 0) {
			//require_once ADMIN."navigation.php";
		}
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : '').ob_get_contents());
		ob_end_clean();
	} else {
		define($p_side['name'], ($p_side['name'] === 'U_CENTER' ? $admin_mess : ''));
	}
}
unset($panels_cache);

//@todo: this can be absorbed into theme engine's settings
if (defined("ADMIN_PANEL") || LEFT && !RIGHT) {
	$main_style = "side-left";
} elseif (LEFT && RIGHT) {
	$main_style = "side-both";
} elseif (!LEFT && RIGHT) {
	$main_style = "side-right";
} elseif (!LEFT && !RIGHT) {
	$main_style = "";
}
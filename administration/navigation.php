<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: navigation.php
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
if (!defined("IN_FUSION")) {
	die("Access Denied");
}
include LOCALE.LOCALESET."admin/main.php";
//include INFUSIONS."user_info_panel/user_info_panel.php";
@list($title) = dbarraynum(dbquery("SELECT admin_title FROM ".DB_ADMIN." WHERE admin_link='".FUSION_SELF."'"));
add_to_title($locale['global_200'].$locale['global_123'].($title ? $locale['global_201'].$title : ""));
$pages = array(1 => FALSE, 2 => FALSE, 3 => FALSE, 4 => FALSE, 5 => FALSE);
$index_link = FALSE;
$admin_nav_opts = "";
$current_page = 0;
$result = dbquery("SELECT admin_title, admin_page, admin_rights, admin_link FROM ".DB_ADMIN." ORDER BY admin_page DESC, admin_title ASC");
$rows = dbrows($result);
$admin_url = array();
while ($data = dbarray($result)) {
	if ($data['admin_link'] != "reserved" && checkrights($data['admin_rights'])) {
		$admin_pages[$data['admin_page']][$data['admin_title']] = $data['admin_link'];
		$pages[$data['admin_page']] .= "<option value='".ADMIN.$data['admin_link'].$aidlink."'>".preg_replace("/&(?!(#\d+|\w+);)/", "&amp;", $data['admin_title'])."</option>\n";
	}
}
function admin_active() {
	global $admin_pages, $settings, $aidlink;
	$inf_page_request = FUSION_REQUEST;
	if (isset($_GET['section'])) {
		$inf_page_request = str_replace("&amp;section=".$_GET['section']."", "", $inf_page_request);
	}
	if (stristr(FUSION_REQUEST, '/infusions/')) {
		$inf_page_request = str_replace($settings['site_path'], '', "../".str_replace($aidlink, '', $inf_page_request));
	}
	foreach ($admin_pages as $key => $data) {
		if (in_array(FUSION_SELF, $data) || in_array($inf_page_request, $data)) {
			return $key;
		}
	}
	return '0';
}

function admin_nav($style = FALSE) {
	global $aidlink, $locale, $settings, $pages;
	$admin_icon = array('0' => 'entypo gauge', '1' => 'entypo docs', '2' => 'entypo user', '3' => 'entypo drive', '4' => 'entypo cog', '5' => 'entypo magnet');
	$inf_page_request = FUSION_REQUEST;
	if (isset($_GET['section'])) {
		$inf_page_request = str_replace("&amp;section=".$_GET['section']."", "", $inf_page_request);
	}
	if (stristr(FUSION_REQUEST, '/infusions/')) {
		$inf_page_request = str_replace($settings['site_path'], '', "../".str_replace($aidlink, '', $inf_page_request));
	}
	if (!$style) {
		// horizontal navigation with dropdown menu.
		$html = "<ul class='admin-horizontal-link'>\n";
		for ($i = 0; $i < 6; $i++) {
			$html .= "<li><a href='".ADMIN.$aidlink."&amp;pagenum=$i'><i class='".$admin_icon[$i]."'></i> ".$locale['ac0'.$i]."</a></li>\n";
		}
		$html .= "</ul>\n";
	} else {
		$html = "<ul id='adl' class='admin-vertical-link'>\n";
		for ($i = 0; $i < 6; $i++) {
			$result = dbquery("SELECT * FROM ".DB_ADMIN." WHERE admin_page='".$i."' AND admin_link !='reserved' ORDER BY admin_title ASC");
			$active = (isset($_GET['pagenum']) && $_GET['pagenum'] == $i || !isset($_GET['pagenum']) && admin_active() == $i) ? 1 : 0;
			$html .= "<li class='".($active ? 'active panel' : 'panel')."' >\n";
			if ($i == 0) {
				$html .= "<a class='adl-link' href='".ADMIN."index.php".$aidlink."&amp;pagenum=0'><i class='".$admin_icon[$i]."'></i> ".$locale['ac0'.$i]." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
			} else {
				$html .= "<a class='adl-link ".($active ? '' : 'collapsed')."' data-parent='#adl' data-toggle='collapse' href='#adl-$i'><i class='".$admin_icon[$i]."'></i> ".$locale['ac0'.$i]." ".($i > 0 ? "<span class='adl-drop pull-right'></span>" : '')."</a>\n";
				$html .= "<div id='adl-$i' class='collapse ".($active ? 'in' : '')."'>\n";
				if (dbrows($result) > 0) {
					$html .= "<ul class='admin-submenu'>\n";
					while ($data = dbarray($result)) {
						$secondary_active = FUSION_SELF == $data['admin_link'] || $inf_page_request == $data['admin_link'] ? "class='active'" : '';
						$html .= checkrights($data['admin_rights']) ? "<li $secondary_active><a href='".ADMIN.$data['admin_link'].$aidlink."'> <img style='max-width:24px;' class='pull-right m-l-10' src='".get_image("ac_".$data['admin_rights'])."'/> ".$data['admin_title']."</a></li>\n" : '';
					}
					$html .= "</ul>\n";
				}
				$html .= "</div>\n";
				$html .= "</li>\n";
			}
		}
		$html .= "</ul>\n";
	}
	return $html;
}

/*
 * openside($locale['global_001']);
$content = FALSE;
for ($i = 1; $i < 6; $i++) {
	$page = $pages[$i];
	if ($i == 1) {
		echo THEME_BULLET." <a href='".ADMIN."index.php".$aidlink."' class='side'>".$locale['ac00']."</a>\n";
		echo "<hr class='side-hr' />\n";
	}
	if ($page) {
		$admin_pages = TRUE;
		echo "<form action='".FUSION_SELF."'>\n";
		echo "<select onchange='window.location.href=this.value' style='width:100%;' class='textbox'>\n";
		echo "<option value='".FUSION_SELF."' style='font-style:italic;' selected='selected'>".$locale['ac0'.$i]."</option>\n";
		echo $page."</select>\n</form>\n";
		$content = TRUE;
	}
	if ($i == 5) {
		if ($content) {
			echo "<hr class='side-hr' />\n";
		}
		echo THEME_BULLET." <a href='".BASEDIR."index.php' class='side'>".$locale['global_181']."</a>\n";
	}
}
closeside();
*/
?>
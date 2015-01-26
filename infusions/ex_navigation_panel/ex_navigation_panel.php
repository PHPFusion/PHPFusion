<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: ex_navigation_panel.php
| Author: Stas Beh (dialektika)
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
if(FUSION_SELF=="viewpage.php")
{
include_once INFUSIONS."ex_navigation_panel/infusion_db.php";
include_once INCLUDES."infusions_include.php";
// Check if a locale file is available that match the selected locale.
if (file_exists(INFUSIONS."ex_navigation_panel/locale/".LANGUAGE.".php")) {
	// Load the locale file matching selection.
	include INFUSIONS."ex_navigation_panel/locale/".LANGUAGE.".php";
} else {
	// Load the default locale file.
	include INFUSIONS."ex_navigation_panel/locale/English.php";
}
$exnav_settings = get_settings("ex_navigation_panel");
$link = FUSION_SELF.(FUSION_QUERY ? "?".FUSION_QUERY : "");
$link = preg_replace("^(&amp;|\?)s_action=(edit|delete)&amp;exlink_id=\d*^", "", $link);
$sep = stristr($link, "?") ? "&amp;" : "?";
$shout_link = "";
$shout_message = "";
openside($locale['ENP_title']);

$result = dbquery("SELECT tl.exlink_name, tl.exlink_url, tl.exlink_position, tl.exlink_window 
	FROM ".DB_EXNAVPANEL." tl where tl.exlink_page=".$_GET['page_id']." 
	ORDER BY tl.exlink_position ASC ");

if (dbrows($result)) {
	$i = 0;
	echo "<div id='navigation'>\n";
	while ($data = dbarray($result)) {
		$li_class = "";
		$i++;
			if ($data['exlink_name'] != "---" && $data['exlink_url'] == "---") {
				if ($list_open) {
					echo "</ul>\n";
					$list_open = FALSE;
				}
				echo "<h2>".parseubb($data['exlink_name'], "b|i|u|color|img")."</h2>\n";
			} else if ($data['exlink_name'] == "---" && $data['exlink_url'] == "---") {
				if ($list_open) {
					echo "</ul>\n";
					$list_open = FALSE;
				}
				echo "<hr class='side-hr' />\n";
			} else {
				if (!$list_open) {
					echo "<ul>\n";
					$list_open = TRUE;
				}
				$link_target = ($data['exlink_window'] == "1" ? " target='_blank'" : "");
				if ($i == 1) {
					$li_class = "first-link";
				}
				if (START_PAGE == $data['exlink_url']) {
					$li_class .= ($li_class ? " " : "")."current-link";
				}
				if (preg_match("!^(ht|f)tp(s)?://!i", $data['exlink_url'])) {
					echo "<li".($li_class ? " class='".$li_class."'" : "").">\n";
					echo "<a href='".$data['exlink_url']."'".$link_target." class='side'>".THEME_BULLET."\n";
					echo "<span>".parseubb($data['exlink_name'], "b|i|u|color|img")."</span></a></li>\n";
				} else {
					echo "<li".($li_class ? " class='".$li_class."'" : "").">\n";
					echo "<a href='".BASEDIR.$data['exlink_url']."'".$link_target." class='side'>".THEME_BULLET."\n";
					echo "<span>".parseubb($data['exlink_name'], "b|i|u|color|img")."</span></a></li>\n";
				}
			}
		
	}
	if ($list_open) {
		echo "</ul>\n";
	}
	echo "</div>\n";
} else {
	echo $locale['ENP_no_msgs'];
}
closeside();
}
?>

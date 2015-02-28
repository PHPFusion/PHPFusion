<?php	
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: navigation_panel.php
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

$list_open = false;

openside($locale['global_001']);
$result = dbquery(
	"SELECT link_name, link_url, link_window, link_visibility FROM ".DB_SITE_LINKS."
	WHERE link_position='1' OR link_position='2' ORDER BY link_order"
);
if (dbrows($result)) {
	$i = 0;
	echo "<div id='navigation'>\n";
	while($data = dbarray($result)) {
		$li_class = ""; $i++;
		if (checkgroup($data['link_visibility'])) {
			if ($data['link_name'] != "---" && $data['link_url'] == "---") {
				if ($list_open) { echo "</ul>\n"; $list_open = false; }
				echo "<h2>".parseubb($data['link_name'], "b|i|u|color|img")."</h2>\n";
			} else if ($data['link_name'] == "---" && $data['link_url'] == "---") {
				if ($list_open) { echo "</ul>\n"; $list_open = false; }
				echo "<hr class='side-hr' />\n";
			} else {
				if (!$list_open) { echo "<ul>\n"; $list_open = true; }
				$link_target = ($data['link_window'] == "1" ? " target='_blank'" : "");
				if ($i == 1) { $li_class = "first-link"; }
				if (START_PAGE == $data['link_url']) { $li_class .= ($li_class ? " " : "")."current-link"; }
				if (preg_match("!^(ht|f)tp(s)?://!i", $data['link_url'])) {
					echo "<li".($li_class ? " class='".$li_class."'" : "").">\n";
					echo "<a href='".$data['link_url']."'".$link_target." class='side'>".THEME_BULLET."\n";
					echo "<span>".parseubb($data['link_name'], "b|i|u|color|img")."</span></a></li>\n";
				} else {
					echo "<li".($li_class ? " class='".$li_class."'" : "").">\n";
					echo "<a href='".BASEDIR.$data['link_url']."'".$link_target." class='side'>".THEME_BULLET."\n";
					echo "<span>".parseubb($data['link_name'], "b|i|u|color|img")."</span></a></li>\n";
				}
			}
		}
	}
	if ($list_open) { echo "</ul>\n"; }
	echo "</div>\n";
} else {
	echo $locale['global_002'];
}
closeside();
?>
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: panel_functions.php
| Version: 1.00
| Author: PHP-Fusion Mods UK
| Developer & Designer: Craig, Chan
| Site: http://www.phpfusionmods.co.uk
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
function opentable($title) {
	echo "<article><h2 class='m-t-0 m-b-0'>".$title."</h2><div class='content'>\n";
}

function closetable() { echo "</div></article>\n"; }

function openside($title, $collapse = FALSE, $state = "on") {
	global $panel_collapse;
	$panel_collapse = $collapse;
	echo "<div class='heading'>\n";
	echo "<div style='margin-left: 10px;'>".$title."</div>\n";
	echo "</div>\n";
	if ($collapse == TRUE) {
		$boxname = str_replace(" ", "", $title);
		echo "<div class='pull-right' style='padding-top: 10px;'>".panelbutton($state, $boxname)."</div>\n";
	}
	echo "<div class='content'>\n";
	if ($collapse == TRUE) {
		echo panelstate($state, $boxname);
	}
}

function closeside() {
	global $panel_collapse;
	if ($panel_collapse == TRUE) {
		echo "</div>\n";
	}
	echo "</div>";
}

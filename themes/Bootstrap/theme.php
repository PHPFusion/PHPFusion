<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Bootstrap Theme
| Filename: theme.php
| Author: Frederick MC Chan (Hien)
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

define("THEME_BULLET", "&middot;");
require_once INCLUDES."theme_functions_include.php";
include "functions.php";

function render_page($license = FALSE) {
	//add_handler("theme_output");
	global $settings, $main_style, $locale, $userdata, $aidlink, $mysql_queries_time;
	// set variables
	$brand = $settings['sitebanner'] ? "<img title='".$settings['sitename']."' style='margin-left:-20px; width:100%; margin-top:-35px;' src='".BASEDIR.$settings['sitebanner']."'/>" : $settings['sitename'];
	// set size - max of 12 min of 0
	$side_grid_settings = array(
		'desktop_size' => 2,
		'laptop_size' => 3,
		'tablet_size' => 3,
		'phone_size' => 4,
	);

	// Render Theme
	echo "<div class='container p-t-20 p-b-20'>\n";
	echo "<div class='display-inline-block m-t-20 m-l-20' style='max-width: 280px;'>";
	echo $brand;
	echo "</div>\n";
	echo showsublinks('', '', array('logo'=>$brand))."\n";
	// row 1 - go for max width
	if (defined('AU_CENTER') && AU_CENTER) echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".AU_CENTER."</div>\n</div>";
	// row 2 - fluid setitngs depending on panel appearances
	echo "<div class='row'>\n";
	if (defined('LEFT') && LEFT) echo "<div class='".html_prefix($side_grid_settings)."'>\n".LEFT."</div>\n"; // column left
	echo "<div class='".html_prefix(center_grid_settings($side_grid_settings))."'>\n".U_CENTER.CONTENT.L_CENTER."</div>\n"; // column center
	if (defined('RIGHT') && RIGHT) echo "<div class='".html_prefix($side_grid_settings)."'>\n".RIGHT."</div>\n"; // column right
	echo "</div>\n";
	// row 3
	if (defined('BL_CENTER') && BL_CENTER) echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>".BL_CENTER."</div>\n</div>";
	// footer
	echo "<hr>\n";
	echo "<div class='row'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";
	echo "<span>".stripslashes(strip_tags($settings['footer']))."</span><br/>\n";
	echo "<span>".showcopyright()."</span><br/>\n";
	echo "<span>Bootstrap Theme by <a href='http://www.php-fusion.co.uk' target='_blank'>PHP-Fusion Inc</a></span><br/>\n";
	echo "<span>";
	if ($settings['visitorcounter_enabled']) echo showcounter();
	if ($settings['rendertime_enabled'] == '1' || $settings['rendertime_enabled'] == '2') {
		if ($settings['visitorcounter_enabled']) {
			echo " | ";
		}
		echo showrendertime();
	}
	echo "</span>\n";
	echo "</div>\n</div>\n";
	echo "</div>\n";
}





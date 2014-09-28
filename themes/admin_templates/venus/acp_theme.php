<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: acp_theme.php
| Author: PHP-Fusion Inc.
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
$settings['bootstrap'] = 1;
require_once INCLUDES."theme_functions_include.php";
include_once THEMES."admin_templates/venus/includes/functions.php";
require_once ADMIN."navigation.php";
add_to_head("<script type='text/javascript' src='".INCLUDES."jquery/jquery.cookie.js'></script>");
function render_adminpanel() {
	global $locale, $userdata, $defender, $pages, $aidlink, $settings;
	$admin_password = '';
	if (!check_admin_pass($admin_password)) {
		add_to_head("<link rel='stylesheet' href='".THEMES."templates/setup_styles.css' type='text/css' />");
		echo "<aside class='block-container'>\n";
		echo "<div class='block'>\n";
		echo "<div class='block-content clearfix' style='font-size:14px;'>\n";
		echo "<h6><strong>".$locale['280']."</strong></h6>\n";
		echo "<img class='pf-logo' src='".IMAGES."php-fusion-icon.png' class='position-absolute'/>";
		echo "<p class='text-right mid-opacity text-smaller'>".$locale['version'].$settings['version']."</p>";
		echo "<div class='row m-0'>\n<div class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>";
		include ADMIN."login.php";
		echo "</div>\n</div>\n";
		echo "</div>\n";
		echo "</div>\n</div>\n";
		echo "<div class='clearfix m-t-10'>\n";
		echo showcopyright();
		echo "</div>\n";
		echo "</aside>\n";
	} else {

		echo "<div id='admin-panel' ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? "class='in'" : '')." >\n";
		include THEMES."admin_templates/Venus/includes/header.php";
		echo "<div class='display-table' style='height:100%; width:100%;'>\n";
		echo "<!-- begin leftnav -->\n";
		echo "<div id='acp-left' class='pull-left off-canvas ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? 'in' : '')."' data-spy='affix' data-offset-top='0' data-offset-bottom='0' style='width:250px; height:100%;'>\n"; // collapse to top menu on sm and xs
		echo "<div class='panel panel-default admin' style='border:0px; box-shadow: none;'><div class='panel-body clearfix'>\n";
		echo "<div class='pull-left m-r-10'>\n".display_avatar($userdata, '50px')."</div>\n";
		echo "<span class='display-block m-t-5'><strong>\n".ucfirst($userdata['user_name'])."</strong>\n<br/>".getuserlevel($userdata['user_level'])."</span></div>\n";
		echo "<div class='text-center m-r-10'>\n";
		echo "<h5><strong>".$locale['UM097']."</strong></h5>\n";
		echo lang_switcher();
		echo "</div>\n";
		echo "</div>\n";
		echo admin_nav(1);
		echo "</div>\n";
		echo "<!--end leftnav -->\n";
		echo "<!-- begin main content -->\n";
		echo "<div id='acp-main' class='display-block acp ".(isset($_COOKIE['Venus']) && $_COOKIE['Venus'] ? 'in' : '')."' style='margin-top:50px; min-height:1125px; width:100%; height:100%; vertical-align:top;'>\n";
		echo "<div id='acp-toolkit' class='hidden-xs hidden-sm col-md-12 col-lg-12 m-b-10 m-r-0' style='width:100%' role='toolkits'>\n";
		echo "<nav>".admin_nav()."</nav>";
		echo "</div>\n";
		echo "<div id='acp-content' class='col-xs-12 col-sm-12 col-md-12 col-lg-12'>\n";
		echo CONTENT;
		echo "</div>\n";
		echo "<div class='m-l-20 display-block'>\n";
		echo "Venus Admin &copy; 2014 created by <a href='https://www.php-fusion.co.uk'><strong>PHP-Fusion Inc.</strong></a>\n";
		echo showcopyright();
		echo "</div>\n";
		echo "</div>\n";
		echo "<!-- end main content -->\n";
		echo "</div>\n"; // end display-table.
		echo "</div>\n"; // end admin-panel
	}
}
?>
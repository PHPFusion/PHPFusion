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
require_once "../maincore.php";
require_once THEMES."templates/admin_header.php";
require_once INCLUDES."theme_functions_include.php";
add_to_head("<link rel='stylesheet' href='".THEMES."admin_templates/venus/acp_styles.css' type='text/css' media='screen' />\n");
require_once ADMIN."navigation.php";
function openside($title = FALSE, $class = FALSE) {
	echo "<div class='panel panel-default tbl-border $class'>\n";
	echo ($title) ? "<div class='panel-heading'>$title</div>\n" : '';
	echo "<div class='panel-body'>\n";
}

function closeside($title = FALSE) {
	echo "</div>\n";
	echo ($title) ? "<div class='panel-footer'>$title</div>\n" : '';
	echo "</div>\n";
}

function opentable($title) {
	echo "<div class='panel panel-default box-shadow' style='border:none;'>\n<div class='panel-body'>\n";
	echo "<h3 class='m-b-20'>".$title."</h3>\n";
}

function closetable() {
	echo "</div>\n</div>\n";
}

function render_adminpanel() {
	global $locale, $userdata, $pages, $aidlink, $settings;
	//print_p($pages);
	$locale['acp_001'] = "Logged in as ";
	echo "<div id='admin-panel'>\n";
	include THEMES."admin_templates/venus/includes/header.php";
	echo "<div class='display-table' style='height:100%; width:100%;'>\n";
	echo "<!-- begin leftnav -->\n";
	echo "<div id='acp-left' class='pull-left off-canvas' data-spy='affix' data-offset-top='0' data-offset-bottom='0' style='width:250px; height:100%;'>\n"; // collapse to top menu on sm and xs
	echo "<div class='panel panel-default admin' style='border:0px; box-shadow: none;'><div class='panel-body clearfix'>\n";
	echo "<div class='pull-left m-r-10'>\n".display_avatar($userdata, '50px')."</div>\n";
	echo "<span class='display-block m-t-5'><strong>\n".ucfirst($userdata['user_name'])."</strong>\n<br/>".getuserlevel($userdata['user_level'])."</span>\n";
	echo "</div>\n</div>\n";
	echo admin_nav(1);
	echo "</div>\n";
	echo "<!--end leftnav -->\n";
	echo "<!-- begin main content -->\n";
	echo "<div id='acp-main' class='display-block acp' style='margin-top:50px; min-height:1125px; width:100%; height:100%; vertical-align:top;'>\n";
	// Venus Features
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

?>
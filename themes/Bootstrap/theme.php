<?php
if (!defined("IN_FUSION")) { die("Access Denied"); }

define("THEME_BULLET", "&middot;");
require_once INCLUDES."theme_functions_include.php";

function render_page($license = FALSE) {

	//add_handler("theme_output");
	global $settings, $main_style, $locale, $userdata, $aidlink, $mysql_queries_time;

	echo "<div class='container'>\n";
	echo showsublinks('')."\n";
	echo AU_CENTER ? "<div class='au-content'>".AU_CENTER."</div>\n" : '';
	echo "<div class='row'>\n";
	echo "<div class='col-xs-12 col-sm-3 col-md-3 col-lg-3 leftbar'>\n";
	echo RIGHT.LEFT;
	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9 main-content'>\n";
	echo U_CENTER.CONTENT.L_CENTER;
	echo "</div>\n";
	echo BL_CENTER ? "<div class='bl-content'>".BL_CENTER."</div>\n" : '';
	echo "</div>\n";
	echo "</div>\n";

}

function openside($title) {
	echo "<h2>$title</h2>\n";
}
function closeside() {

}
function opentable($title) {
	echo "<h2>$title</h2>\n";
}
function closetable() {

}


?>

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) 2002 - 2014 Nick Jones
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Name: Septenary Theme
| Filename: footer.php
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

if (file_exists(THEME."locale/".LANGUAGE.".php")) {
	include THEME."locale/".LANGUAGE.".php";
} else {
	include THEME."locale/English.php";
}

global $aidlink, $locale;

open_grid('footer', true);
echo "<div class='footer-row row'>\n";
echo "<div class='hidden-xs col-sm-3 col-md-3 col-lg-3'>\n";
echo "<img style='width:80%;' alt='".$locale['sept_011']."' class='img-responsive' src='".THEME."images/htmlcss.jpg' />";
echo "</div>\n";
echo "<div class='col-xs-12 col-sm-9 col-md-9 col-lg-9 footer-right-col'>\n";
echo "<div class='pull-right'>\n";
echo "<a href='#top'><i style='font-size:50px;' class='entypo mid-opacity up-circled'></i></a>\n";
echo "</div>\n";
echo "<p class='text-left'>".stripslashes(strip_tags($settings['footer']))."</p>
	<p>".showcopyright()."</p>
	<p>Septenary Theme by <a href='https://www.php-fusion.co.uk/profile.php?lookup=3674' target='_blank'>Craig</a> and <a href='https://www.php-fusion.co.uk/profile.php?lookup=16331' target='_blank'>Chan</a></p>
	<p>";
if ($settings['visitorcounter_enabled']) {
	echo "<p>".showcounter()."</p>\n";
}
if ($settings['rendertime_enabled'] == '1' || $settings['rendertime_enabled'] == '2') {
	// Make showing of queries and memory usage separate settings
	echo showrendertime();
	echo showMemoryUsage();
}
$footer_errors = showFooterErrors();
if (!empty($footer_errors)) {
    echo "<div>\n".showFooterErrors()."</div>\n";
}

echo "</p>\n";
echo "</div>\n";
echo "</div>\n";
close_grid(1);

add_to_footer("<script type='text/javascript'>
function ValidateForm(frm) {
	if(frm.stext.value=='') {
		alert('You Must Enter Something In The Search!');
	return false;
	}
	if(frm.stext.value.length < 3){
		alert('Search text must be at least 3 characters long!');
	return false;
	}
}
</script>");



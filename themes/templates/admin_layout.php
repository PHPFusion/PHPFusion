<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html><head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."' />";
echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />";
echo "<meta http-equiv='Cache-control' content='no-cache' />";
echo "<meta http-equiv='expires' content='".gmstrftime("%A %d-%b-%y %T %Z", time ()+64800)."'/>";
echo "<meta name='robots' content='none' />";
echo "<meta name='googlebot' content='noarchive' />";
if ($settings['bootstrap']) {
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
}
if ($bootstrap_theme_css_src) {
	echo "<link href='".$bootstrap_theme_css_src."' rel='stylesheet' media='screen' />";
}
if ($settings['entypo']) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-embedded.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/animation.css' type='text/css' />\n";
}

if ($settings['fontawesome']) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css' />\n";
}

// Default CSS styling which applies to all themes but can be overriden
echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />";

// Admin Panel Theme CSS
echo "<link href='".THEMES."admin_themes/".$settings['admin_theme']."/acp_styles.css' rel='stylesheet' type='text/css' media='screen' />";

// jQuery related includes
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>";

echo render_favicons(IMAGES);

if (function_exists("get_head_tags")) {
	echo get_head_tags();
}
echo "</head><body>";

// Check if the user is logged in
if (!check_admin_pass('')) {
	render_admin_login();
	} else {
	render_admin_panel();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
	$fusion_jquery_tags = \PHPFusion\Minifier::minify($fusion_jquery_tags, array('flaggedComments' => false));
	echo "<script type='text/javascript'>
		$(function() { $fusion_jquery_tags; });
		</script>\n";
}
echo "</body>\n</html>\n";
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
$locale = fusion_get_locale('', LOCALE.LOCALESET."global.php");
$locale += fusion_get_locale('', LOCALE.LOCALESET."admin/main.php");
header("Content-Type: text/html; charset=".$locale['charset']."");
echo "<!DOCTYPE html>";
echo "<html lang='".fusion_get_locale('xml_lang')."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."' />";
echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />";
echo "<meta http-equiv='Cache-control' content='no-cache' />";
echo "<meta http-equiv='expires' content='".gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60))."'/>";
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
echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' /\n>";
// Admin Panel Theme CSS
echo "<link href='".THEMES."admin_themes/".$settings['admin_theme']."/acp_styles.css' rel='stylesheet' type='text/css' media='screen' />\n";
// jQuery related includes
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.min.js'></script>\n";
echo render_favicons(IMAGES);
if (function_exists("get_head_tags")) {
    echo get_head_tags();
}
echo "</head>";
echo "<body>";

// Check if the user is logged in
if (!check_admin_pass('')) {
    render_admin_login();
} else {
    render_admin_panel();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";

// Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
    $fusion_jquery_tags = \PHPFusion\Minifier::minify($fusion_jquery_tags, array('flaggedComments' => FALSE));
    echo "<script type='text/javascript'>
		$(function() { $fusion_jquery_tags; });
		</script>\n";
}
echo "</body>\n</html>\n";
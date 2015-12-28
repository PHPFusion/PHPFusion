<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| http://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: layout.php
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
echo "<!DOCTYPE html>\n";
echo "<head>\n";
echo "<title>".fusion_get_settings("sitename")."</title>\n";
echo "<meta charset='".$locale['charset']."' />\n";
echo "<meta property='og:title' content='".fusion_get_settings("sitename")."' />\n";
echo "<meta name='description' content='".fusion_get_settings("description")."' />\n";
echo "<meta property='og:description' name='description' content='".fusion_get_settings("description")."' />\n";
echo "<meta property='og:url' content='".fusion_get_settings("siteurl")."' />\n";
echo "<meta name='url' content='".fusion_get_settings("siteurl")."' />\n";
echo "<meta property='og:keywords' content='".fusion_get_settings("keywords")."' />\n";
echo "<meta name='keywords' content='".fusion_get_settings("keywords")."' />\n";
echo "<meta property='og:image' content='".fusion_get_settings("sitebanner")."' />\n";
echo "<meta name='image' content='".fusion_get_settings("sitebanner")."' />\n";
echo "<meta http-equiv='Cache-control' content='PUBLIC' />\n";
echo "<meta http-equiv='expires' content='".gmstrftime("%A %d-%b-%y %T %Z", time() + 64800)."'/>\n";
if (fusion_get_settings("bootstrap") == TRUE) {
	echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
	echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
}
$theme_css_src = '';
if ($theme_css_src) {
	echo "<link href='".$theme_css_src."' rel='stylesheet' media='screen' />\n";
}

if (fusion_get_settings("entypo")) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-embedded.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7-codes.css' type='text/css' />\n";
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/animation.css' type='text/css' />\n";
}

if (fusion_get_settings("fontawesome")) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css' />\n";
}

// Load bootstrap stylesheets
if (fusion_get_settings("bootstrap")) {
	define('BOOTSTRAPPED', TRUE);
	$theme_name = isset($userdata['user_theme']) && $userdata['user_theme'] !== 'Default' ? $userdata['user_theme'] : fusion_get_settings('theme');
	$theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
	$theme_css = INCLUDES.'bootstrap/bootstrap.min.css';
	if (!empty($theme_data)) {
		$theme_css = THEMES.$theme_data['theme_file'];
	}
	echo "<link rel='stylesheet' href='".$theme_css."' type='text/css' />\n";
}

if (!defined('NO_DEFAULT_CSS')) {
	echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />\n";
}

echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";

echo render_favicons(IMAGES);
if (function_exists("get_head_tags")) {
	echo get_head_tags();
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscript.js'></script>\n";
echo "</head>\n";
echo "<body>\n";
if (iADMIN) {
	if (iSUPERADMIN && file_exists(BASEDIR."install/")) addNotice("danger", $locale['global_198'], 'all');
	if (fusion_get_settings("maintenance")) addNotice("warning", $locale['global_190'], 'all');
	if (!$userdata['user_admin_password']) addNotice("warning", str_replace(
        array("[LINK]","[/LINK]"), array("<a href='edit_profile.php'>", "</a>"),
              $locale['global_199']), 'all');
}

if (function_exists("render_page")) {
    render_page(); // by here, header and footer already closed
}
// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;
if (!empty($footerError)) {
	echo "<div class='admin-message container'>".$footerError."</div>";
}

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
	$fusion_jquery_tags = \PHPFusion\Minifier::minify($fusion_jquery_tags, array('flaggedComments' => FALSE));
	echo "<script type='text/javascript'>
		$(function() { $fusion_jquery_tags; });
		</script>\n";
}

// Load bootstrap javascript
if (fusion_get_settings("bootstrap")) {
	echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
	echo "<script type='text/javascript' src='".INCLUDES."bootstrap/holder.js'></script>\n";
}

/**
 * Uncomment to guide your theme development
 * echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";
 */
echo "</body>\n";
echo "</html>\n";
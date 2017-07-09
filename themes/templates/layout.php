<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
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
header("Content-Type: text/html; charset=".fusion_get_locale('charset')."");
echo "<!DOCTYPE html>\n";
echo "<html lang='".fusion_get_locale('xml_lang')."'".(fusion_get_settings('create_og_tags') ? " prefix='og: http://ogp.me/ns#'" : "").">\n";
echo "<head>\n";
echo "<title>".fusion_get_settings('sitename')."</title>\n";
echo "<meta charset='".fusion_get_locale('charset')."' />\n";
echo "<meta name='description' content='".fusion_get_settings('description')."' />\n";
echo "<meta name='url' content='".fusion_get_settings('siteurl')."' />\n";
echo "<meta name='keywords' content='".fusion_get_settings('keywords')."' />\n";
echo "<meta name='image' content='".fusion_get_settings('siteurl').fusion_get_settings('sitebanner')."' />\n";
if (fusion_get_enabled_languages() > 1) {
    echo "<link rel='alternate' hreflang='x-default' href='".fusion_get_settings('siteurl')."' />\n";
}
// Load bootstrap stylesheets
if (fusion_get_settings('bootstrap') == TRUE) {
    define('BOOTSTRAPPED', TRUE);
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap.min.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap-submenu.min.css' type='text/css' />\n";
    $user_theme = fusion_get_userdata('user_theme');
    $theme_name = $user_theme !== 'Default' ? $user_theme : fusion_get_settings('theme');
    $theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
    if (!empty($theme_data)) {
        $theme_css = THEMES.$theme_data['theme_file'];
        echo "<link href='".$theme_css."' rel='stylesheet' type='text/css' />\n";
    }
}

if (fusion_get_settings('entypo')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-codes.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-embedded.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo-ie7-codes.css' type='text/css' />\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/animation.css' type='text/css' />\n";
}

if (fusion_get_settings('fontawesome')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css' />\n";
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link href='".THEMES."templates/default.min.css' rel='stylesheet' type='text/css' media='screen' />\n";
}

echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";
echo render_favicons(IMAGES);

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}
if (!file_exists(INCLUDES.'jquery/jquery.min.js')) {
    echo "<script type='text/javascript' src='https://code.jquery.com/jquery-2.2.4.min.js'></script>\n";
}
echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.js'></script>\n";
echo "</head>\n";

/**
 * new constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */

if (!defined("THEME_BODY")) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

if (iADMIN) {
    if (iSUPERADMIN && file_exists(BASEDIR.'install.php')) {
        addNotice("danger", fusion_get_locale('global_198'), 'all');
    }
    if (fusion_get_settings('maintenance')) {
        addNotice("warning maintenance-alert", fusion_get_locale('global_190'), 'all');
    }
    if (!fusion_get_userdata('user_admin_password')) {
        addNotice("warning", str_replace(array("[LINK]", "[/LINK]"), array("<a href='".BASEDIR."edit_profile.php'>", "</a>"), fusion_get_locale('global_199')), 'all');
    }
}

if (function_exists("render_page")) {
    render_page(); // by here, header and footer already closed
}
// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;
if (!empty($footerError)) {
    echo "<div class='admin-message container'>".$footerError."</div>\n";
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-msg.js'></script>\n";
// Output lines added with add_to_jquery()
$jquery_tags = "$('[data-submenu]').submenupicker();";
// Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
$jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";

if (!empty($fusion_jquery_tags)) {
    $jquery_tags .= $fusion_jquery_tags;
}

$jquery_tags = \PHPFusion\Minifier::minify($jquery_tags, array('flaggedComments' => FALSE));
echo "<script type='text/javascript'>\n";
echo "$(function() { $jquery_tags });";
echo "</script>\n";

// Load bootstrap javascript
if (fusion_get_settings('bootstrap')) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap-submenu.min.js'></script>\n";
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/holder.min.js'></script>\n";
}

//Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";
echo "</body>\n";
echo "</html>";

PHPFusion\OpenGraph::ogDefault();

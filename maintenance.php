<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: maintenance.php
| Author: PHP-Fusion Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once dirname(__FILE__).'/maincore.php';

if (!fusion_get_settings("maintenance")) {
    redirect(BASEDIR.'index.php');
}

require_once INCLUDES."output_handling_include.php";

if (fusion_get_settings("site_seo") == 1 && !defined("IN_PERMALINK")) {
    \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handle_url_routing("");
}

ob_start();

if (!iMEMBER) {
    switch (fusion_get_settings('login_method')) {
        case "2" :
            $placeholder = $locale['global_101c'];
            break;
        case "1" :
            $placeholder = $locale['global_101b'];
            break;
        default:
            $placeholder = $locale['global_101a'];
    }
    $user_name = isset($_POST['user_name']) ? form_sanitizer($_POST['user_name'], "", "user_name") : "";
    $user_password = isset($_POST['user_pass']) ? form_sanitizer($_POST['user_pass'], "", "user_pass") : "";
    $path = fusion_get_settings('opening_page');
    if (!defined('IN_PERMALINK')) {
        $path = BASEDIR.(!stristr(fusion_get_settings('opening_page'), '.php') ? fusion_get_settings('opening_page').'/index.php' : fusion_get_settings('opening_page'));
    }
}

require_once THEME."theme.php";
require_once INCLUDES."header_includes.php";
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."templates/render_functions.php";

header("Content-Type: text/html; charset=".$locale['charset']."");
echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>\n";
echo "<head>\n";
echo "<title>".fusion_get_settings('sitename')."</title>\n";
echo "<meta charset='".$locale['charset']."' />\n";
echo "<meta name='description' content='".fusion_get_settings('description')."' />\n";
echo "<meta name='url' content='".fusion_get_settings('siteurl')."' />\n";
echo "<meta name='keywords' content='".fusion_get_settings('keywords')."' />\n";
echo "<meta name='image' content='".fusion_get_settings('siteurl').fusion_get_settings('sitebanner')."' />\n";



// Load bootstrap stylesheets
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap.min.css' type='text/css'/>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/bootstrap-submenu.min.css' type='text/css'/>\n";

    if ($locale['text-direction'] == 'rtl') {
        echo "<link href='".INCLUDES."bootstrap/bootstrap-rtl.min.css' rel='stylesheet' media='screen'/>\n";
    }
}

// Global CSS, Resets etc.
if (!defined('NO_GLOBAL_CSS')) {
	echo "<link rel='stylesheet' href='".THEMES."templates/global.css' type='text/css' media='screen' />\n";
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen'/>\n";
}

if ($settings['entypo'] || defined('ENTYPO')) {
	echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
}

// Font Awesome 4
if (defined('FONTAWESOME-V4')) {
    if ($settings['fontawesome'] || defined('FONTAWESOME')) {
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome/css/font-awesome.min.css' type='text/css'/>\n";
    }
}
// Font Awesome 5
if (!defined('FONTAWESOME-V4')) {
    if ($settings['fontawesome'] || defined('FONTAWESOME')) {
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css' type='text/css'/>\n";
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css' type='text/css'/>\n";
    }
}

echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.js'></script>\n";
echo "</head>\n";


if (fusion_get_settings('bootstrap') || defined('BOOTSTRAP')) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/bootstrap.min.js'></script>\n";
}

echo "<body class='maintenance'>\n";
echo "<table style='width:100%;height:100%'>\n<tr>\n<td>\n";

echo "<table cellpadding='0' cellspacing='1' width='80%' class='tbl-border center'>\n<tr>\n";
echo "<td class='tbl2'>\n<div style='text-align:center'><br />\n";
echo "<img src='".BASEDIR.$settings['sitebanner']."' alt='".$settings['sitename']."' /><br /><br />\n";
echo stripslashes(nl2br($settings['maintenance_message']))."<br /><br />\n";
echo "Powered by <a href='http://www.php-fusion.co.uk'>PHP-Fusion</a> &copy; PHP-Fusion Inc<br /><br />\n";
echo "</div>\n</td>\n</tr>\n</table>\n";

echo "<div align='center'><br />\n";
echo "<form name='loginform' method='post' action='".$settings['opening_page']."'>\n";
echo $locale['global_101'].": <input type='text' name='user_name' class='textbox' style='width:100px' />\n";
echo $locale['global_102'].": <input type='password' name='user_pass' class='textbox' style='width:100px' />\n";
echo "<input type='checkbox' name='remember_me' value='y' title='".$locale['global_103']."' />\n";
echo "<input type='submit' name='login' value='".$locale['global_104']."' class='button' />\n";
echo "</form>\n</div>\n";

echo "</td>\n</tr>\n</table>\n";
echo "</body>\n</html>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
$fusion_jquery_tags = "$('[data-submenu]').submenupicker();";
// Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
$fusion_jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
    push_jquery();
}

$output = ob_get_contents();
if (ob_get_length() !== FALSE) {
    ob_end_clean();
}
$output = handle_output($output);
echo $output;
if ((ob_get_length() > 0)) {
    ob_end_flush();
}
<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Core Development Team (coredevs@phpfusion.com)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/main.php");
$settings = fusion_get_settings();
header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'>";
echo "<meta name='robots' content='none'>";
echo "<meta name='googlebot' content='noarchive'>";

if ($settings['bootstrap'] || defined('BOOTSTRAP') || defined('BOOTSTRAP4')) {
    if (defined('BOOTSTRAP4')) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap.min.css">';
    } else {
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap.min.css">';

        if ($locale['text-direction'] == 'rtl') {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-rtl.min.css">';
        }
    }
}

if ($settings['entypo'] || defined('ENTYPO')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.min.css'>\n";
}

if ($settings['fontawesome'] || defined('FONTAWESOME')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css'/>\n";
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/PHPFusion/font.min.css'>\n";
}

// Admin Panel Theme CSS
$admin_theme_css = file_exists(THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css') ? THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.min.css' : THEMES.'admin_themes/'.$settings['admin_theme'].'/acp_styles.css';
echo "<link rel='stylesheet' href='".$admin_theme_css."?v=".filemtime($admin_theme_css)."'/>\n";

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script>var site_path = '".$settings['site_path']."';</script>";
echo "<script src='".INCLUDES."jscripts/jscript.min.js?v=".filemtime(INCLUDES.'jscripts/jscript.min.js')."'></script>\n";
echo "</head>";

/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */

if (!defined("THEME_BODY")) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

// Check if the user is logged in
if (!check_admin_pass('')) {
    if (empty(fusion_get_userdata("user_admin_password"))) {
        redirect(BASEDIR."edit_profile.php");
    } else {
        render_admin_login();
    }
} else {
    render_admin_panel();
}

// Load Bootstrap javascript
if ($settings['bootstrap'] || defined('BOOTSTRAP') || defined('BOOTSTRAP4')) {
    if (defined('BOOTSTRAP4')) {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap.bundle.min.js"></script>';
    } else {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap3/js/bootstrap.min.js"></script>';
    }
}

// Output lines added with add_to_footer()
global $fusion_page_footer_tags;
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
    if ($settings['devmode'] == 0) {
        $minifier = new PHPFusion\Minify\JS($fusion_jquery_tags);
        $js = $minifier->minify();
    } else {
        $js = $fusion_jquery_tags;
    }

    echo "<script>$(function(){".$js."});</script>\n";
}

echo "</body>\n";
echo "</html>";

<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: admin_layout.php
| Author: Core Development Team
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\OutputHandler;

$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/main.php");
$settings = fusion_get_settings();
define("BOOTSTRAP_ENABLED", (defined('BOOTSTRAP') && BOOTSTRAP == TRUE) || (defined('BOOTSTRAP4') && BOOTSTRAP4 == TRUE) || (defined('BOOTSTRAP5') && BOOTSTRAP5 == TRUE));

header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'>";
echo "<meta name='robots' content='none'>";
echo "<meta name='googlebot' content='noarchive'>";

if (BOOTSTRAP_ENABLED) {
    // Will optimize later with strings
    $custom_file = file_exists(THEME.'custom_bootstrap/custom_bootstrap.min.css') ? THEME.'custom_bootstrap/custom_bootstrap.min.css' : THEME.'custom_bootstrap/custom_bootstrap.css';
    if (defined('BOOTSTRAP5')) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        if (file_exists($custom_file)) {
            echo '<link rel="stylesheet" href="'.$custom_file.'">';
        } else {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap.min.css">';
        }
        if ($locale['text-direction'] == 'rtl') {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap-rtl.min.css">';
        }
        // need a submenu..
        //echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap-submenu.min.css">';
    } else if (defined('BOOTSTRAP4')) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
        if (file_exists($custom_file)) {
            echo '<link rel="stylesheet" href="'.$custom_file.'">';
        } else {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap.min.css">';
        }
        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap-submenu.min.css">';
    } else {
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        if (file_exists($custom_file)) {
            echo '<link rel="stylesheet" href="'.$custom_file.'">';
        } else {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap.min.css">';
        }

        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-submenu.min.css">';

        if ($locale['text-direction'] == 'rtl') {
            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-rtl.min.css">';
        }
    }
}

if (defined('ENTYPO') && ENTYPO == TRUE) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.min.css'>\n";
}

if (defined('FONTAWESOME') && FONTAWESOME == TRUE) {
    if (is_file(INCLUDES."fonts/font-awesome-5/css/all.min.css")) {
        echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>\n";
    }
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-6/css/all.min.css'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-6/css/v5-font-face.min.css'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-6/css/v4-shims.min.css'>\n";
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/PHPFusion/font.min.css?v2'>\n";
}

// Core CSS loading
$core_css_files = fusion_filter_hook("fusion_core_styles");
if (is_array($core_css_files)) {
    $core_css_files = array_filter($core_css_files);
    foreach ($core_css_files as $css_file) {
        if (is_file($css_file)) {
            echo fusion_load_script($css_file, "css", TRUE);
        }
    }
}
// Theme CSS loading
echo fusion_load_script(THEMES."admin_themes/".$settings["admin_theme"]."/acp_styles.css", "css", TRUE);

$theme_css_files = fusion_filter_hook("fusion_css_styles");
if (is_array($theme_css_files)) {
    $theme_css_files = array_filter($theme_css_files);
    foreach ($theme_css_files as $css_file) {
        //print_p($css_file);
        if (is_file($css_file)) {
            echo fusion_load_script($css_file, "css", TRUE);
        }
    }
}

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

echo "<script src='".INCLUDES."jquery/jquery-2.min.js'></script>\n";
echo "<script>var site_path = '".$settings['site_path']."';</script>";
echo "<script src='".INCLUDES."jscripts/jscript.min.js?v=".filemtime(INCLUDES.'jscripts/jscript.min.js')."'></script>\n";
echo "</head>\n";

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
    echo '<script src="'.ADMIN.'includes/update/update.js?v='.filemtime(ADMIN.'includes/update/update.js').'"></script>';

    if ($settings['update_checker'] == 1) {
        add_to_jquery('
            update_checker();
            setInterval(update_checker, 2000);
        ');
    }

    render_admin_panel();
}

// Load Bootstrap javascript
if (BOOTSTRAP_ENABLED) {
    if (defined('BOOTSTRAP5')) {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap5/js/bootstrap.bundle.min.js"></script>';
        //echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap-submenu.min.js"></script>';
    } else if (defined('BOOTSTRAP4')) {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap.bundle.min.js"></script>';
    } else {
        echo '<script src="'.INCLUDES.'bootstrap/bootstrap3/js/bootstrap.min.js"></script>';
    }
}
echo "<script defer src='".INCLUDES."jquery/notify.min.js'></script>\n";
// Output lines added with add_to_footer()
echo OutputHandler::$pageFooterTags;

// Output lines added with add_to_jquery()
$fusion_jquery_tags = OutputHandler::$jqueryCode;

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

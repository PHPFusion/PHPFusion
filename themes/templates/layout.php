<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: layout.php
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

$locale = fusion_get_locale();
$settings = fusion_get_settings();

//define( "BOOTSTRAP_ENABLED", (defined( 'BOOTSTRAP' ) && BOOTSTRAP == TRUE) || (defined( 'BOOTSTRAP4' ) && BOOTSTRAP4 == TRUE) || (defined( 'BOOTSTRAP5' ) && BOOTSTRAP5 == TRUE) );

if (!headers_sent()) {

//    if (iDEVELOPER) {

        header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
        header( 'Cache-Control: no-cache' );

//    } elseif (!check_get('logout')) {
//        // Get last modification time of the current PHP file
//        $file_last_mod_time = filemtime( $_SERVER['SCRIPT_FILENAME'] );
//
//        // Get last modification time of the main content (that user sees)
//        // Hardcoded just as an example
//        $content_last_mod_time = 0;
//        // Combine both to generate a unique ETag for a unique content
//        // Specification says ETag should be specified within double quotes
//        $etag = '"' . $file_last_mod_time . '.' . $content_last_mod_time . '"';
//
//        // Set Cache-Control header
//        header( 'Cache-Control: max-age=86400' );
//        // Set ETag header
//        header( 'ETag: ' . $etag );
//        header( "Content-Type: text/html; charset=" . $locale['charset'] );
//
//        // Check whether browser had sent a HTTP_IF_NONE_MATCH request header
//        if (isset( $_SERVER['HTTP_IF_NONE_MATCH'] )) {
//            // If HTTP_IF_NONE_MATCH is same as the generated ETag => content is the same as browser cache
//            // So send a 304 Not Modified response header and exit
//            if ($_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
//                header( 'HTTP/1.1 304 Not Modified', TRUE, 304 );
//                exit();
//            }
//        }
//    } elseif (check_get('logout')) {
//        header("Expires: Thu, 19 Nov 1981 08:52:00 GMT");
//        header("Cache-Control: no-store, no-cache, must-revalidate");
//    }
}

echo "<!DOCTYPE html>\n";
echo "<html lang='" . $locale['xml_lang'] . "' dir='" . $locale['text-direction'] . "'" . ($settings['create_og_tags'] ? " prefix='og: http://ogp.me/ns#'" : "") . ">\n";
echo "<head>\n";
echo "<title>" . $settings['sitename'] . "</title>\n";
echo "<meta charset='" . $locale['charset'] . "'>\n";
echo "<meta name='description' content='" . str_replace( "\n", ' ', strip_tags( htmlspecialchars_decode( $settings['description'] ) ) ) . "'>\n";
echo "<meta name='url' content='" . $settings['siteurl'] . "'>\n";
echo "<meta name='keywords' content='" . $settings['keywords'] . "'>\n";
echo "<meta name='image' content='" . $settings['siteurl'] . $settings['sitebanner'] . "'>\n";

$is_https = (isset( $_SERVER['HTTPS'] ) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
echo "<link rel='canonical' href='http" . ($is_https ? 's' : '') . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'>\n";

$languages = fusion_get_enabled_languages();
if (count( $languages ) > 1) {
    foreach ($languages as $language_folder => $language_name) {
        include LOCALE . $language_folder . '/global.php';
        echo '<link rel="alternate" hreflang="' . $locale['xml_lang'] . '" href="' . $settings['siteurl'] . $settings['opening_page'] . '?lang=' . $language_folder . '">';
    }

    echo "<link rel='alternate' hreflang='x-default' href='" . $settings['siteurl'] . "'>\n";
}

fusion_apply_hook( 'fusion_header_include', $custom_file ?? '' );

//if (BOOTSTRAP_ENABLED) {
//    // Will optimize later with strings
//    $custom_file = file_exists(THEME.'custom_bootstrap/custom_bootstrap.min.css') ? THEME.'custom_bootstrap/custom_bootstrap.min.css' : THEME.'custom_bootstrap/custom_bootstrap.css';
//    if (defined('BOOTSTRAP5')) {
//        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
//        if (file_exists($custom_file)) {
//            echo '<link rel="stylesheet" href="'.$custom_file.'">';
//        } else {
//            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap.min.css">';
//        }
//        if ($locale['text-direction'] == 'rtl') {
//            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap-rtl.min.css">';
//        }
//        // need a submenu..
//        //echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap5/css/bootstrap-submenu.min.css">';
//    } else if (defined('BOOTSTRAP4')) {
//        echo '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
//        if (file_exists($custom_file)) {
//            echo '<link rel="stylesheet" href="'.$custom_file.'">';
//        } else {
//            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap.min.css">';
//        }
//        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap4/css/bootstrap-submenu.min.css">';
//    } else {
//        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
//        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
//        if (file_exists($custom_file)) {
//            echo '<link rel="stylesheet" href="'.$custom_file.'">';
//        } else {
//            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap.min.css">';
//        }
//
//        echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-submenu.min.css">';
//
//        if ($locale['text-direction'] == 'rtl') {
//            echo '<link rel="stylesheet" href="'.INCLUDES.'bootstrap/bootstrap3/css/bootstrap-rtl.min.css">';
//        }
//    }
//}
//
//if (defined( 'FONTAWESOME' ) && FONTAWESOME == TRUE) {
//    if (is_file( INCLUDES . "fonts/font-awesome-5/css/all.min.css" )) {
//        echo "<link rel='stylesheet' href='" . INCLUDES . "fonts/font-awesome-5/css/all.min.css'>\n";
//    }
//    echo "<link rel='stylesheet' href='" . INCLUDES . "fonts/font-awesome-6/css/all.min.css'>\n";
//    echo "<link rel='stylesheet' href='" . INCLUDES . "fonts/font-awesome-6/css/v5-font-face.min.css'>\n";
//    echo "<link rel='stylesheet' href='" . INCLUDES . "fonts/font-awesome-6/css/v4-shims.min.css'>\n";
//}

if (!defined( 'NO_DEFAULT_CSS' )) {
    echo "<link rel='stylesheet' href='" . THEMES . "templates/default.min.css?v=" . filemtime( THEMES . 'templates/default.min.css' ) . "'>\n";
}

// Core CSS loading
$core_css_files = fusion_filter_hook( 'fusion_core_styles' );
if (is_array( $core_css_files )) {
    $core_css_files = array_filter( $core_css_files );
    foreach ($core_css_files as $css_file) {
        if (is_file( $css_file )) {
            echo fusion_load_script( $css_file, "css", TRUE );
        }
    }
}
// Theme CSS loading
$_styles_path = THEME.'styles.css';
if (is_file(THEME.'styles.min.css')) {
    $_styles_path = THEME.'styles.min.css';
}
$filetime = filemtime($_styles_path);
echo '<link rel="stylesheet" href="'.$_styles_path.'?v='.$filetime.'" defer>';


/*if (defined('BOOTSTRAP') && BOOTSTRAP == TRUE) {
    $user_theme = fusion_get_userdata('user_theme');
    $theme_name = $user_theme !== 'Default' ? $user_theme : $settings['theme'];
    $theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
    if (!empty($theme_data)) {
        echo fusion_load_script(THEMES.$theme_data["theme_file"], "css", TRUE);
    }
}*/

$theme_css_files = fusion_filter_hook( "fusion_css_styles" );
if (is_array( $theme_css_files )) {
    $theme_css_files = array_filter( $theme_css_files );
    foreach ($theme_css_files as $css_file) {
        if (is_file( $css_file )) {
            echo fusion_load_script( $css_file, "css", TRUE );
        }
    }
}

echo render_favicons( defined( 'THEME_ICON' ) ? THEME_ICON : IMAGES . 'favicons/' );

if (function_exists( "get_head_tags" )) {
    echo get_head_tags();
}

echo "<script src='" . INCLUDES . "jquery/jquery-2.min.js'></script>\n";
echo "<script>var site_path = '" . $settings['site_path'] . "';</script>";
echo "<script defer src='" . INCLUDES . "jscripts/jscript.min.js?v=" . filemtime( INCLUDES . 'jscripts/jscript.min.js' ) . "'></script>\n";
echo "</head>\n";

/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */

if (!defined( "THEME_BODY" )) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

if (iADMIN) {
    if (iSUPERADMIN && file_exists( BASEDIR . 'install.php' ) && $settings['devmode'] == 0 && !defined( "DEVMODE" )) {
        addnotice( 'danger', $locale['global_198'], 'all' );
    }

    if ($settings['maintenance']) {
        addnotice( 'warning maintenance-alert', $locale['global_190'], 'all' );
    }

    if (!fusion_get_userdata( 'user_admin_password' )) {
        addnotice( 'warning', str_replace( ["[LINK]", "[/LINK]"], ["<a href='" . BASEDIR . "edit_profile.php'>", "</a>"], $locale['global_199'] ), 'all' );
    }
}

//if (function_exists( "render_page" )) {
render_page(); // by here, header and footer already closed
//}

fusion_apply_hook( 'fusion_footer_include' );

// Load Bootstrap javascript
//if (BOOTSTRAP_ENABLED) {
//    if (defined('BOOTSTRAP5')) {
//        echo '<script src="'.INCLUDES.'bootstrap/bootstrap5/js/bootstrap.bundle.min.js"></script>';
//        //echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap-submenu.min.js"></script>';
//    } else if (defined('BOOTSTRAP4')) {
//        echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap.bundle.min.js"></script>';
//        echo '<script src="'.INCLUDES.'bootstrap/bootstrap4/js/bootstrap-submenu.min.js"></script>';
//    } else {
//
//    }
//}

echo "<script src='" . INCLUDES . "jquery/notify.min.js' defer></script>\n";
// Output lines added with add_to_footer()
echo OutputHandler::$pageFooterTags;

//@todo: This one need to port to BS3 and BS4 folder
$jquery_tags = '';
if (defined( 'BOOTSTRAP' ) && BOOTSTRAP < 5) {
    $jquery_tags .= "$('[data-submenu]').submenupicker();";
    // Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
    $jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";
}

// Output lines added with add_to_jquery()
$fusion_jquery_tags = OutputHandler::$jqueryCode;
if (!empty( $fusion_jquery_tags )) {
    $jquery_tags .= $fusion_jquery_tags;

    if (!$settings['devmode'] or !defined( 'DEVELOPER_MODE' )) {

        $minifier = new PHPFusion\Minify\JS( $jquery_tags );
        $js = $minifier->minify();

    } else {
        $js = $jquery_tags;
    }

    echo "<script>$(function(){" . $js . "});</script>\n";
}

echo "</body>\n";
echo "</html>";

PHPFusion\OpenGraph::ogDefault();

<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: admin_layout.php
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
$locale = fusion_get_locale( '', LOCALE.LOCALESET."admin/main.php" );
$settings = fusion_get_settings();

// Define CDN
$_themes = THEMES;
$_includes = INCLUDES;
if ( !empty( 'CDN' ) ) {
    $_themes = CDN.'themes/';
    $_includes = CDN.'includes/';
}

header( "Content-Type: text/html; charset=".$locale['charset']."" );

echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'>";
echo "<meta name='robots' content='none'>";
echo "<meta name='googlebot' content='noarchive'>";
echo "<link rel='canonical' href='".$settings['siteurl']."'>";
if ( isset( $fusion_steam ) ) {
    $fusion_steam->run();
    fusion_apply_hook( 'start_boiler' );
}
Dynamics::getInstance()->getHeaders();
if ( $settings['entypo'] || defined( 'ENTYPO' ) ) {
    echo "<link rel='stylesheet' href='".$_includes."fonts/entypo/entypo.min.css'>\n";
}

if ( $settings['fontawesome'] || defined( 'FONTAWESOME' ) ) {
    echo "<link rel='stylesheet' href='".$_includes."fonts/font-awesome-5/css/all.min.css'>\n";
    echo "<link rel='stylesheet' href='".$_includes."fonts/font-awesome-5/css/v4-shims.min.css'>\n";
}


// Admin Panel Theme CSS
$admin_css_file = min_file( THEMES.'admin_themes'.DIRECTORY_SEPARATOR.$settings['admin_theme'].DIRECTORY_SEPARATOR.'acp_styles.css' );
echo "<link rel='stylesheet' href='".$admin_css_file."?v=".filemtime( $admin_css_file )."'>\n";
// Default CSS styling which applies to all themes but can be overriden
if ( !defined( 'NO_DEFAULT_CSS' ) ) {
    $default_css_file = min_file( $_themes.'templates/default.css' );
    echo "<link rel='stylesheet' href='$default_css_file?v=".filemtime( $default_css_file )."'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/PHPFusion/font.min.css'>\n";
}

echo render_favicons( defined( 'THEME_ICON' ) ? THEME_ICON : IMAGES.'favicons/' );

if ( function_exists( "get_head_tags" ) ) {
    echo get_head_tags();
}

echo "<script src='".$_includes."jquery/jquery.min.js'></script>\n";
echo "<script>const SITE_PATH = '".$settings['site_path']."';const CDN = '".CDN."';const BASEDIR = document.location.origin + SITE_PATH;</script>\n";
echo "<script src='".$_includes."jscripts/jscript.min.js?v=".filemtime( $_includes.'jscripts/jscript.min.js' )."'></script>\n";
echo "</head>\n";

/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */
if ( !defined( "THEME_BODY" ) ) {
    echo "<body>\n";
} else {
    echo THEME_BODY;
}

// Check if the user is logged in
if ( !check_admin_pass( '' ) ) {
    if ( empty( fusion_get_userdata( "user_admin_password" ) ) ) {
        redirect( BASEDIR."edit_profile.php" );
    } else {
        render_admin_login();
    }
} else {
    render_admin_panel();
}

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;
echo "<script src='".$_includes."jquery/holder/holder.min.js'></script>\n";
// Output lines added with add_to_jquery()
if ( !empty( $fusion_jquery_tags ) ) {
    $js = $fusion_jquery_tags;
    if ( !fusion_get_settings( 'devmode' ) ) {
        $minifier = new PHPFusion\Minify\JS( $fusion_jquery_tags );
        $js = $minifier->minify();
    }
    echo "<script>$(function(){".$js."});</script>\n";
}
// Uncomment to guide your theme development
if ( fusion_get_settings( 'devmode' ) ) {
    echo "<script src='".INCLUDES."jscripts".DIRECTORY_SEPARATOR."html-inspector.js'></script><script>HTMLInspector.inspect()</script>\n";
}
echo "</body>\n";
echo "</html>";

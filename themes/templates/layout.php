<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: layout.php
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
$locale = fusion_get_locale();
$settings = fusion_get_settings();

// Define CDN
$_themes = THEMES;
$_includes = INCLUDES;
if (!empty('CDN')) {
    $_themes = CDN.'themes/';
    $_includes = CDN.'includes/';
}

header("Content-Type: text/html; charset=".$locale['charset']."");

echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'".($settings['create_og_tags'] ? " prefix='og: http://ogp.me/ns#'" : "").">\n";
echo "<head>\n";
echo "<title>".$settings['sitename']."</title>\n";
echo "<meta charset='".$locale['charset']."'/>\n";
echo "<meta name='description' content='".$settings['description']."'/>\n";
echo "<meta name='url' content='".$settings['siteurl']."'/>\n";
echo "<meta name='keywords' content='".$settings['keywords']."'/>\n";
echo "<meta name='image' content='".$settings['siteurl'].$settings['sitebanner']."'/>\n";

if (fusion_get_enabled_languages() > 1) {
    echo "<link rel='alternate' hreflang='x-default' href='".$settings['siteurl']."'/>\n";
}

if (isset($fusion_steam)) {
    $fusion_steam->run();
    fusion_apply_hook('start_boiler');
}

if ($settings['entypo'] || defined('ENTYPO')) {
    echo "<link rel='stylesheet' href='".$_includes."fonts/entypo/entypo.min.css'/>\n";
}

if ($settings['fontawesome'] || defined('FONTAWESOME')) {
    // Font Awesome 4
    if (defined('FONTAWESOME-V4')) {
        echo "<link rel='stylesheet' href='".$_includes."fonts/font-awesome/css/font-awesome.min.css'/>\n";
    } else {
        // Font Awesome 5
        echo "<link rel='stylesheet' href='".$_includes."fonts/font-awesome-5/css/all.min.css'/>\n";
        echo "<link rel='stylesheet' href='".$_includes."fonts/font-awesome-5/css/v4-shims.min.css'/>\n";
    }
}

// Default CSS styling which applies to all themes but can be overriden
if (!defined('NO_DEFAULT_CSS')) {
    $dev_mode = TRUE;
    $default_css_file = $dev_mode ? $_themes.'templates/default.css' : $_themes.'templates/default.min.css';
    echo "<link rel='stylesheet' href='$default_css_file?v=".filemtime($default_css_file)."'/>\n";
}

// Theme CSS
$theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
echo "<link rel='stylesheet' href='".$theme_css."?v=".filemtime($theme_css)."'/>\n";

// Atom Engine
$user_theme = fusion_get_userdata('user_theme');
$theme_name = $user_theme !== 'Default' ? $user_theme : fusion_get_settings('theme');
$theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));
if (!empty($theme_data)) {
    $theme_css = THEMES.$theme_data['theme_file'];
    add_to_head("<link href='".$theme_css."' rel='stylesheet' type='text/css' />\n");
} else {
    $theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
    echo "<link href='".$theme_css."' rel='stylesheet' type='text/css' media='screen' />\n";
}

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".$_includes."jquery/jquery.2.2.4.min.js'></script>\n";
//echo "<script type='text/javascript' src='".$_includes."jquery/jquery-migrate.min.js'></script>\n";
echo "<script>const SITE_PATH = '".$settings['site_path']."';const CDN = '".CDN."';</script>\n";
echo "<script type='text/javascript' src='".$_includes."jscripts/jscript.min.js?v=".filemtime($_includes.'jscripts/jscript.min.js')."'></script>\n";
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

if (iADMIN) {
    if (iSUPERADMIN && file_exists(BASEDIR.'install.php') && $settings['devmode'] == 0 && !defined("DEVMODE")) {
        addNotice('danger', $locale['global_198'], 'all');
    }

    if ($settings['maintenance']) {
        addNotice('warning maintenance-alert', $locale['global_190'], 'all');
    }

    if (!fusion_get_userdata('user_admin_password')) {
        addNotice('warning', str_replace(["[LINK]", "[/LINK]"], ["<a href='".BASEDIR."edit_profile.php'>", "</a>"], $locale['global_199']), 'all');
    }
}

if (function_exists("render_page")) {
    render_page(); // by here, header and footer already closed
}

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

echo "<script src='".$_includes."jquery/admin-scripts.js'></script>\n";
echo "<script src='".$_includes."jquery/holder/holder.min.js'></script>\n";

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

// Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script><script>HTMLInspector.inspect()</script>\n";
echo "</body>\n";
echo "</html>";

PHPFusion\OpenGraph::ogDefault();

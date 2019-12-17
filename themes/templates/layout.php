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

if (!headers_sent()) {
    header('Expires: Thu, 23 Mar 1972 07:00:00 GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header("Content-Type: text/html; charset=".$locale['charset']);
}

echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'".($settings['create_og_tags'] ? " prefix='og: http://ogp.me/ns#'" : "").">\n";
echo "<head>\n";
echo "<title>".$settings['sitename']."</title>\n";
echo "<meta charset='".$locale['charset']."'>\n";
echo "<meta name='description' content='".$settings['description']."'>\n";
echo "<meta name='url' content='".$settings['siteurl']."'>\n";
echo "<meta name='keywords' content='".$settings['keywords']."'>\n";
echo "<meta name='image' content='".$settings['siteurl'].$settings['sitebanner']."'>\n";

$languages = fusion_get_enabled_languages();
if (count($languages) > 1) {
    foreach ($languages as $language_folder => $language_name) {
        include LOCALE.$language_folder.'/global.php';
        echo '<link rel="alternate" hreflang="'.$locale['xml_lang'].'" href="'.$settings['siteurl'].$settings['opening_page'].'?lang='.$language_folder.'">';
    }

    echo "<link rel='alternate' hreflang='x-default' href='".$settings['siteurl']."'>\n";
}

if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap.min.css'>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap-submenu.min.css'>\n";

    if ($locale['text-direction'] == 'rtl') {
        echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap-rtl.min.css'>\n";
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
}

if (!defined('PF_FONT') || (defined('PF_FONT') && PF_FONT == TRUE)) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/PHPFusion/font.min.css'>\n";
}

$theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
echo "<link rel='stylesheet' href='".$theme_css."?v=".filemtime($theme_css)."'>\n";

if ($settings['bootstrap'] == TRUE || defined('BOOTSTRAP')) {
    $user_theme = fusion_get_userdata('user_theme');
    $theme_name = $user_theme !== 'Default' ? $user_theme : $settings['theme'];
    $theme_data = dbarray(dbquery("SELECT theme_file FROM ".DB_THEME." WHERE theme_name='".$theme_name."' AND theme_active='1'"));

    if (!empty($theme_data)) {
        echo "<link rel='stylesheet' href='".THEMES.$theme_data['theme_file']."'>\n";
    }
}

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script>var site_path = '".$settings['site_path']."';</script>";
echo "<script defer src='".INCLUDES."jscripts/jscript.min.js?v=".filemtime(INCLUDES.'jscripts/jscript.min.js')."'></script>\n";
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

// Load Bootstrap javascript
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<script src='".INCLUDES."bootstrap/js/bootstrap.min.js'></script>\n";
    echo "<script src='".INCLUDES."bootstrap/js/bootstrap-submenu.min.js'></script>\n";
}

echo "<script src='".INCLUDES."jquery/holder/holder.min.js'></script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

$jquery_tags = '';

if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    $jquery_tags .= "$('[data-submenu]').submenupicker();";
    // Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
    $jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";
}

// Output lines added with add_to_jquery()
if (!empty($fusion_jquery_tags)) {
    $jquery_tags .= $fusion_jquery_tags;

    if ($settings['devmode'] == 0) {
        $minifier = new PHPFusion\Minify\JS($jquery_tags);
        $js = $minifier->minify();
    } else {
        $js = $jquery_tags;
    }

    echo "<script>$(function(){".$js."});</script>\n";
}

// Uncomment to guide your theme development
//echo "<script src='".INCLUDES."jscripts/html-inspector.js'></script>\n<script> HTMLInspector.inspect() </script>\n";
echo "</body>\n";
echo "</html>";

PHPFusion\OpenGraph::ogDefault();

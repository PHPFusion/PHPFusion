<?php
/*-------------------------------------------------------+
| PHPFusion Content Management System
| Copyright (C) PHP Fusion Inc
| https://phpfusion.com/
+--------------------------------------------------------+
| Filename: layout.php
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
header("Content-Type: text/html; charset=".$locale['charset']);

echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>\n";
echo "<head>\n";
echo "<title>".$settings['sitename']."</title>\n";
echo "<meta charset='".$locale['charset']."'/>\n";
echo "<meta name='description' content='".$settings['description']."'/>\n";
echo "<meta name='url' content='".$settings['siteurl']."'/>\n";
echo "<meta name='keywords' content='".$settings['keywords']."'/>\n";
echo "<meta name='image' content='".$settings['siteurl'].$settings['sitebanner']."'/>\n";
$is_https = (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
echo "<link rel='canonical' href='http".($is_https ? 's' : '')."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."'>\n";

if (fusion_get_enabled_languages() > 1) {
    echo "<link rel='alternate' hreflang='x-default' href='".$settings['siteurl']."'/>\n";
}

// Load bootstrap stylesheets
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<meta http-equiv='X-UA-Compatible' content='IE=edge'/>\n";
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'/>\n";

    $custom_bs = file_exists(THEME.'custom_bootstrap/custom_bootstrap.min.css') ? THEME.'custom_bootstrap/custom_bootstrap.min.css' : THEME.'custom_bootstrap/custom_bootstrap.css';
    if (file_exists($custom_bs)) {
        echo '<link rel="stylesheet" href="'.$custom_bs.'">';
    } else {
        echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap.min.css' type='text/css'/>\n";
    }

    echo "<link rel='stylesheet' href='".INCLUDES."bootstrap/css/bootstrap-submenu.min.css' type='text/css'/>\n";

    if ($locale['text-direction'] == 'rtl') {
        echo "<link href='".INCLUDES."bootstrap/css/bootstrap-rtl.min.css' rel='stylesheet' media='screen'/>\n";
    }
}

if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/default.min.css?v=".filemtime(THEMES.'templates/default.min.css')."'>\n";
}

if ($settings['entypo'] || defined('ENTYPO')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.css' type='text/css' />\n";
}

if ($settings['fontawesome'] || defined('FONTAWESOME')) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css' type='text/css'/>\n";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css' type='text/css'/>\n";
}

$theme_css = file_exists(THEME.'styles.min.css') ? THEME.'styles.min.css' : THEME.'styles.css';
echo "<link rel='stylesheet' href='".$theme_css."?v=".filemtime($theme_css)."'>\n";

echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES.'favicons/');

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.min.js'></script>\n";
echo "<script type='text/javascript' src='".INCLUDES."jscripts/jscript.min.js'></script>\n";
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

if (function_exists("render_page")) {
    render_page(); // by here, header and footer already closed
}

// Load Bootstrap javascript
if ($settings['bootstrap'] || defined('BOOTSTRAP')) {
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/js/bootstrap.min.js'></script>\n";
    echo "<script type='text/javascript' src='".INCLUDES."bootstrap/js/bootstrap-submenu.min.js'></script>\n";
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/admin-scripts.js'></script>\n";

// Output lines added with add_to_footer()
echo $fusion_page_footer_tags;

// Output lines added with add_to_jquery()
//$fusion_jquery_tags = "$('[data-submenu]').submenupicker();";
// Fix select2 on modal - http://stackoverflow.com/questions/13649459/twitter-bootstrap-multiple-modal-error/15856139#15856139
//$fusion_jquery_tags .= "$.fn.modal.Constructor.prototype.enforceFocus = function () {};";

if (!empty($fusion_jquery_tags)) {
    echo "<script type='text/javascript'>$(function(){".$fusion_jquery_tags."});</script>\n";
}
echo "</body>\n";
echo "</html>";

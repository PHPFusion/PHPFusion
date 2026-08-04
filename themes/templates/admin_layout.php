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
use PHPFusion\Minify;
use PHPFusion\OutputHandler;
$locale = fusion_get_locale('', LOCALE.LOCALESET."admin/main.php");
$settings = fusion_get_settings();

require_once INCLUDES.'frameworks/framework_engine.php';
fusion_framework_boot('admin');

header("Content-Type: text/html; charset=".$locale['charset']."");
echo "<!DOCTYPE html>";
echo "<html lang='".$locale['xml_lang']."' dir='".$locale['text-direction']."'>";
echo "<head>";
echo "<title>".$settings['sitename']."</title>";
echo "<meta charset='".$locale['charset']."'>";
echo "<meta name='robots' content='none'>";
echo "<meta name='googlebot' content='noarchive'>";

if (defined('ENTYPO') && ENTYPO == TRUE) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/entypo/entypo.min.css'>";
}
if (defined('FONTAWESOME') && FONTAWESOME == TRUE) {
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/all.min.css'>";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/font-awesome-5/css/v4-shims.min.css'/>";
}
if (!defined('NO_DEFAULT_CSS')) {
    echo "<link rel='stylesheet' href='".THEMES."templates/styles/default.min.css?v=".filemtime(THEMES.'templates/styles/default.min.css')."'>";
    echo "<link rel='stylesheet' href='".INCLUDES."fonts/PHPFusion/font.min.css?v2'>";
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
echo render_favicons(defined('THEME_ICON') ? THEME_ICON : IMAGES . 'favicons/');

echo "<script src='".INCLUDES."jquery/jquery-2.min.js'></script>";
echo "<script>var site_path = '".$settings['site_path']."';</script>";
echo "<script src='".INCLUDES."jscripts/jscript.min.js?v=".filemtime(INCLUDES.'jscripts/jscript.min.js')."'></script>";

// Theme CSS loading
$active_admin_theme = defined('ADMIN_THEME_NAME') ? ADMIN_THEME_NAME : $settings["admin_theme"];
$admin_css_file = THEMES."admin_themes/".$active_admin_theme."/acp_styles.css";
if (defined('ADMIN_CSS_FILE')) {
	$admin_css_file = THEMES."admin_themes/".$active_admin_theme."/".ADMIN_CSS_FILE;
}

fusion_apply_hook('fusion_framework_header', 'admin');

//echo fusion_load_script($admin_css_file, "css", TRUE);
echo "<link rel='stylesheet' href='".$admin_css_file."' type='text/css' media='all'>";

echo "</head>";
/**
 * Constant - THEME_BODY;
 * replace <body> tags with your own theme definition body tags. Some body tags require additional params
 * for the theme purposes.
 */
if (!defined("THEME_BODY")) {
    echo "<body>";
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
fusion_apply_hook('fusion_framework_footer', 'admin');
echo "<script defer src='".INCLUDES."jquery/notify.min.js'></script>";
// Output lines added with add_to_footer()
echo OutputHandler::$pageFooterTags;
// Output lines added with add_to_jquery()
$jquery_tags = '';
$fusion_jquery_tags = OutputHandler::$jqueryCode;
if (!empty($fusion_jquery_tags)) {
    ksort($fusion_jquery_tags);

    $fusion_jquery_tags = implode('', $fusion_jquery_tags);
    $jquery_tags .= $fusion_jquery_tags;
    $js = $jquery_tags;
    if ($settings['devmode'] == 0 || !defined('FUSION_DEVELOPMENT')) {
       // $js = Minify::minify($jquery_tags);
    }
    ?>
    <script>
        $(function(){
            <?= $js ?>
        });
    </script>
    <?php
}
echo "</body>";
echo "</html>";

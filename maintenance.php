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
    redirect("index.php");
}

if (fusion_get_settings("site_seo") == 1 && !defined("IN_PERMALINK")) {
    \PHPFusion\Rewrite\Permalinks::getPermalinkInstance()->handle_url_routing("");
}
$locale = fusion_get_locale();

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

    $info = [
        "open_form"            => openform('loginpageform', 'POST', fusion_get_settings('opening_page')),
        "user_name"            => form_text('user_name', "", $user_name, ['placeholder' => $placeholder, 'inline' => TRUE]),
        "user_pass"            => form_text('user_pass', "", $user_password, ['placeholder' => $locale['global_102'], 'type' => 'password', 'inline' => TRUE]),
        "remember_me"          => form_checkbox('remember_me', $locale['global_103'], ""),
        "login_button"         => form_button('login', $locale['global_104'], $locale['global_104'], ['class' => 'btn-primary btn-block m-b-20']),
        "registration_link"    => (fusion_get_settings('enable_registration')) ? "<p>".$locale['global_105']."</p>\n" : "",
        "forgot_password_link" => $locale['global_106'],
        "close_form"           => closeform()
    ];
}

require_once THEME."theme.php";
require_once INCLUDES."header_includes.php";
require_once INCLUDES."theme_functions_include.php";
require_once THEMES."templates/render_functions.php";
include THEMES."templates/global/maintenance.php";

header("Content-Type: text/html; charset=".$locale['charset']."");
echo "<!DOCTYPE html>\n";
echo "<html lang='".$locale['xml_lang']."'>\n";
echo "<head>\n";
echo "<title>".fusion_get_settings('sitename')."</title>\n";
echo "<meta charset='".$locale['charset']."' />\n";
echo "<meta name='description' content='".fusion_get_settings('description')."' />\n";
echo "<meta name='url' content='".fusion_get_settings('siteurl')."' />\n";
echo "<meta name='keywords' content='".fusion_get_settings('keywords')."' />\n";
echo "<meta name='image' content='".fusion_get_settings('siteurl').fusion_get_settings('sitebanner')."' />\n";

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
    echo "<link href='".THEMES."templates/default.css' rel='stylesheet' type='text/css' media='screen' />\n";
}

echo "<link href='".THEME."styles.css' rel='stylesheet' type='text/css' media='screen' />\n";
echo render_favicons(IMAGES);

if (function_exists("get_head_tags")) {
    echo get_head_tags();
}

echo "<script type='text/javascript' src='".INCLUDES."jquery/jquery.js'></script>\n";
echo "</head>\n";

display_maintenance($info);

echo "</body>\n";
echo "</html>";
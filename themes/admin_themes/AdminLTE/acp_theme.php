<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.phpfusion.com/
+--------------------------------------------------------+
| Filename: AdminLTE/acp_theme.php
| Author: RobiNN
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
if (!defined('IN_FUSION')) {
    die('Access Denied');
}

if (!defined('ALTE_LOCALE')) {
    if (file_exists(THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php')) {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/'.LANGUAGE.'.php');
    } else {
        define('ALTE_LOCALE', THEMES.'admin_themes/AdminLTE/locale/English.php');
    }
}

define('ADMINLTE', THEMES.'admin_themes/AdminLTE/');
require_once INCLUDES.'theme_functions_include.php';
require_once ADMINLTE.'acp_autoloader.php';

define('BOOTSTRAP', TRUE);
define('FONTAWESOME', TRUE);
define('ENTYPO', TRUE);

if (!check_admin_pass('') && !stristr($_SERVER['PHP_SELF'], $settings['site_path'].'infusions')) {
    define('THEME_BODY', '<body class="hold-transition lockscreen">');
} else {
    define('THEME_BODY', '<body class="hold-transition skin-blue sidebar-mini">');
}

function render_admin_panel() {
    new AdminLTE\AdminPanel();
}

function render_admin_login() {
    new AdminLTE\Login();
}

function render_admin_dashboard() {
    AdminLTE\Dashboard::AdminDashboard();
}

function openside($title = FALSE, $class = NULL) {
    echo '<div class="box box-widget '.$class.'">';
    echo $title ? '<div class="box-header with-border">'.$title.'</div>' : '';
    echo '<div class="box-body">';
}

function closeside($footer = FALSE) {
    echo '</div>';
    echo $footer ? '<div class="box-footer">'.$footer.'</div>' : '';
    echo '</div>';
}

function opentable($title, $class = NULL, $bg = TRUE) {
    AdminLTE\AdminPanel::OpenTable($title, $class, $bg);
}

function closetable($bg = TRUE) {
    AdminLTE\AdminPanel::CloseTable($bg);
}

\PHPFusion\OutputHandler::addHandler(function ($output = '') {
    $color = !check_admin_pass('') ? 'd2d6de' : '3c8dbc';

    return preg_replace("/<meta name='theme-color' content='#ffffff'>/i", '<meta name="theme-color" content="#'.$color.'"/>', $output);
});
